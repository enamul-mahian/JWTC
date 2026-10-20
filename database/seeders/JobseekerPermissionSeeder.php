<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enum\RoleType;
use App\Enum\UserType;
use App\Enum\StatusType;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\PersonalInformation;
use App\Models\Dashboard\JobCategory;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JobseekerPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $permissions = [
            'job-apply',
            'my-library',
            'employer-cv-view',
            'job-shortlist',
            'job-applied',
            'manage-profile',
            'view-profile',
            'job-seeker-dashboard',

        ];

        // Create the 'Job Seeker' role
        $role = Role::create(['name' => RoleType::JOB_SEEKER]);

        // Create individual permissions and assign them to the 'Job Seeker' role
        foreach ($permissions as $permissionName) {
            $permission = Permission::create(['name' => $permissionName]);
            $role->givePermissionTo($permission);
        }
        $jobCategoryId = JobCategory::first();
        // Create a job seeker user
        $user = User::create([
            'name' => 'Meshkat Hasan',
            'email' => 'jobseeker@gmail.com',
            'phone' => '01714254785',
            'user_type' => UserType::JOB_SEEKER,
            'password' => bcrypt('123456'), // Use bcrypt to hash the password
            'status' => StatusType::ACTIVE,
             'job_category_id' => $jobCategoryId->id,
        ]);

        PersonalInformation::create([
            'user_id' => $user->id,
            'first_name' => $user->name,
            'last_name' => 'Riad',
        ]);

        // Assign the 'Job Seeker' role to the job seeker user
        $user->assignRole($role);
    }
}