<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('support_tickets',function(Blueprint $t){$t->unsignedBigInteger('recipient_company_id')->nullable()->index();});
  Schema::create('support_mail_deliveries',function(Blueprint $t){$t->engine='InnoDB';$t->id();$t->unsignedBigInteger('ticket_id')->index();$t->string('event_key',100)->unique();$t->text('recipient_encrypted');$t->text('message_encrypted');$t->string('status',30)->default('pending');$t->unsignedInteger('attempts')->default(0);$t->timestamp('accepted_at')->nullable();$t->timestamps();});
 }
 public function down(): void { /* Preserve ticket recipients and delivery history. */ }
};
