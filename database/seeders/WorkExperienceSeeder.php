<?php

namespace Database\Seeders;

use App\Models\WorkExperience;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WorkExperienceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WorkExperience::factory()->count(3)->create();
    }
}