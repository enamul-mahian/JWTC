<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_private_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('type', 24); // passport, certificate, other
            $table->string('title', 180);
            $table->string('status', 24)->default('pending_review');
            $table->timestamp('locked_at')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->text('lock_reason')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'type']);
        });

        Schema::create('applicant_private_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')
                ->constrained('applicant_private_documents');
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedInteger('version');
            $table->string('disk', 24)->default('local');
            $table->string('path', 512);
            $table->string('original_name', 180);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('file_size');
            $table->char('sha256', 64);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['document_id', 'version']);
            // Prevent applicant from uploading identical bytes under another name.
            $table->unique(['user_id', 'sha256'], 'private_doc_user_hash_unique');
        });

        Schema::create('applicant_private_document_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')
                ->constrained('applicant_private_documents');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 32);
            $table->string('old_status', 24)->nullable();
            $table->string('new_status', 24)->nullable();
            $table->unsignedInteger('version')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['document_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_private_document_events');
        Schema::dropIfExists('applicant_private_document_versions');
        Schema::dropIfExists('applicant_private_documents');
    }
};
