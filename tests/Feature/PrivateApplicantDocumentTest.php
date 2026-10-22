<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PrivateApplicantDocumentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user_type');
            $table->string('password')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Use the real additive migration, not an approximation of its schema.
        (require database_path('migrations/2026_10_22_000004_create_private_applicant_documents_tables.php'))->up();

        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Applicant One', 'email' => 'applicant1@example.invalid', 'user_type' => 'job_seeker'],
            ['id' => 2, 'name' => 'Applicant Two', 'email' => 'applicant2@example.invalid', 'user_type' => 'job_seeker'],
            ['id' => 3, 'name' => 'Employer', 'email' => 'employer@example.invalid', 'user_type' => 'employer'],
        ]);
    }

    private function loginAs(int $id): void
    {
        Sanctum::actingAs(User::findOrFail($id), ['*']);
    }

    private function seedDocument(int $userId, bool $locked = false): int
    {
        return DB::table('applicant_private_documents')->insertGetId([
            'user_id' => $userId, 'type' => 'passport', 'title' => 'Passport',
            'status' => 'pending_review',
            'locked_at' => $locked ? now() : null,
            'locked_by' => $locked ? 3 : null,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_applicant_cannot_read_or_download_another_users_passport(): void
    {
        $id = $this->seedDocument(1);
        $this->loginAs(2);

        $this->getJson("/api/jobseeker/private-documents/{$id}/history")->assertNotFound();
        $this->getJson("/api/jobseeker/private-documents/{$id}/versions/1")->assertNotFound();
        $this->getJson('/api/jobseeker/private-documents')->assertOk()->assertJsonCount(0, 'documents');
    }

    public function test_employer_cannot_use_applicant_document_management(): void
    {
        $this->loginAs(3);
        $this->getJson('/api/jobseeker/private-documents')->assertForbidden();
        $this->postJson('/api/jobseeker/private-documents', [])->assertForbidden();
    }

    public function test_applicant_can_upload_one_passport_and_sees_only_safe_metadata(): void
    {
        $this->loginAs(1);
        $response = $this->post('/api/jobseeker/private-documents', [
            'type' => 'passport', 'title' => 'My passport',
            'file' => UploadedFile::fake()->create('passport.pdf', 5, 'application/pdf'),
        ], ['Accept' => 'application/json']);

        $response->assertCreated()->assertJsonPath('document.version', 1);
        $this->assertDatabaseCount('applicant_private_documents', 1);
        $this->assertDatabaseCount('applicant_private_document_versions', 1);
        $this->assertDatabaseCount('applicant_private_document_events', 1);

        $this->getJson('/api/jobseeker/private-documents')
            ->assertOk()->assertJsonCount(1, 'documents')
            ->assertDontSee('private-documents/');
    }

    public function test_applicant_cannot_replace_a_locked_document(): void
    {
        $id = $this->seedDocument(1, true);
        $this->loginAs(1);

        $this->post('/api/jobseeker/private-documents/' . $id . '/revisions', [
            'file' => UploadedFile::fake()->create('another.pdf', 5, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertUnprocessable();

        $this->assertDatabaseCount('applicant_private_document_versions', 0);
        $this->assertDatabaseCount('applicant_private_document_events', 0);
    }

    public function test_same_file_hash_is_rejected_even_when_renamed(): void
    {
        $id = $this->seedDocument(1);
        $file = UploadedFile::fake()->create('duplicate.pdf', 5, 'application/pdf');
        DB::table('applicant_private_document_versions')->insert([
            'document_id' => $id, 'user_id' => 1, 'version' => 1,
            'disk' => 'local', 'path' => 'private-documents/1/existing.pdf',
            'original_name' => 'original.pdf', 'mime_type' => 'application/pdf',
            'file_size' => 100, 'sha256' => hash_file('sha256', $file->getRealPath()),
            'created_at' => now(),
        ]);

        $this->loginAs(1);
        $this->post('/api/jobseeker/private-documents', [
            'type' => 'other', 'title' => 'A renamed duplicate', 'file' => $file,
        ], ['Accept' => 'application/json'])->assertUnprocessable();

        $this->assertDatabaseCount('applicant_private_document_versions', 1);
    }

    public function test_unprivileged_applicant_cannot_lock_or_approve_documents(): void
    {
        $id = $this->seedDocument(1);
        $this->loginAs(1);
        $this->postJson("/api/admin/private-documents/{$id}/review", [
            'action' => 'approve',
        ])->assertForbidden();

        $this->assertDatabaseHas('applicant_private_documents', [
            'id' => $id, 'status' => 'pending_review',
        ]);
        $this->assertDatabaseCount('applicant_private_document_events', 0);
    }
}
