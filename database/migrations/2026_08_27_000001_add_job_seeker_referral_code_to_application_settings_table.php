<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJobSeekerReferralCodeToApplicationSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('application_settings', function (Blueprint $table) {
            $table->string('job_seeker_referral_code')->nullable();
        });
    }

    public function down()
    {
        Schema::table('application_settings', function (Blueprint $table) {
            $table->dropColumn('job_seeker_referral_code');
        });
    }
}