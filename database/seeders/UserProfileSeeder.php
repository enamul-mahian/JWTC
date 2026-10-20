<?php

namespace Database\Seeders;

use App\Models\PersonalInformation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        PersonalInformation::factory()->count(1)->create();
    }
}