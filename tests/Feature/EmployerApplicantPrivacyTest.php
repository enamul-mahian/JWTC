<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmployerApplicantPrivacyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(static fn ($user, $ability) => $ability === 'post-job' ? true : null);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user_type');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('company_name');
        });
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');
            $table->string('title');
            $table->softDeletes();
        });
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('job_id');
            $table->integer('status')->nullable();
            $table->timestamps();
        });
        Schema::create('employer_cv_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employer_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('application_id');
            $table->integer('count');
            $table->timestamps();
        });
        Schema::create('application_status_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->unsignedBigInteger('from_status_id')->nullable();
            $table->unsignedBigInteger('to_status_id');
            $table->string('status_name');
            $table->unsignedTinyInteger('progress');
            $table->string('source');
            $table->text('note')->nullable();
            $table->timestamp('created_at');
        });

        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Employer A', 'email' => 'employer-a@example.invalid', 'user_type' => 'employer'],
            ['id' => 2, 'name' => 'Employer B', 'email' => 'employer-b@example.invalid', 'user_type' => 'employer'],
            ['id' => 3, 'name' => 'Applicant A', 'email' => 'applicant-a@example.invalid', 'user_type' => 'job_seeker'],
            ['id' => 4, 'name' => 'Applicant B', 'email' => 'applicant-b@example.invalid', 'user_type' => 'job_seeker'],
        ]);
        DB::table('companies')->insert([
            ['id' => 11, 'user_id' => 1, 'company_name' => 'Tenant A'],
            ['id' => 22, 'user_id' => 2, 'company_name' => 'Tenant B'],
        ]);
        DB::table('jobs')->insert([
            ['id' => 101, 'user_id' => 1, 'company_id' => 11, 'title' => 'Tenant A job'],
            ['id' => 202, 'user_id' => 2, 'company_id' => 22, 'title' => 'Tenant B job'],
        ]);
        DB::table('applications')->insert([
            ['id' => 301, 'user_id' => 3, 'job_id' => 101, 'status' => 1],
            ['id' => 302, 'user_id' => 4, 'job_id' => 202, 'status' => 1],
        ]);
    }

    public function test_employer_cannot_view_another_tenants_applicant_list(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('view.application', ['id' => 202]))
            ->assertNotFound();
    }

    public function test_employer_cannot_view_another_tenants_cv_or_substitute_user_id(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('view.resume', ['user_id' => 4, 'applicationId' => 302]))
            ->assertNotFound();

        $this->get(route('view.resume', ['user_id' => 4, 'applicationId' => 301]))
            ->assertNotFound();

        $this->assertDatabaseCount('employer_cv_views', 0);
    }

    public function test_employer_cannot_mutate_other_tenants_application(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->postJson(route('shortList.cv'), ['applicationId' => 302, 'status' => 2])
            ->assertNotFound();

        $this->assertDatabaseHas('applications', ['id' => 302, 'status' => 1]);
        $this->assertDatabaseCount('application_status_events', 0);
    }

    public function test_only_shortlist_or_interview_are_supported_by_legacy_shortcut(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->postJson(route('shortList.cv'), ['applicationId' => 301, 'status' => 10])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        $this->assertDatabaseHas('applications', ['id' => 301, 'status' => 1]);
    }

    public function test_jobseeker_cannot_access_employer_application_workflow(): void
    {
        $this->actingAs(User::findOrFail(3))
            ->get(route('view.application', ['id' => 101]))
            ->assertForbidden();

        $this->postJson(route('shortList.cv'), ['applicationId' => 301, 'status' => 2])
            ->assertForbidden();
    }

    public function test_status_update_is_audited_instead_of_silent_direct_write(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->postJson(route('shortList.cv'), ['applicationId' => 301, 'status' => 2])
            ->assertOk()
            ->assertJsonPath('status', 2);

        $this->assertDatabaseHas('applications', ['id' => 301, 'status' => 2]);
        $this->assertDatabaseHas('application_status_events', [
            'application_id' => 301, 'actor_id' => 1,
            'from_status_id' => 1, 'to_status_id' => 2, 'source' => 'employer',
        ]);
    }
}
