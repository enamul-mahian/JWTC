<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('job_category_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreignId('country_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();

            // $table->unsignedBigInteger('country')->nullable();
            $table->unsignedBigInteger('degree_level_id')->nullable();
            $table->unsignedBigInteger('degree_name_id')->nullable();

            $table->string('title');
            $table->string('slug');
            $table->string('job_status')->comment('Pending Approved Rejected');
            $table->string('salary_type');
            $table->string('resume_receiving_email');
            $table->integer('minimum_salary');
            $table->integer('maximum_salary');
            $table->longText('job_responsibilities');
            $table->string('vacancies', 10)->nullable();
            $table->date('application_deadline')->nullable();
            $table->string('salary_review')->nullable();
            $table->integer('festival_bonus')->nullable();
            $table->string('lunch_facilities')->nullable();
            $table->longText('job_context')->nullable();
            $table->longText('additional_salary_info')->nullable();
            $table->longText('trade_course')->nullable();
            $table->longText('professional_certification')->nullable();

            // Experience
            $table->integer('mini_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->integer('minimum_year')->nullable()->comment('mini_experience');
            $table->integer('maximum_year')->nullable()->comment('max_experience');
            $table->string('fresher')->nullable();
            $table->longText('additional_requirements')->nullable();

            $table->json('skills')->nullable();
            $table->json('job_level')->nullable();
            $table->json('work_place')->nullable();
            $table->json('other_benefits')->nullable();
            $table->json('employment_status')->nullable()->comment('job types');
            $table->json('gender')->nullable();
            $table->timestamp('approval_date')->nullable();
            $table->integer('is_publish')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};