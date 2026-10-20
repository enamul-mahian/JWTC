<?php

namespace Database\Seeders;

use App\Enum\StatusType;
use App\Models\Dashboard\DegreeLavel;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DegreeLavelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $degrees = [
            ['name' => 'PSC/5 Pass', 'status' => StatusType::ACTIVE],
            ['name' => 'JSC/JDC/8 Pass', 'status' => StatusType::ACTIVE],
            ['name' => 'Secondary', 'status' => StatusType::ACTIVE],
            ['name' => 'Heigher Secondary', 'status' => StatusType::ACTIVE],
            ['name' => 'Diploma', 'status' => StatusType::ACTIVE],
            ['name' => 'Bachelor/Honors', 'status' => StatusType::ACTIVE],
            ['name' => 'Masters', 'status' => StatusType::ACTIVE],
        ];

        foreach ($degrees as $degree) {
            DegreeLavel::create($degree);
        }
    }
}
