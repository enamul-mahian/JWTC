<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void {
  Schema::table('jobs',function(Blueprint $t){$t->string('logo_path')->nullable();$t->json('faqs')->nullable();});
  Schema::create('job_location_options',function(Blueprint $t){$t->engine='InnoDB';$t->id();$t->unsignedBigInteger('country_id')->unique();$t->boolean('enabled')->default(true);$t->timestamps();});
  foreach(['United Kingdom'=>'GB','Scotland'=>'XS','Ireland'=>'IE','UK Territories'=>'XT'] as $name=>$code){
   $id=DB::table('countries')->where('name',$name)->value('id');
   if(!$id)$id=DB::table('countries')->insertGetId(['name'=>$name,'code'=>$code]);
   DB::table('job_location_options')->insert(['country_id'=>$id,'enabled'=>true,'created_at'=>now(),'updated_at'=>now()]);
  }
  Schema::create('site_security_settings',function(Blueprint $t){$t->engine='InnoDB';$t->unsignedInteger('id')->primary();$t->boolean('hcaptcha_enabled')->default(false);$t->string('hcaptcha_sitekey',100)->nullable();$t->text('hcaptcha_secret_encrypted')->nullable();$t->timestamps();});
  Schema::create('applicant_passport_profiles',function(Blueprint $t){$t->engine='InnoDB';$t->id();$t->unsignedBigInteger('user_id')->unique();$t->text('number_encrypted');$t->string('number_hash',64)->unique();$t->string('issuing_country',100);$t->string('holder_name',180);$t->date('issue_date')->nullable();$t->date('expiry_date');$t->timestamps();});
 }
 public function down(): void { /* Retain private records and historic job metadata. */ }
};
