<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateDocumentWebAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user_type');
            $table->string('password')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        (require database_path('migrations/2023_12_21_130613_create_permission_tables.php'))->up();
        (require database_path('migrations/2026_10_22_000004_create_private_applicant_documents_tables.php'))->up();

        DB::table('users')->insert([
            ['id' => 1, 'name' => 'One Applicant', 'email' => 'app1@example.invalid', 'user_type' => 'job_seeker'],
            ['id' => 2, 'name' => 'Another Applicant', 'email' => 'app2@example.invalid', 'user_type' => 'job_seeker'],
            ['id' => 3, 'name' => 'Employer', 'email' => 'employer@example.invalid', 'user_type' => 'employer'],
            ['id' => 4, 'name' => 'Admin Without Document Permission', 'email' => 'admin@example.invalid', 'user_type' => 'admin'],
        ]);
        DB::table('applicant_private_documents')->insert([
            'id' => 40, 'user_id' => 1, 'type' => 'passport',
            'title' => 'Personal Passport', 'status' => 'pending_review',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_unauthenticated_guest_is_redirected_from_private_document_workspace(): void
    {
        $this->get(route('jobseeker.private-documents.index'))->assertRedirect();
        $this->get(route('admin.private-documents.index'))->assertRedirect();
    }

    public function test_applicant_can_view_own_document_without_third_party_scripts(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('jobseeker.private-documents.show', 40))
            ->assertOk()
            ->assertSee('Personal Passport')
            ->assertSee('Upload a new revision')
            ->assertDontSee('maps.googleapis.com')
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_another_applicant_cannot_view_or_upload_a_revision_to_private_passport(): void
    {
        $this->actingAs(User::findOrFail(2))
            ->get(route('jobseeker.private-documents.show', 40))
            ->assertNotFound();

        $this->post(route('jobseeker.private-documents.revise', 40), [])
            ->assertNotFound();
    }

    public function test_employer_cannot_read_jobseeker_document_workspace(): void
    {
        $this->actingAs(User::findOrFail(3))
            ->get(route('jobseeker.private-documents.index'))
            ->assertForbidden();
    }

    public function test_admin_without_explicit_document_review_permission_is_denied(): void
    {
        // Permission has not been granted to this admin account.
        $this->actingAs(User::findOrFail(4))
            ->get(route('admin.private-documents.index'))
            ->assertForbidden();

        $this->post(route('admin.private-documents.decide', 40), [
            'action' => 'approve',
        ])->assertForbidden();
    }

    public function test_upload_uses_post_and_csrf_protected_web_route(): void
    {
        $route = app('router')->getRoutes()->getByName('jobseeker.private-documents.store');
        $this->assertContains('POST', $route->methods());
        $this->assertContains('web', $route->gatherMiddleware());
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('private.document.headers', $route->gatherMiddleware());
    }
    public function test_explicitly_authorized_reviewer_can_lock_and_unlock_with_audit(): void
    {
        \Spatie\Permission\Models\Permission::findOrCreate('review-private-documents', 'web');
        $admin = User::findOrFail(4);
        $admin->givePermissionTo('review-private-documents');

        $this->actingAs($admin)
            ->get(route('admin.private-documents.show', 40))
            ->assertOk()
            ->assertSee('Personal Passport')
            ->assertDontSee('maps.googleapis.com');

        $this->post(route('admin.private-documents.decide', 40), [
            'action' => 'lock',
            'reason' => 'Identity document is approved for restricted processing.',
        ])->assertRedirect(route('admin.private-documents.show', 40));

        $this->assertNotNull(DB::table('applicant_private_documents')
            ->where('id', 40)->value('locked_at'));
        $this->assertDatabaseHas('applicant_private_document_events', [
            'document_id' => 40, 'actor_id' => 4, 'action' => 'lock',
        ]);

        $this->post(route('admin.private-documents.decide', 40), [
            'action' => 'unlock', 'reason' => 'Applicant is permitted to submit a revision.',
        ])->assertRedirect(route('admin.private-documents.show', 40));

        $this->assertNull(DB::table('applicant_private_documents')
            ->where('id', 40)->value('locked_at'));
        $this->assertDatabaseCount('applicant_private_document_events', 2);
    }

    public function test_explicit_review_permission_is_not_implicitly_granted_to_admin(): void
    {
        \Spatie\Permission\Models\Permission::findOrCreate('review-private-documents', 'web');
        $this->actingAs(User::findOrFail(4))
            ->get(route('admin.private-documents.show', 40))
            ->assertForbidden();
    }

}
