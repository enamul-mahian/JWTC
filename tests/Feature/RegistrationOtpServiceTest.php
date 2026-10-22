<?php

namespace Tests\Feature;

use App\Enum\UserType;
use App\Models\User;
use App\Services\RegistrationOtpService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationOtpServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config()->set('security.registration_otp.enabled', true);

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('phone')->nullable()->unique();
            $t->string('password');
            $t->string('user_type');
            $t->unsignedBigInteger('job_category_id')->nullable();
            $t->tinyInteger('status')->nullable();
            $t->timestamp('email_verified_at')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('pending_registrations', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('email')->unique();
            $t->text('encrypted_payload');
            $t->string('code_hash');
            $t->unsignedTinyInteger('attempts')->default(0);
            $t->timestamp('expires_at');
            $t->timestamp('sent_at');
            $t->timestamps();
        });

        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('guard_name');
            $t->timestamps();
            $t->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_roles', function (Blueprint $t) {
            $t->unsignedBigInteger('role_id');
            $t->string('model_type');
            $t->unsignedBigInteger('model_id');
            $t->primary(['role_id', 'model_type', 'model_id']);
        });

        Schema::create('personal_information', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->timestamps();
        });

        Role::create(['name' => 'Job Seeker', 'guard_name' => 'web']);
        Role::create(['name' => 'Employer', 'guard_name' => 'web']);
    }

    private function payload(string $type = UserType::JOB_SEEKER): array
    {
        return [
            'name' => 'Applicant Example',
            'email' => 'Applicant@Example.invalid',
            'phone' => '01230000000',
            'user_type' => $type,
            'job_category_id' => 1,
            'password' => 'SecurePassword123',
        ];
    }

    public function test_new_user_is_created_only_after_valid_email_code(): void
    {
        $id = app(RegistrationOtpService::class)->issue($this->payload());

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('pending_registrations', 1);
        $this->assertStringNotContainsString('SecurePassword123',
            DB::table('pending_registrations')->first()->encrypted_payload);

        DB::table('pending_registrations')->where('id', $id)
            ->update(['code_hash' => Hash::make('123456')]);

        $user = app(RegistrationOtpService::class)->confirm($id, '123456');
        $this->assertSame('applicant@example.invalid', $user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('SecurePassword123', $user->password));
        $this->assertTrue($user->hasRole('Job Seeker'));
        $this->assertDatabaseCount('pending_registrations', 0);
        $this->assertDatabaseHas('personal_information', ['user_id' => $user->id]);
    }

    public function test_invalid_code_increments_attempts_and_never_creates_an_account(): void
    {
        $id = app(RegistrationOtpService::class)->issue($this->payload());
        DB::table('pending_registrations')->where('id', $id)
            ->update(['code_hash' => Hash::make('123456')]);

        try {
            app(RegistrationOtpService::class)->confirm($id, '222222');
            $this->fail('Invalid OTP accepted');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('otp', $e->errors());
        }
        $this->assertSame(1, (int) DB::table('pending_registrations')->first()->attempts);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_expired_code_does_not_create_an_account(): void
    {
        $id = app(RegistrationOtpService::class)->issue($this->payload());
        DB::table('pending_registrations')->where('id', $id)
            ->update(['expires_at' => now()->subMinute(), 'code_hash' => Hash::make('123456')]);

        $this->expectException(ValidationException::class);
        app(RegistrationOtpService::class)->confirm($id, '123456');
    }

    public function test_requesting_a_second_code_immediately_is_throttled(): void
    {
        app(RegistrationOtpService::class)->issue($this->payload());

        $this->expectException(ValidationException::class);
        app(RegistrationOtpService::class)->issue($this->payload());
    }

    public function test_only_approved_user_types_can_request_otp(): void
    {
        $this->expectException(ValidationException::class);
        app(RegistrationOtpService::class)->issue($this->payload('super_admin'));
    }
}
