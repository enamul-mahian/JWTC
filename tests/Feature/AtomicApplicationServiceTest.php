<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\User;
use App\Services\ApplicationSubmissionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AtomicApplicationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user_type');
            $table->string('password')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('title');
            $table->boolean('is_publish')->default(true);
            $table->date('application_deadline')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('unique_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('code')->nullable()->unique();
            $table->boolean('is_used')->default(false);
            $table->date('date')->nullable();
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('employer_id');
            $table->unsignedBigInteger('job_id');
            $table->integer('status');
            $table->string('date_applied');
            $table->decimal('expected_salary', 8, 2);
            $table->timestamps();
        });

        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Applicant', 'email' => 'applicant@example.invalid', 'user_type' => 'job_seeker'],
            ['id' => 2, 'name' => 'Employer', 'email' => 'employer@example.invalid', 'user_type' => 'employer'],
        ]);

        DB::table('jobs')->insert([
            'id' => 10, 'company_id' => 99, 'title' => 'Test Position',
            'is_publish' => 1, 'application_deadline' => now()->addWeek()->toDateString()
        ]);

        DB::table('unique_codes')->insert([
            'user_id' => 1, 'code' => 'SECURE-CODE-1', 'is_used' => 0,
        ]);
    }

    public function test_one_code_is_consumed_with_exactly_one_application(): void
    {
        $application = app(ApplicationSubmissionService::class)->submit(
            User::findOrFail(1), Job::findOrFail(10), 1500.00
        );

        $this->assertSame(1, (int) $application->status);
        $this->assertSame(99, (int) $application->employer_id);
        $this->assertDatabaseCount('applications', 1);
        $this->assertDatabaseHas('unique_codes', [
            'user_id' => 1, 'code' => 'SECURE-CODE-1', 'is_used' => 1,
        ]);

        try {
            app(ApplicationSubmissionService::class)->submit(
                User::findOrFail(1), Job::findOrFail(10), 2000.00
            );
            $this->fail('Duplicate application was accepted.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('already applied', $e->getMessage());
        }

        $this->assertDatabaseCount('applications', 1);
    }

    public function test_non_applicant_cannot_consume_a_code(): void
    {
        $this->expectException(\DomainException::class);

        app(ApplicationSubmissionService::class)->submit(
            User::findOrFail(2), Job::findOrFail(10), 1500.00
        );
    }

    public function test_unpublished_job_does_not_consume_an_approved_code(): void
    {
        DB::table('jobs')->where('id', 10)->update(['is_publish' => 0]);

        try {
            app(ApplicationSubmissionService::class)->submit(
                User::findOrFail(1), Job::findOrFail(10), 1500.00
            );
            $this->fail('Unpublished job accepted application.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('not accepting', $e->getMessage());
        }

        $this->assertDatabaseCount('applications', 0);
        $this->assertDatabaseHas('unique_codes', ['user_id' => 1, 'is_used' => 0]);
    }
}
