<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyRecordPreservationTest extends TestCase
{
    /**
     * Deliberately minimal legacy schema. The data is synthetic, never copied
     * from real applicants, and this does NOT replace a verified MySQL restore.
     */
    public function test_new_private_modules_do_not_modify_existing_applicant_job_upload_or_payment_rows(): void
    {
        Schema::create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('user_type');
        });
        Schema::create('jobs', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
        });
        Schema::create('applications', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('job_id');
            $table->unsignedInteger('status');
        });
        Schema::create('uploaded_cvs', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('file');
        });
        Schema::create('jobseeker_uploads', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('file');
        });
        Schema::create('payment_credentials', static function (Blueprint $table): void {
            $table->id();
            $table->string('provider');
            $table->string('reference');
        });

        DB::table('users')->insert([
            'id' => 1001, 'name' => 'Synthetic Applicant', 'email' => 'test@example.invalid',
            'user_type' => 'job_seeker',
        ]);
        DB::table('jobs')->insert([
            'id' => 2001, 'user_id' => 1001, 'title' => 'Legacy Job',
        ]);
        DB::table('applications')->insert([
            'id' => 3001, 'user_id' => 1001, 'job_id' => 2001, 'status' => 4,
        ]);
        DB::table('uploaded_cvs')->insert([
            'id' => 4001, 'user_id' => 1001, 'file' => 'original-cv-unchanged.pdf',
        ]);
        DB::table('jobseeker_uploads')->insert([
            'id' => 5001, 'user_id' => 1001, 'file' => 'original-passport-unchanged.pdf',
        ]);
        DB::table('payment_credentials')->insert([
            'id' => 6001, 'provider' => 'legacy-provider', 'reference' => 'old-reference',
        ]);

        $legacyTables = [
            'users', 'jobs', 'applications', 'uploaded_cvs',
            'jobseeker_uploads', 'payment_credentials',
        ];
        $snapshot = [];
        foreach ($legacyTables as $table) {
            $snapshot[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }

        foreach ([
            '2026_10_22_000003_create_application_status_workflow_tables.php',
            '2026_10_22_000004_create_private_applicant_documents_tables.php',
            '2026_10_22_000012_create_private_application_chats.php',
            '2026_10_22_000014_create_support_ticket_and_refund_tables.php',
        ] as $migration) {
            (require database_path('migrations/' . $migration))->up();
        }

        foreach ($legacyTables as $table) {
            $this->assertSame($snapshot[$table],
                DB::table($table)->orderBy('id')->get()->toJson(),
                $table . ' changed during additive module migrations.');
        }
        $this->assertDatabaseHas('applications', [
            'id' => 3001, 'status' => 4,
        ]);
        $this->assertTrue(Schema::hasTable('applicant_private_documents'));
        $this->assertTrue(Schema::hasTable('support_tickets'));
        $this->assertTrue(Schema::hasTable('application_chat_threads'));
        $this->assertTrue(Schema::hasTable('application_status_events'));
    }
}
