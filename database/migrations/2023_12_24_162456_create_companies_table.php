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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_sizes_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('industry_type_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('company_name')->nullable();
            $table->string('estd_year', 20)->nullable();
            $table->tinyInteger('status')->default(1)->comment("1->Active 0->Inactive");
            // $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('company_address')->nullable();
            $table->longText('business_description')->nullable();
            $table->string('trade_license')->nullable();
            $table->string('website_url')->nullable();
            $table->string('contact_per_name')->nullable();
            $table->string('persone_designation')->nullable();
            $table->string('contact_per_email')->nullable();
            $table->string('contact_per_phone')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
