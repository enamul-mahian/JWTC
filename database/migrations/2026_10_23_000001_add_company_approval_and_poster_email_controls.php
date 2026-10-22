<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('employer_approved_at')->nullable();
            $table->unsignedBigInteger('employer_approved_by')->nullable();
        });
        Schema::table('application_settings', function (Blueprint $table) {
            $table->boolean('show_poster_email')->default(false);
        });
        Schema::create('employer_review_events', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('employer_id')->index();
            $table->unsignedBigInteger('actor_id'); $table->string('decision', 16);
            $table->text('reason')->nullable(); $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void
    {
        // Preserve approval history and administrator decisions on code rollback.
    }
};
