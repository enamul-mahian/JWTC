<?php

namespace Database\Seeders;

use App\Models\EmployerInformation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployerInformationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EmployerInformation::factory()->count(1)->create();
    }
}
