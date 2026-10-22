<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminPasswordOtpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config()->set('security.password_change_otp.enabled', true);
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('user_type');
            $t->string('password');
            $t->rememberToken();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('email_otp_challenges', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('purpose', 32);
            $t->string('code_hash');
            $t->string('payload_hash');
            $t->unsignedTinyInteger('attempts')->default(0);
            $t->timestamp('expires_at');
            $t->timestamp('sent_at');
            $t->timestamps();
            $t->unique(['user_id', 'purpose']);
        });
        Schema::create('personal_access_tokens', function (Blueprint $t) {
            $t->id();
            $t->morphs('tokenable');
            $t->string('name');
            $t->string('token', 64)->unique();
            $t->text('abilities')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });
        DB::table('users')->insert([
            'id' => 1, 'name' => 'Admin', 'email' => 'admin@example.invalid',
            'user_type' => 'Admin', 'password' => Hash::make('OldPassword123'),
            'remember_token' => 'prior-remember-token',
        ]);
    }

    private function passwordData(): array
    {
        return [
            'current_password' => 'OldPassword123',
            'new_password' => 'NewPassword123',
            'new_password_confirmation' => 'NewPassword123',
        ];
    }

    public function test_admin_password_waits_for_correct_email_code_and_revokes_tokens(): void
    {
        $admin = User::findOrFail(1);
        $admin->createToken('old-session');
        $this->actingAs($admin)->from('/admin/admin-change-password')
            ->post(route('update-admin-password'), $this->passwordData())
            ->assertRedirect('/admin/admin-change-password')->assertSessionHas('otp_sent');
        $this->assertTrue(Hash::check('OldPassword123', $admin->fresh()->password));
        $this->assertDatabaseCount('email_otp_challenges', 1);
        $challenge = DB::table('email_otp_challenges')->first();
        $this->assertTrue(Hash::check('NewPassword123', $challenge->payload_hash));
        $this->assertNotSame('NewPassword123', $challenge->payload_hash);
        DB::table('email_otp_challenges')->update(['code_hash' => Hash::make('123456')]);

        $this->post(route('account.password.verify_otp'), ['otp' => '111111'])
            ->assertSessionHasErrors('otp');
        $this->assertTrue(Hash::check('OldPassword123', $admin->fresh()->password));
        $this->assertAuthenticated();

        $this->post(route('account.password.verify_otp'), ['otp' => '123456'])
            ->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NewPassword123', $admin->fresh()->password));
        $this->assertDatabaseCount('email_otp_challenges', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertGuest();
    }

    public function test_wrong_current_password_never_creates_challenge_or_changes_password(): void
    {
        $data = $this->passwordData();
        $data['current_password'] = 'WrongPassword123';
        $admin = User::findOrFail(1);
        $this->actingAs($admin)->post(route('update-admin-password'), $data)
            ->assertSessionHasErrors('current_password');
        $this->assertDatabaseCount('email_otp_challenges', 0);
        $this->assertTrue(Hash::check('OldPassword123', $admin->fresh()->password));
    }

    public function test_non_admin_cannot_enter_admin_password_change_flow(): void
    {
        DB::table('users')->where('id', 1)->update(['user_type' => 'employer']);
        $this->actingAs(User::findOrFail(1))
            ->postJson(route('update-admin-password'), $this->passwordData())
            ->assertForbidden();
        $this->assertDatabaseCount('email_otp_challenges', 0);
    }

    public function test_flag_off_preserves_legacy_change_but_revokes_tokens_and_stale_otp(): void
    {
        $admin = User::findOrFail(1);
        $admin->createToken('old-session');
        app(\App\Services\PasswordOtpService::class)->issue($admin, Hash::make('StalePassword123'));
        config()->set('security.password_change_otp.enabled', false);
        $this->actingAs($admin)->post(route('update-admin-password'), $this->passwordData())
            ->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NewPassword123', $admin->fresh()->password));
        $this->assertNotSame('prior-remember-token', $admin->fresh()->remember_token);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('email_otp_challenges', 0);
        $this->assertGuest();
    }
}
