<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('filename');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('ready_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->string('status')->default('staged');
            $table->timestamps();
        });

        Schema::create('job_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('job_import_batches')->cascadeOnDelete();
            $table->string('source_key', 64);
            $table->string('status')->default('pending');
            $table->string('reason')->nullable();
            $table->json('payload');
            $table->foreignId('published_job_id')->nullable()->constrained('jobs')->nullOnDelete();
            $table->timestamps();
            $table->unique(['batch_id', 'source_key']);
            $table->index(['source_key', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_import_rows');
        Schema::dropIfExists('job_import_batches');
    }
};
