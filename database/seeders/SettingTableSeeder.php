<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApplicationSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $setting = new ApplicationSetting();
        $setting->business_name    = 'IT Business';
        $setting->business_address = 'your business address';
        $setting->business_email   = 'business@gmail.com';
        $setting->business_number  = '00000000000';
        $setting->currency_symbol  = '$';
        $setting->save();
    }
}