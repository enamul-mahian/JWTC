<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PasswordOtpService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PasswordOtpServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config()->set('security.password_change_otp.enabled', true);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('email_otp_challenges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('purpose', 32);
            $table->string('code_hash');
            $table->string('payload_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('sent_at');
            $table->timestamps();
            $table->unique(['user_id', 'purpose']);
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Test Applicant',
            'email' => 'test-user@example.invalid',
            'password' => Hash::make('OldPassword123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function challenge(): object
    {
        return DB::table('email_otp_challenges')->first();
    }

    public function test_otp_does_not_change_password_until_valid_code_is_confirmed(): void
    {
        $user = User::findOrFail(1);
        $nextHash = Hash::make('NewPassword123');
        app(PasswordOtpService::class)->issue($user, $nextHash);

        $this->assertDatabaseCount('email_otp_challenges', 1);
        $this->assertTrue(Hash::check('OldPassword123', $user->fresh()->password));

        DB::table('email_otp_challenges')
            ->update(['code_hash' => Hash::make('123456')]);

        app(PasswordOtpService::class)->confirm($user, '123456');

        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
        $this->assertDatabaseCount('email_otp_challenges', 0);
    }

    public function test_expired_codes_are_rejected_without_changing_password(): void
    {
        $user = User::findOrFail(1);
        app(PasswordOtpService::class)->issue($user, Hash::make('NewPassword123'));
        DB::table('email_otp_challenges')->update([
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->subMinute(),
        ]);
        try {
            app(PasswordOtpService::class)->confirm($user, '123456');
            $this->fail('Expired OTP was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('otp', $exception->errors());
        }

        $this->assertTrue(Hash::check('OldPassword123', $user->fresh()->password));
    }

    public function test_invalid_otp_increments_attempts_and_cannot_be_replayed(): void
    {
        $user = User::findOrFail(1);
        app(PasswordOtpService::class)->issue($user, Hash::make('NewPassword123'));
        DB::table('email_otp_challenges')->update(['code_hash' => Hash::make('123456')]);

        try {
            app(PasswordOtpService::class)->confirm($user, '111111');
            $this->fail('Invalid code was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('otp', $exception->errors());
        }
        $this->assertSame(1, (int) $this->challenge()->attempts);

        app(PasswordOtpService::class)->confirm($user, '123456');

        $this->expectException(ValidationException::class);
        app(PasswordOtpService::class)->confirm($user, '123456');
    }

    public function test_requests_are_rate_limited_by_cooldown(): void
    {
        $user = User::findOrFail(1);
        app(PasswordOtpService::class)->issue($user, Hash::make('NewPassword123'));

        $this->expectException(ValidationException::class);
        app(PasswordOtpService::class)->issue($user, Hash::make('AnotherPassword123'));
    }
}
