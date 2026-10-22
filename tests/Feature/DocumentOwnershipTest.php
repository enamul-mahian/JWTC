<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DocumentOwnershipTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        foreach (['uploaded_cvs', 'jobseeker_certificates', 'jobseeker_uploads'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('file');
                $table->string('title')->nullable();
                $table->timestamps();
            });
        }

        foreach ([1, 2] as $id) {
            DB::table('users')->insert([
                'id' => $id,
                'name' => 'Applicant ' . $id,
                'email' => 'applicant-' . $id . '@example.invalid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs(User::findOrFail(1));
    }

    public function test_applicants_cannot_delete_another_users_documents(): void
    {
        $types = [
            'uploaded_cvs' => 'uploaded-cv-delete',
            'jobseeker_certificates' => 'delete-jobseeer-certificate',
            'jobseeker_uploads' => 'delete-upload-document',
        ];

        foreach ($types as $tableName => $routeName) {
            $id = DB::table($tableName)->insertGetId([
                'user_id' => 2,
                'file' => 'test.pdf',
                'title' => 'Private Test Document',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->delete(route($routeName, $id))
                ->assertRedirect(route('job-seeker-profile'));

            $this->assertDatabaseHas($tableName, ['id' => $id, 'user_id' => 2]);
        }
    }

    public function test_applicants_can_delete_their_own_document_records(): void
    {
        $id = DB::table('uploaded_cvs')->insertGetId([
            'user_id' => 1,
            'file' => 'own.pdf',
            'title' => 'My Document',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->delete(route('uploaded-cv-delete', $id))
            ->assertRedirect(route('job-seeker-profile'));

        $this->assertDatabaseMissing('uploaded_cvs', ['id' => $id]);
    }
}
