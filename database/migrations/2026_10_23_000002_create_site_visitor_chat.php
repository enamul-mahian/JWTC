<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_chat_settings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedTinyInteger('id')->primary();
            $table->boolean('enabled')->default(true);
            $table->timestamp('online_until')->nullable();
            $table->timestamps();
        });
        DB::table('site_chat_settings')->insert(['id'=>1, 'enabled'=>true, 'created_at'=>now(), 'updated_at'=>now()]);
        Schema::create('site_chat_threads', function (Blueprint $table) {
            $table->engine = 'InnoDB'; $table->id();
            $table->char('public_id', 36)->unique();
            $table->text('name_encrypted'); $table->text('email_encrypted');
            $table->string('status', 16)->default('open');
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
        });
        Schema::create('site_chat_messages', function (Blueprint $table) {
            $table->engine = 'InnoDB'; $table->id();
            $table->unsignedBigInteger('thread_id')->index();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('body_encrypted'); $table->timestamp('created_at');
        });
    }
    public function down(): void { /* Retain conversations during code rollback. */ }
};
