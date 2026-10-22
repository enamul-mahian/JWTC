<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use App\Services\InterviewManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InterviewManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('user_type');
            $t->string('password')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('companies', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('company_name')->nullable();
            $t->timestamps();
        });

        Schema::create('jobs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('company_id');
            $t->string('title');
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('applications', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('job_id');
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('employer_id');
            $t->integer('status');
            $t->string('date_applied');
            $t->decimal('expected_salary', 8, 2);
            $t->timestamps();
        });

        (require database_path('migrations/2023_12_21_130613_create_permission_tables.php'))->up();
        (require database_path('migrations/2026_10_22_000006_create_application_interview_tables.php'))->up();

        DB::table('users')->insert([
            ['id'=>1,'name'=>'Applicant','email'=>'applicant@example.invalid','user_type'=>'job_seeker'],
            ['id'=>2,'name'=>'Other Applicant','email'=>'other@example.invalid','user_type'=>'job_seeker'],
            ['id'=>3,'name'=>'First Employer','email'=>'employer1@example.invalid','user_type'=>'employer'],
            ['id'=>4,'name'=>'Other Employer','email'=>'employer2@example.invalid','user_type'=>'employer'],
            ['id'=>5,'name'=>'Admin','email'=>'admin@example.invalid','user_type'=>'admin'],
        ]);
        DB::table('companies')->insert([
            ['id'=>10,'user_id'=>3,'company_name'=>'First Company'],
            ['id'=>20,'user_id'=>4,'company_name'=>'Other Company'],
        ]);
        DB::table('jobs')->insert([
            ['id'=>50,'user_id'=>3,'company_id'=>10,'title'=>'First Job'],
            ['id'=>60,'user_id'=>4,'company_id'=>20,'title'=>'Second Job'],
        ]);
        DB::table('applications')->insert([
            'id'=>99,'job_id'=>50,'user_id'=>1,'employer_id'=>10,
            'status'=>1,'date_applied'=>now()->toDateString(),
            'expected_salary'=>1000,
        ]);

        Permission::findOrCreate('post-job', 'web');
        Permission::findOrCreate('manage-interviews', 'web');
        User::findOrFail(3)->givePermissionTo('post-job');
        User::findOrFail(4)->givePermissionTo('post-job');
    }

    private function applicant(): Application
    {
        return Application::findOrFail(99);
    }

    private function scheduleData(string $action = 'schedule'): array
    {
        return [
            'action' => $action,
            'starts_at_local' => now('Asia/Dhaka')->addDays(2)->format('Y-m-d\TH:i'),
            'timezone' => 'Asia/Dhaka',
            'duration_minutes' => 45,
            'meeting_url' => 'https://meet.example.org/room-123',
            'instructions' => 'Please join 10 minutes early.',
            'note' => 'Interview invitation sent.',
        ];
    }

    private function seedInterview(): int
    {
        return DB::table('application_interviews')->insertGetId([
            'application_id'=>99,'organizer_id'=>3,
            'starts_at_utc'=>now('UTC')->addDays(2),
            'duration_minutes'=>30,'timezone'=>'UTC',
            'meeting_url'=>'https://meet.example.org/private-room',
            'status'=>'scheduled',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    public function test_organizer_schedule_converts_timezone_and_creates_one_audit_event(): void
    {
        $data = $this->scheduleData();
        $expectedUtc = \Illuminate\Support\Carbon::createFromFormat(
            '!Y-m-d\TH:i', $data['starts_at_local'], 'Asia/Dhaka'
        )->utc()->format('Y-m-d H:i:s');

        $meeting = app(InterviewManager::class)->schedule(
            $this->applicant(), User::findOrFail(3), $data, 'schedule'
        );

        $this->assertSame('scheduled', $meeting->status);
        $this->assertSame($expectedUtc, $meeting->starts_at_utc);
        $this->assertDatabaseCount('application_interviews', 1);
        $this->assertDatabaseHas('application_interview_events', [
            'application_id'=>99,'actor_id'=>3,
            'action'=>'schedule','after_status'=>'scheduled',
        ]);

        try {
            app(InterviewManager::class)->schedule(
                $this->applicant(), User::findOrFail(3), $data, 'schedule'
            );
            $this->fail('Duplicate schedule accepted');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('action', $e->errors());
        }
        $this->assertDatabaseCount('application_interviews', 1);
    }

    public function test_applicant_can_confirm_then_organizer_can_reschedule_with_audit(): void
    {
        $this->seedInterview();
        $service = app(InterviewManager::class);

        $confirmed = $service->action($this->applicant(), User::findOrFail(1), 'confirm');
        $this->assertSame('confirmed', $confirmed->status);

        $rescheduled = $service->schedule(
            $this->applicant(), User::findOrFail(3), $this->scheduleData('reschedule'),
            'reschedule'
        );
        $this->assertSame('scheduled', $rescheduled->status);
        $this->assertDatabaseCount('application_interview_events', 2);
        $this->assertDatabaseHas('application_interview_events', [
            'application_id'=>99,'action'=>'reschedule','before_status'=>'confirmed',
            'after_status'=>'scheduled',
        ]);
    }

    public function test_other_employer_cannot_schedule_or_view_another_employers_applicant(): void
    {
        $service = app(InterviewManager::class);
        $this->assertFalse($service->canManage(User::findOrFail(4), $this->applicant()));
        $this->actingAs(User::findOrFail(4))
            ->getJson('/api/interviews/manage/99')->assertForbidden();
        $this->postJson('/api/interviews/manage/99/schedule', $this->scheduleData())
            ->assertForbidden();
        $this->assertDatabaseCount('application_interviews', 0);
    }

    public function test_other_applicant_cannot_view_or_confirm_private_interview(): void
    {
        $this->seedInterview();
        $this->actingAs(User::findOrFail(2))
            ->get('/job_seeker/interviews/99')->assertNotFound();
        $this->postJson('/api/interviews/my/99/reply', [
            'action'=>'confirm',
        ])->assertForbidden();
    }

    public function test_admin_without_explicit_interview_permission_is_denied(): void
    {
        $this->actingAs(User::findOrFail(5))
            ->get('/interviews/manage')->assertForbidden();
        $this->get('/interviews/manage/99')->assertForbidden();
    }

    public function test_manager_rejects_insecure_meeting_links_and_keeps_database_clean(): void
    {
        $data = $this->scheduleData();
        $data['meeting_url'] = 'http://unsecured.example.org/meeting';

        try {
            app(InterviewManager::class)->schedule(
                $this->applicant(), User::findOrFail(3), $data, 'schedule'
            );
            $this->fail('Insecure meeting URL was accepted.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('meeting_url', $e->errors());
        }

        $this->assertDatabaseCount('application_interviews', 0);
        $this->assertDatabaseCount('application_interview_events', 0);
    }

    public function test_cancelled_appointment_cannot_be_confirmed_and_completed_future_is_denied(): void
    {
        $this->seedInterview();
        $service = app(InterviewManager::class);

        try {
            $service->action($this->applicant(), User::findOrFail(3), 'complete');
            $this->fail('Future interview was marked completed.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('action', $e->errors());
        }

        $cancelled = $service->action(
            $this->applicant(), User::findOrFail(3), 'cancel', 'Organizer is unavailable.'
        );
        $this->assertSame('cancelled', $cancelled->status);

        try {
            $service->action($this->applicant(), User::findOrFail(1), 'confirm');
            $this->fail('Cancelled interview confirmation was accepted.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('action', $e->errors());
        }
        $this->assertDatabaseCount('application_interview_events', 1);
    }

    public function test_applicant_cannot_cancel_organizer_interview(): void
    {
        $this->seedInterview();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(InterviewManager::class)->action(
            $this->applicant(), User::findOrFail(1), 'cancel'
        );
    }

    public function test_assigned_employer_can_view_own_interview_and_meeting_url_is_not_public(): void
    {
        $this->seedInterview();
        $this->actingAs(User::findOrFail(3))
            ->get('/interviews/manage/99')
            ->assertOk()
            ->assertSee('https://meet.example.org/private-room')
            ->assertHeader('X-Frame-Options','DENY');

        auth()->logout();
        $this->get('/interviews/manage/99')
            ->assertRedirect();
    }
}
