<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('employer_id')->nullable();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->integer('status')->nullable()->comment("1->applied 2->shortListed 3->interview");
            $table->string('date_applied', 20);
            $table->decimal('expected_salary', 8, 2);

            $table->index('user_id');
            $table->index('employer_id');
            $table->index('job_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('employer_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};