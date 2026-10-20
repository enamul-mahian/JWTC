<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            JobCategorySeeder::class,
            AdminPermissionSeeder::class,
            EmployerPermissionSeeder::class,
            JobseekerPermissionSeeder::class,
//            UserProfileSeeder::class,
            EducationSeeder::class,
            LanguageSeeder::class,
            TrainingSeeder::class,
            WorkExperienceSeeder::class,
            IndustryTypesTableSeeder::class,
            // LocationSeeder::class,
            DegreeLavelSeeder::class,
            DegreeNameSeeder::class,
            SkillSeeder::class,
            JobTypeSeeder::class,
            CompanySize::class,
//            CompanySeeder::class,
//            EmployerInformationSeeder::class,
//            JobsTableSeeder ::class,
            SettingTableSeeder::class,
//            PersonalInformationSeeder::class,
//            ApplicationSeeder::class,

        ]);
    }
}
