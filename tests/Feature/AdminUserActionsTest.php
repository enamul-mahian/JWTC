<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUserActionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email')->unique();
            $t->string('user_type'); $t->string('password');
            $t->string('phone')->nullable(); $t->tinyInteger('status')->default(1);
            $t->string('photo')->nullable(); $t->rememberToken(); $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('applicant_private_documents', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id');
        });
        Schema::create('applicant_private_document_versions', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('document_id');
            $t->unsignedBigInteger('user_id'); $t->string('disk'); $t->string('path');
        });
        Schema::create('personal_access_tokens', function (Blueprint $t) {
            $t->id(); $t->morphs('tokenable'); $t->string('name')->default('token');
            $t->string('token')->default('hashed_token'); $t->timestamps();
        });
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin@example.invalid',
            'user_type' => 'admin', 'password' => Hash::make('Original!AdminPass123'),
        ]);
    }

    private function employer(): User
    {
        return User::create([
            'name' => 'Employer', 'email' => 'employer@example.invalid',
            'user_type' => 'employer', 'password' => Hash::make('OldEmployer!123'),
        ]);
    }

    public function test_regular_user_cannot_set_or_delete_another_account(): void
    {
        $employer = User::create([
            'name' => 'Applicant', 'email' => 'applicant@example.invalid',
            'user_type' => 'job_seeker', 'password' => Hash::make('OldEmployer!123'),
        ]);
        $other = User::create([
            'name' => 'Other', 'email' => 'other@example.invalid',
            'user_type' => 'job_seeker', 'password' => Hash::make('Other!Password123'),
        ]);
        $this->actingAs($employer)->post(route('admin.users.password', $other->id), [
            'admin_password' => 'OldEmployer!123',
            'password' => 'NewUser!Password123',
            'password_confirmation' => 'NewUser!Password123',
        ])->assertForbidden();
        $this->delete(route('admin.users.destroy', $other->id), [
            'admin_password' => 'OldEmployer!123', 'confirm_email' => $other->email,
        ])->assertForbidden();
        $this->assertFalse($other->fresh()->trashed());
    }

    public function test_admin_must_verify_own_password_to_set_strong_target_password(): void
    {
        $actor = $this->admin(); $target = $this->employer();
        $this->actingAs($actor);
        $payload = [
            'admin_password' => 'WRONG',
            'password' => 'NewUser!Password123',
            'password_confirmation' => 'NewUser!Password123',
        ];
        $this->post(route('admin.users.password', $target->id), $payload)
            ->assertSessionHasErrors('admin_password');
        $this->assertTrue(Hash::check('OldEmployer!123', $target->fresh()->password));
        $payload['admin_password'] = 'Original!AdminPass123';
        $this->post(route('admin.users.password', $target->id), $payload)
            ->assertRedirect(route('admin.users.actions', $target->id));
        $this->assertTrue(Hash::check('NewUser!Password123', $target->fresh()->password));
    }

    public function test_admin_cannot_change_another_administrator_or_self(): void
    {
        $actor = $this->admin(); $otherAdmin = $this->adminWithEmail('second@example.invalid');
        $this->actingAs($actor);
        $payload = [
            'admin_password' => 'Original!AdminPass123',
            'password' => 'NewUser!Password123',
            'password_confirmation' => 'NewUser!Password123',
        ];
        $this->post(route('admin.users.password', $otherAdmin->id), $payload)->assertForbidden();
        $this->delete(route('admin.users.destroy', $otherAdmin->id), [
            'admin_password' => 'Original!AdminPass123',
            'confirm_email' => $otherAdmin->email,
        ])->assertForbidden();
        $this->get(route('admin.users.actions', $actor->id))->assertForbidden();
    }

    private function adminWithEmail(string $email): User
    {
        return User::create([
            'name' => 'Another admin', 'email' => $email, 'user_type' => 'super_admin',
            'password' => Hash::make('Original!AdminPass123'),
        ]);
    }

    public function test_delete_requires_email_confirmation_and_purges_owned_private_document(): void
    {
        $actor = $this->admin(); $target = $this->employer();
        $this->actingAs($actor);
        $path = 'private-documents/'.$target->id.'/proof.pdf';
        Storage::disk('local')->put($path, 'synthetic test file');
        DB::table('applicant_private_documents')->insert(['id' => 42, 'user_id' => $target->id]);
        DB::table('applicant_private_document_versions')->insert([
            'document_id' => 42, 'user_id' => $target->id,
            'disk' => 'local', 'path' => $path,
        ]);
        $this->delete(route('admin.users.destroy', $target->id), [
            'admin_password' => 'Original!AdminPass123', 'confirm_email' => 'WRONG',
        ])->assertSessionHasErrors('confirm_email');
        $this->assertTrue(Storage::disk('local')->exists($path));

        $this->delete(route('admin.users.destroy', $target->id), [
            'admin_password' => 'Original!AdminPass123', 'confirm_email' => $target->email,
        ])->assertRedirect(route('user-management.index'));
        $this->assertSoftDeleted('users', ['id' => $target->id]);
        $this->assertFalse(Storage::disk('local')->exists($path));
        $this->assertDatabaseCount('applicant_private_documents', 0);
        $this->assertDatabaseCount('applicant_private_document_versions', 0);
        $this->assertFalse($actor->fresh()->trashed());
    }

    public function test_phone_fields_are_optional_for_admin_forms(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/DashboardController.php'));
        $this->assertGreaterThanOrEqual(2, substr_count($controller, "'phone' => 'nullable|string|max:20'"));
        foreach (['create', 'edit'] as $page) {
            $blade = file_get_contents(resource_path('views/admin/userCreate/'.$page.'.blade.php'));
            $this->assertStringContainsString('Phone (optional)', $blade);
        }
    }
}
