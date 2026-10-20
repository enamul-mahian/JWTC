<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\User;
use App\Models\Skill;
use App\Models\Location;
use App\Models\Dashboard\Company;
use App\Models\Dashboard\DegreeName;
use App\Models\Dashboard\DegreeLavel;
use App\Models\Dashboard\JobCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $skillIds = Skill::factory()->count(5)->create()->pluck('id')->toArray();

        return [
            'user_id' => User::where('user_type', 'employer')->get()->random()->id,
            'job_category_id' => JobCategory::all()->random()->id,
            'degree_level_id' => DegreeLavel::get()->random()->id,
            'degree_name_id' => DegreeName::get()->random()->id,
            'minimum_salary' => 2000,
            'maximum_salary' => 20000,
            'mini_age' => $this->faker->numberBetween(19, 25),
            'max_age' => $this->faker->numberBetween(30, 50),
            'company_id' => Company::all()->random()->id,
            'minimum_year' => 1,
            'maximum_year' => $this->faker->numberBetween(2, 5),
            'title' => $this->faker->jobTitle,
            'slug' => $this->faker->slug,
            'job_status' => $this->faker->word,
            'professional_certification' =>  $this->faker->word,
            'trade_course' =>  $this->faker->word,
            'vacancies' => $this->faker->randomDigitNotZero,
            'application_deadline' => $this->faker->date,
            'resume_receiving_email' => $this->faker->safeEmail,
            'job_context' => $this->faker->sentence,
            'job_responsibilities' => $this->faker->paragraph,
            'additional_requirements' => $this->faker->paragraph,
            'additional_salary_info' => $this->faker->paragraph,
            'lunch_facilities' => $this->faker->randomElement(['partially_subsidize', 'full_subsidize']),
            'salary_review' => $this->faker->randomElement(['half_yearly', 'yearly']),
            'festival_bonus' => $this->faker->numberBetween(1, 4),

            'country_id' => Country::all()->random()->id,
            'gender' => json_encode(['male', 'female']),
            'employment_status' => json_encode(['part_time', 'full_time', 'contractual', 'remote']), //Job Type
            'job_level' => json_encode(['entry', 'mid']),
            'other_benefits' => json_encode(['mobile_bill', 'pension_policy', 'tour', 'medical']),
            'work_place' => json_encode(['work_at_office', 'work_from_home']),
            'approval_date' => $this->faker->date,
            'skills' => json_encode($skillIds),
            'salary_type' => 'monthly',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
