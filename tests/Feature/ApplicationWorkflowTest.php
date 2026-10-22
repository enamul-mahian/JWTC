<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use App\Services\ApplicationStatusCatalog;
use App\Services\ApplicationStatusManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApplicationWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user_type');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->unsignedBigInteger('employer_id')->nullable();
            $table->integer('status')->nullable();
            $table->string('date_applied');
            $table->decimal('expected_salary', 8, 2);
            $table->timestamps();
        });

        Schema::create('application_status_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('progress');
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('is_terminal');
            $table->boolean('is_active');
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

        DB::table('application_status_definitions')->insert([
            ['id'=>1,'name'=>'Application received','progress'=>0,'sort_order'=>10,'is_terminal'=>0,'is_active'=>1],
            ['id'=>2,'name'=>'Under assessment','progress'=>20,'sort_order'=>20,'is_terminal'=>0,'is_active'=>1],
            ['id'=>11,'name'=>'Rejected','progress'=>0,'sort_order'=>110,'is_terminal'=>1,'is_active'=>0],
        ]);
        DB::table('users')->insert([
            ['id'=>1,'name'=>'Applicant','email'=>'applicant@example.invalid','user_type'=>'job_seeker'],
            ['id'=>2,'name'=>'Admin','email'=>'admin@example.invalid','user_type'=>'admin'],
        ]);
        DB::table('applications')->insert([
            'id'=>10,'user_id'=>1,'job_id'=>20,'employer_id'=>30,'status'=>1,
            'date_applied'=>now()->toDateString(),'expected_salary'=>1000,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    public function test_status_transition_records_immutable_snapshot_and_actor(): void
    {
        $application = app(ApplicationStatusManager::class)->transition(
            Application::findOrFail(10), 2, User::findOrFail(2),
            'admin', 'Next document review'
        );
        $this->assertSame(2, (int) $application->status);
        $this->assertDatabaseHas('application_status_events', [
            'application_id'=>10, 'actor_id'=>2,
            'from_status_id'=>1, 'to_status_id'=>2,
            'status_name'=>'Under assessment', 'progress'=>20,
            'note'=>'Next document review', 'source'=>'admin',
        ]);

        // Renaming a stage does not rewrite the old audit snapshot.
        DB::table('application_status_definitions')->where('id',2)
            ->update(['name'=>'Updated assessment label','progress'=>30]);
        $this->assertDatabaseHas('application_status_events', [
            'status_name'=>'Under assessment','progress'=>20,
        ]);
    }

    public function test_same_status_does_not_create_duplicate_timeline_event(): void
    {
        app(ApplicationStatusManager::class)->transition(
            Application::findOrFail(10), 1, User::findOrFail(2), 'admin'
        );
        $this->assertDatabaseCount('application_status_events', 0);
    }

    public function test_disabled_or_unknown_status_cannot_be_saved(): void
    {
        foreach ([11, 999] as $status) {
            try {
                app(ApplicationStatusManager::class)->transition(
                    Application::findOrFail(10), $status, User::findOrFail(2), 'admin'
                );
                $this->fail('Disabled or invalid status was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('status', $exception->errors());
            }
        }
        $this->assertDatabaseCount('application_status_events', 0);
        $this->assertDatabaseHas('applications', ['id'=>10,'status'=>1]);
    }

    public function test_catalog_uses_admin_configuration_and_retains_legacy_fallback(): void
    {
        $catalog = app(ApplicationStatusCatalog::class);
        $this->assertSame('Under assessment', $catalog->find(2)['name']);
        $this->assertSame(20, $catalog->find(2)['progress']);
        $this->assertCount(2, $catalog->active());
        Schema::drop('application_status_definitions');
        $this->assertSame(11, count($catalog->all()));
        $this->assertSame('Visa Processing Complete', $catalog->find(10)['name']);
    }
}
