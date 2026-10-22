<?php

namespace Tests\Feature;

use App\Mail\StudioEmail;
use App\Models\User;
use App\Services\EmailStudioHtml;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EmailStudioSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Mail::fake();

        config()->set('email_studio.enabled', true);
        config()->set('mail.mailers.studio_smtp.host', 'smtp.example.invalid');
        config()->set('mail.mailers.studio_smtp.username', 'test@example.invalid');
        config()->set('email_studio.from_address', 'test@example.invalid');
        config()->set('email_studio.from_name', 'Join Gallagher');

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('user_type');
            $t->string('password')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        (require database_path('migrations/2023_12_21_130613_create_permission_tables.php'))->up();
        (require database_path('migrations/2026_10_22_000008_create_email_studio_tables.php'))->up();
        (require database_path('migrations/2026_10_22_000010_create_email_studio_pdf_visual_pages.php'))->up();
        (require database_path('migrations/2026_10_22_000011_create_email_studio_smtp_profiles.php'))->up();

        DB::table('users')->insert([
            ['id'=>1,'name'=>'Allowed Admin','email'=>'admin@example.invalid','user_type'=>'admin'],
            ['id'=>2,'name'=>'Unprivileged Admin','email'=>'otheradmin@example.invalid','user_type'=>'admin'],
            ['id'=>3,'name'=>'Applicant','email'=>'applicant@example.invalid','user_type'=>'job_seeker'],
        ]);

        Permission::findOrCreate('manage-email-studio', 'web');
        User::findOrFail(1)->givePermissionTo('manage-email-studio');
    }

    private function template(): int
    {
        return DB::table('email_studio_templates')->insertGetId([
            'name'=>'Applicant Letter',
            'subject'=>'Application update for {{client_name}}',
            'html_body'=>'<p>Hello <strong>{{client_name}}</strong>, {{message}}</p>',
            'footer'=>'Join Gallagher',
            'images'=>'{}',
            'created_by'=>1,'updated_by'=>1,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    public function test_pasted_html_sanitizer_removes_scripts_images_and_event_handlers(): void
    {
        $html = app(EmailStudioHtml::class)->sanitize(
            '<p style="text-align:center;color:#193a64;position:absolute" onclick="evil()">Hi ' .
            '<strong>Client</strong><script>alert(1)</script>' .
            '<img src="https://evil.invalid/tracker.png">' .
            '<a href="javascript:alert(1)">bad link</a></p>'
        );

        $this->assertStringContainsString('text-align:center', $html);
        $this->assertStringContainsString('color:#193a64', $html);
        $this->assertStringContainsString('<strong>Client</strong>', $html);
        $this->assertStringNotContainsString('script', $html);
        $this->assertStringNotContainsString('evil.invalid', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('position:', $html);
    }

    public function test_placeholders_escape_html_and_keep_readable_plaintext(): void
    {
        $f = app(EmailStudioHtml::class);
        $email = $f->personalize('<p>Hello {{client_name}}</p>',
            ['client_name'=>'<script>alert(1)</script>']);
        $this->assertStringNotContainsString('<script>', $email);
        $this->assertStringContainsString('&lt;script&gt;', $email);
        $this->assertSame("First\nSecond", $f->plainText('<p>First</p><p>Second</p>'));
    }

    public function test_disabled_feature_blocks_admin_even_with_permission(): void
    {
        config()->set('email_studio.enabled', false);
        $this->actingAs(User::findOrFail(1))
            ->get(route('email-studio.index'))->assertNotFound();
    }

    public function test_admin_without_permission_and_applicant_cannot_view_templates(): void
    {
        $this->actingAs(User::findOrFail(2))
            ->get(route('email-studio.index'))->assertForbidden();
        $this->actingAs(User::findOrFail(3))
            ->get(route('email-studio.index'))->assertForbidden();
    }

    public function test_valid_admin_can_create_safe_template_with_csrf_form(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->post(route('email-studio.store'), [
                'name'=>'Case Update', 'subject'=>'Welcome {{client_name}}',
                'html_body'=>'<p>Dear <strong>{{client_name}}</strong></p><script>x()</script>',
                'footer'=>'Official contact details',
                'width_top'=>600,'width_bottom_left'=>220,'width_bottom_right'=>230,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('email_studio_templates', 1);
        $body = DB::table('email_studio_templates')->value('html_body');
        $this->assertStringContainsString('<strong>{{client_name}}</strong>', $body);
        $this->assertStringNotContainsString('<script>', $body);
    }

    public function test_authorized_manual_smtp_send_is_logged_without_body_or_credentials(): void
    {
        $id = $this->template();

        $this->actingAs(User::findOrFail(1))
            ->post(route('email-studio.send',$id), [
                'recipient'=>'client@example.invalid',
                'client_name'=>'Jane Doe',
                'message'=>'Your documents are being reviewed.',
                'pdf_mode'=>'none',
            ])->assertRedirect();

        Mail::assertSent(StudioEmail::class, function (StudioEmail $mail) {
            return $mail->hasTo('client@example.invalid')
                && $mail->subjectLine === 'Application update for Jane Doe'
                && str_contains($mail->bodyHtml, 'Jane Doe')
                && !str_contains($mail->bodyHtml, '<script>');
        });

        $this->assertDatabaseHas('email_studio_send_logs', [
            'template_id'=>$id, 'recipient'=>'client@example.invalid',
            'result'=>'accepted','pdf_attached'=>0,
        ]);
    }

    public function test_pdf_generation_includes_attachment_and_does_not_make_public_file(): void
    {
        $id = $this->template();

        $this->actingAs(User::findOrFail(1))
            ->post(route('email-studio.send',$id), [
                'recipient'=>'sample@example.invalid',
                'client_name'=>'Example Applicant',
                'message'=>'Please see the attached PDF.',
                'pdf_mode'=>'generate',
            ])->assertRedirect();

        Mail::assertSent(StudioEmail::class, function (StudioEmail $mail) {
            return $mail->hasTo('sample@example.invalid') &&
                $mail->pdfFilename === 'JWTC-document.pdf' &&
                str_starts_with((string) $mail->pdfBytes, '%PDF');
        });
        $this->assertDatabaseHas('email_studio_send_logs', [
            'recipient'=>'sample@example.invalid', 'pdf_attached'=>1,
        ]);
    }

    public function test_template_image_upload_stays_on_private_disk(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->post(route('email-studio.store'), [
                'name'=>'Graphic Template','subject'=>'Document notification',
                'html_body'=>'<p>Text with branding.</p>', 'footer'=>'Private footer',
                'width_top'=>600, 'width_bottom_left'=>250,'width_bottom_right'=>250,
                'image_top'=>UploadedFile::fake()->image('banner.png',500,180),
            ])->assertRedirect();

        $img = json_decode(DB::table('email_studio_templates')->value('images'),true);
        $this->assertArrayHasKey('top', $img);
        $this->assertStringStartsWith('private/email-studio/', $img['top']['path']);
        Storage::disk('local')->assertExists($img['top']['path']);

        $this->actingAs(User::findOrFail(2))
            ->get(route('email-studio.image',[1,'top']))->assertForbidden();
    }
    public function test_legacy_html_table_structure_keeps_safe_gmail_spacing_but_strips_trackers(): void
    {
        $import = '<table role="presentation" width="600" cellpadding="0" cellspacing="0" ' .
            'style="border-collapse:collapse;width:100%;max-width:600px" onmouseover="evil()">' .
            '<tr><td align="center" valign="top" colspan="2" ' .
            'style="padding:20px 12px;text-align:center;background-color:#f3f6fa">' .
            '<strong>Welcome</strong><img src="https://tracker.example.invalid/a.png">' .
            '</td></tr></table>';

        $safe = app(EmailStudioHtml::class)->sanitize($import);

        $this->assertStringContainsString('width="600"', $safe);
        $this->assertStringContainsString('cellpadding="0"', $safe);
        $this->assertStringContainsString('align="center"', $safe);
        $this->assertStringContainsString('colspan="2"', $safe);
        $this->assertStringContainsString('border-collapse:collapse', $safe);
        $this->assertStringContainsString('padding:20px 12px', $safe);
        $this->assertStringNotContainsString('tracker.example.invalid', $safe);
        $this->assertStringNotContainsString('onmouseover', $safe);
        $this->assertStringNotContainsString('<img', $safe);
    }

    public function test_authorized_editor_exposes_local_html_import_without_remote_scripts(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('email-studio.create'))
            ->assertOk()
            ->assertSee('studio-import-html')
            ->assertSee('studio-import-mode')
            ->assertSee('email-studio/editor.js')
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_one_page_studio_has_all_five_screenshot_panels(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('email-studio.index'))
            ->assertOk()
            ->assertSee('1. Template Builder')
            ->assertSee('2. Live Preview')
            ->assertSee('3. Send Email')
            ->assertSee('4. Custom SMTP Manager')
            ->assertSee('5. Recent Email Attempts')
            ->assertSee('studio-import-html')
            ->assertSee('smtp-manager-select')
            ->assertSee('studio-preview')
            ->assertSee('email-studio/studio.css');
    }

    public function test_smtp_profile_password_is_encrypted_never_shown_and_blank_edit_retains_it(): void
    {
        $admin = User::findOrFail(1);
        $this->actingAs($admin);
        $details = [
            'name' => 'Office SMTP',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'office@example.org',
            'password' => 'Secret-App-Password-123',
            'from_address' => 'office@example.org',
            'from_name' => 'Join Gallagher',
        ];
        $this->post(route('email-studio.smtp.store'), $details)->assertRedirect();
        $id = DB::table('email_studio_smtp_profiles')->value('id');
        $this->assertNotNull($id);
        $saved = DB::table('email_studio_smtp_profiles')->first();
        $this->assertNotSame('Secret-App-Password-123', $saved->password_encrypted);
        $this->assertSame('Secret-App-Password-123',
            \Illuminate\Support\Facades\Crypt::decryptString($saved->password_encrypted));

        $this->get(route('email-studio.index'))
            ->assertOk()->assertDontSee('Secret-App-Password-123');

        $details['name'] = 'Office SMTP Updated';
        $details['password'] = '';
        $this->patch(route('email-studio.smtp.update', $id), $details)->assertRedirect();
        $this->assertDatabaseHas('email_studio_smtp_profiles', [
            'id' => $id, 'name' => 'Office SMTP Updated',
        ]);
        $this->assertSame('Secret-App-Password-123',
            \Illuminate\Support\Facades\Crypt::decryptString(
                DB::table('email_studio_smtp_profiles')->value('password_encrypted')
            ));
    }

    public function test_default_smtp_profile_is_selected_and_insecure_port_is_rejected(): void
    {
        $this->actingAs(User::findOrFail(1));
        $data = [
            'name' => 'Gmail SMTP', 'host' => 'smtp.gmail.com',
            'port' => 587, 'encryption' => 'tls',
            'username' => 'office@example.org',
            'password' => 'SecretTestPassword',
            'from_address' => 'office@example.org',
            'from_name' => 'Join Gallagher',
        ];
        $this->post(route('email-studio.smtp.store'), $data)->assertRedirect();
        $id = DB::table('email_studio_smtp_profiles')->value('id');
        $this->post(route('email-studio.smtp.default', $id))->assertRedirect();
        $this->assertSame((int) $id, app(\App\Services\StudioSmtpProfiles::class)->defaultId());

        $data['host'] = 'localhost';
        $data['port'] = 25;
        $this->post(route('email-studio.smtp.store'), $data)->assertSessionHasErrors();
        $this->assertDatabaseCount('email_studio_smtp_profiles', 1);
    }

    public function test_private_page_image_import_is_rendered_to_mail_and_protected_from_other_admins(): void
    {
        $id = $this->template();
        $this->actingAs(User::findOrFail(1))
            ->post(route('email-studio.visual.import', $id), [
                'visual_page_1' => UploadedFile::fake()->image('page1.png', 500, 700),
                'visual_page_2' => UploadedFile::fake()->image('page2.png', 500, 700),
            ])->assertRedirect();

        $this->assertDatabaseCount('email_studio_visual_pages', 2);
        $this->get(route('email-studio.edit', $id))
            ->assertOk()->assertSee('PDF → Email Visual Pages');
        $this->get(route('email-studio.visual.page', [$id, 1]))
            ->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->post(route('email-studio.send', $id), [
            'recipient' => 'client@example.invalid', 'pdf_mode' => 'none',
        ])->assertRedirect();
        Mail::assertSent(StudioEmail::class, function (StudioEmail $mail) {
            return $mail->hasTo('client@example.invalid')
                && count($mail->visualPages) === 2
                && str_contains($mail->visualPages[0]['private_path'], 'pdf-visual');
        });
        $this->actingAs(User::findOrFail(2))
            ->get(route('email-studio.visual.page', [$id, 1]))->assertForbidden();
    }

    public function test_duplicate_creates_independent_template_and_removal_keeps_send_history(): void
    {
        $id = $this->template();
        $this->actingAs(User::findOrFail(1));
        $this->post(route('email-studio.visual.import', $id), [
            'visual_page_1' => UploadedFile::fake()->image('page1.png', 320, 450),
        ])->assertRedirect();
        $oldPath = DB::table('email_studio_visual_pages')->value('private_path');

        $this->post(route('email-studio.duplicate', $id))->assertRedirect();
        $this->assertDatabaseCount('email_studio_templates', 2);
        $this->assertDatabaseCount('email_studio_visual_pages', 2);
        $copyPath = DB::table('email_studio_visual_pages')
            ->where('template_id', '!=', $id)->value('private_path');
        $this->assertNotSame($oldPath, $copyPath);
        Storage::disk('local')->assertExists($copyPath);

        DB::table('email_studio_send_logs')->insert([
            'template_id' => $id, 'actor_id' => 1,
            'recipient' => 'client@example.invalid', 'subject' => 'Test case',
            'result' => 'accepted', 'pdf_attached' => false, 'created_at' => now(),
        ]);
        $this->delete(route('email-studio.destroy', $id))->assertRedirect();
        $this->assertDatabaseCount('email_studio_templates', 1);
        $this->assertDatabaseHas('email_studio_send_logs', [
            'recipient' => 'client@example.invalid', 'template_id' => null,
        ]);
        Storage::disk('local')->assertExists($copyPath);
    }

    public function test_template_actions_and_smtp_profiles_are_forbidden_to_unprivileged_admin(): void
    {
        $id = $this->template();
        $this->actingAs(User::findOrFail(2))
            ->post(route('email-studio.duplicate', $id))->assertForbidden();
        $this->delete(route('email-studio.destroy', $id))->assertForbidden();
        $this->post(route('email-studio.smtp.store'), [
            'name'=>'Unapproved','host'=>'smtp.gmail.com','port'=>587,
            'encryption'=>'tls','username'=>'evil@example.org',
            'password'=>'secret','from_address'=>'evil@example.org',
            'from_name'=>'Unauthorised',
        ])->assertForbidden();
    }

    public function test_uploading_a_non_pdf_for_visual_conversion_is_rejected(): void
    {
        $id = $this->template();
        $this->actingAs(User::findOrFail(1))
            ->post(route('email-studio.visual.import', $id), [
                'pdf_visual' => UploadedFile::fake()->image('not-a-pdf.png', 50, 50),
            ])->assertSessionHasErrors('pdf_visual');
        $this->assertDatabaseCount('email_studio_visual_pages', 0);
    }

}
