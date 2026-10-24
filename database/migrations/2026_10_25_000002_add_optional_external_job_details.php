<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('source_url', 2048)->nullable();
            $table->string('external_apply_url', 2048)->nullable();
            $table->string('location_detail', 255)->nullable();
        });
    }
    public function down(): void {
        // Preserve historical job records and optional fields on rollback.
    }
};
