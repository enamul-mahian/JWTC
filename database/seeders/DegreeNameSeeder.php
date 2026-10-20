<?php

namespace Database\Seeders;

use App\Enum\StatusType;
use App\Models\Dashboard\DegreeName;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DegreeNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $degreeName = [
            ['degree_lavel_id' => '1', 'name' => 'PSC', 'status'=> StatusType::ACTIVE],
            ['degree_lavel_id' => '1', 'name' => '5 pass', 'status'=> StatusType::ACTIVE],
            ['degree_lavel_id' => '2', 'name' => 'JSC', 'status'=> StatusType::ACTIVE],
            ['degree_lavel_id' => '2', 'name' => 'JDC', 'status'=> StatusType::ACTIVE],
            ['degree_lavel_id' => '2', 'name' => '8 Pass', 'status'=> StatusType::ACTIVE],
            ['degree_lavel_id' => '3', 'name' => 'SSC', 'status'=> StatusType::ACTIVE],
            ['degree_lavel_id' => '3', 'name' => 'Vocational', 'status'=> StatusType::ACTIVE],
            ['degree_lavel_id' => '3', 'name' => 'Madrasa', 'status'=> StatusType::ACTIVE],
            ['degree_lavel_id' => '4', 'name' => 'HSC', 'status'=> StatusType::ACTIVE],
            ['degree_lavel_id' => '4', 'name' => 'Vocational', 'status'=> StatusType::ACTIVE],
            ['degree_lavel_id' => '4', 'name' => 'Madrasa', 'status'=> StatusType::ACTIVE],
        ];

        foreach ($degreeName as $degree) {
            DegreeName::create($degree);
        }
    }
}
