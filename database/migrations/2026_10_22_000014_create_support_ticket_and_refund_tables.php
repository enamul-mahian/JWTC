<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 32)->unique();
            $table->unsignedBigInteger('requester_id')->index();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->string('category', 32);
            $table->string('priority', 16)->default('normal');
            $table->string('subject', 180);
            $table->longText('description_encrypted');
            $table->string('status', 24)->default('open');
            $table->unsignedBigInteger('last_reply_id')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'updated_at']);
        });

        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->index();
            $table->unsignedBigInteger('author_id');
            $table->longText('body_encrypted');
            $table->boolean('internal_only')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['ticket_id', 'id']);
        });

        Schema::create('support_ticket_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->index();
            $table->unsignedBigInteger('actor_id');
            $table->unsignedBigInteger('related_user_id')->nullable();
            $table->string('action', 36);
            $table->string('old_status', 24)->nullable();
            $table->string('new_status', 24)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('support_refund_requests', function (Blueprint $table) {
            $table->id();
            // Exactly one financial dispute per support ticket.
            $table->unsignedBigInteger('ticket_id')->unique();
            $table->unsignedBigInteger('requester_id')->index();
            $table->char('currency', 3);
            $table->unsignedBigInteger('amount_minor');
            $table->text('payment_reference_encrypted');
            $table->longText('reason_encrypted');
            $table->string('status', 24)->default('requested');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('settled_by')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->text('settlement_reference_encrypted')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('support_refund_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('refund_id')->index();
            $table->unsignedBigInteger('actor_id');
            $table->string('action', 28);
            $table->string('from_status', 24)->nullable();
            $table->string('to_status', 24);
            $table->longText('note_encrypted')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_refund_events');
        Schema::dropIfExists('support_refund_requests');
        Schema::dropIfExists('support_ticket_events');
        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
    }
};
