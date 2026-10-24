<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('external_job_imports', function (Blueprint $table) {
            $table->id();
            $table->string('source', 60)->default('uk_government_jobs');
            $table->string('source_url', 2048);
            $table->char('source_hash', 64)->unique();
            $table->string('title');
            $table->string('company_name');
            $table->string('location')->nullable();
            $table->string('salary')->nullable();
            $table->date('application_deadline')->nullable();
            $table->date('posted_on')->nullable();
            $table->string('workplace')->default('onsite');
            $table->string('employment_type')->default('permanent');
            $table->string('schedule')->default('full_time');
            $table->text('summary')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('imported_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('external_job_imports'); }
};
