<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Additive: do not alter older Email Studio templates or attachments.
        Schema::create('email_studio_visual_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id')->index();
            $table->unsignedTinyInteger('page_number');
            $table->string('private_path', 512);
            $table->unsignedInteger('file_size');
            $table->char('sha256', 64);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['template_id', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_studio_visual_pages');
    }
};
