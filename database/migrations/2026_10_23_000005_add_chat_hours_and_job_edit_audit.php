<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('site_chat_settings',function(Blueprint $table){$table->boolean('schedule_enabled')->default(false);$table->string('timezone',64)->default('UTC');$table->json('weekly_hours')->nullable();});
        Schema::create('job_edit_events',function(Blueprint $table){$table->engine='InnoDB';$table->id();$table->unsignedBigInteger('job_id')->index();$table->unsignedBigInteger('actor_id');$table->json('changed_fields');$table->timestamp('created_at');});
    }
    public function down(): void { /* Retain schedules and edit audit on code rollback. */ }
};
