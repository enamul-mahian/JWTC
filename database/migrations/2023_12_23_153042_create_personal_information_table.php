<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_information', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('motto')->nullable();
            $table->longText('about_me')->nullable();
            $table->string('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('email')->nullable();
            $table->string('alternate_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->string('instant_messaging')->nullable();
            $table->string('website')->nullable();
            $table->string('present_address_type')->nullable();
            $table->string('present_address_line_1')->nullable();
            $table->string('present_address')->nullable();
            $table->string('present_postal_code')->nullable();
            $table->string('present_city')->nullable();
            $table->string('present_country')->nullable();
            $table->string('permanent_address_type')->nullable();
            $table->string('permanent_address_line_1')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('permanent_postal_code')->nullable();
            $table->string('permanent_city')->nullable();
            $table->string('permanent_country')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_information');
    }
};
