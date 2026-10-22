<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_studio_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('subject', 250);
            $table->longText('html_body');
            $table->text('footer')->nullable();
            $table->json('images')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
        });

        Schema::create('email_studio_send_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id')->nullable()->index();
            $table->unsignedBigInteger('actor_id');
            $table->string('recipient', 254);
            $table->string('subject', 250);
            $table->string('result', 16);
            $table->boolean('pdf_attached')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['actor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_studio_send_logs');
        Schema::dropIfExists('email_studio_templates');
    }
};
