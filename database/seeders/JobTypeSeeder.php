<?php

namespace Database\Seeders;

use App\Enum\StatusType;
use App\Models\JobType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobTypes = [
            ['name' => 'Full Time', 'status' => StatusType::ACTIVE],
            ['name' => 'Part Time', 'status' => StatusType::ACTIVE],
            ['name' => 'Remote', 'status' => StatusType::ACTIVE],
            ['name' => 'Contractual', 'status' => StatusType::ACTIVE],
        ];

        foreach ($jobTypes as $jobType){
            JobType::create($jobType);
        }
    }
}
