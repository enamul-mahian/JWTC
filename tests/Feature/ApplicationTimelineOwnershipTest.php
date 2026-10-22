<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApplicationTimelineOwnershipTest extends TestCase
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
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('job_id');
            $table->integer('status')->nullable();
            $table->string('date_applied');
            $table->decimal('expected_salary', 8, 2);
            $table->timestamps();
        });
        DB::table('users')->insert([
            ['id'=>1,'name'=>'Original Applicant','email'=>'person1@example.invalid','user_type'=>'job_seeker'],
            ['id'=>2,'name'=>'Other Applicant','email'=>'person2@example.invalid','user_type'=>'job_seeker'],
        ]);
        DB::table('applications')->insert([
            'id'=>55,'user_id'=>1,'job_id'=>66,'status'=>4,
            'date_applied'=>now()->toDateString(),'expected_salary'=>1000,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    public function test_other_applicant_cannot_read_private_web_timeline(): void
    {
        $this->actingAs(User::findOrFail(2))
            ->get('/job_seeker/applications/55/timeline')
            ->assertNotFound();
    }

    public function test_other_applicant_cannot_read_private_api_timeline(): void
    {
        Sanctum::actingAs(User::findOrFail(2), ['*']);
        $this->getJson('/api/jobseeker/applications/55/timeline')
            ->assertNotFound();
    }

    public function test_unauthenticated_applicant_api_cannot_read_timeline(): void
    {
        $this->getJson('/api/jobseeker/applications/55/timeline')
            ->assertUnauthorized();
    }
}
