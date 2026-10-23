<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_hero_settings', function(Blueprint $table) {
            $table->engine='InnoDB'; $table->unsignedTinyInteger('id')->primary();
            $table->boolean('enabled')->default(true); $table->unsignedSmallInteger('interval')->default(8);
            $table->json('slides'); $table->unsignedBigInteger('updated_by')->nullable(); $table->timestamps();
        });
        DB::table('site_hero_settings')->insert(['id'=>1,'enabled'=>true,'interval'=>8,'slides'=>json_encode([
            ['id'=>'london-evening','path'=>'theme/hero-london.webp','position'=>'center 55%'],
            ['id'=>'london-detail','path'=>'theme/hero-tower-bridge.webp','position'=>'center'],
        ],JSON_THROW_ON_ERROR),'created_at'=>now(),'updated_at'=>now()]);
    }
    public function down(): void { /* Preserve administrator images and settings on code rollback. */ }
};
