<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_notice_settings', function(Blueprint $table) {
            $table->engine='InnoDB'; $table->unsignedTinyInteger('id')->primary();
            $table->boolean('enabled')->default(true); $table->unsignedSmallInteger('speed')->default(60);
            $table->json('items'); $table->unsignedBigInteger('updated_by')->nullable(); $table->timestamps();
        });
        DB::table('site_notice_settings')->insert(['id'=>1,'enabled'=>true,'speed'=>60,'items'=>json_encode([
            ['title'=>'Browse published job vacancies before registering.','url'=>'/search-jobs'],
            ['title'=>'Company accounts require administrator approval.','url'=>'/company/register'],
            ['title'=>'Track submitted applications from your job seeker account.','url'=>'/jobseeker/login'],
        ],JSON_THROW_ON_ERROR),'created_at'=>now(),'updated_at'=>now()]);
    }
    public function down(): void { /* Preserve administrator announcements on code rollback. */ }
};
