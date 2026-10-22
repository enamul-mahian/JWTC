<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_interviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id')->unique();
            $table->unsignedBigInteger('organizer_id');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('starts_at_utc');
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->string('timezone', 64);
            $table->string('meeting_url', 2000)->nullable();
            $table->string('status', 32)->default('scheduled');
            $table->text('instructions')->nullable();
            $table->timestamps();
            $table->index(['status', 'starts_at_utc']);
        });

        Schema::create('application_interview_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('interview_id')->index();
            $table->unsignedBigInteger('application_id')->index();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('action', 32);
            $table->string('before_status', 32)->nullable();
            $table->string('after_status', 32);
            $table->dateTime('before_starts_at_utc')->nullable();
            $table->dateTime('after_starts_at_utc')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_interview_events');
        Schema::dropIfExists('application_interviews');
    }
};
