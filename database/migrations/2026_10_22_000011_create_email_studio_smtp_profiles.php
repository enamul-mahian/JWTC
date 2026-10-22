<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_studio_smtp_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('host', 253);
            $table->unsignedSmallInteger('port');
            $table->string('encryption', 12);
            $table->string('username', 254);
            $table->text('password_encrypted');
            $table->string('from_address', 254);
            $table->string('from_name', 180);
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_studio_smtp_profiles');
    }
};
