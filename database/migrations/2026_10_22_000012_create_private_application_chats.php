<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_chat_threads', function (Blueprint $table) {
            $table->id();
            // One conversation per existing application, no application rewrite.
            $table->unsignedBigInteger('application_id')->unique();
            $table->unsignedBigInteger('opened_by');
            $table->string('status', 16)->default('open');
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'last_message_at']);
        });

        Schema::create('application_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('thread_id')->index();
            $table->unsignedBigInteger('sender_id')->index();
            // Body is Laravel APP_KEY encrypted; never store plaintext.
            $table->longText('body_encrypted');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['thread_id', 'id']);
        });

        Schema::create('application_chat_read_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('thread_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('last_read_message_id')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['thread_id', 'user_id']);
        });

        Schema::create('application_chat_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('thread_id')->index();
            $table->unsignedBigInteger('actor_id');
            $table->string('action', 16);
            $table->string('before_status', 16)->nullable();
            $table->string('after_status', 16);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_chat_events');
        Schema::dropIfExists('application_chat_read_states');
        Schema::dropIfExists('application_chat_messages');
        Schema::dropIfExists('application_chat_threads');
    }
};
