<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySize extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'title' => "1 - 10",
            ],
            [
                'title' => "10 - 30",
            ],
            [
                'title' => "30 - 50",
            ],
            [
                'title' => "50 - 100",
            ],
            [
                'title' => "100 - 500",
            ]
        ];

        DB::table('company_sizes')->insert($data);
    }
}
