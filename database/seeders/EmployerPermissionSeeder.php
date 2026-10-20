<?php

namespace Database\Seeders;

use App\Enum\RoleType;
use App\Enum\UserType;
use App\Models\Dashboard\JobCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EmployerPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $permissions = [
            'employer-dashboard',
            'post-job',
            'archive-job', // corrected spelling
            'job-manage',
            'company-information',
            'cv-bank',
            'update-profile',
            'analytics',
            'job-edit',
            'job-delete',
        ];

        $role = Role::create(['name' => RoleType::EMPLOYER]);

        foreach ($permissions as $permissionName) {
            $permission = Permission::create(['name' => $permissionName]);
            $role->givePermissionTo($permission);
        }
        $jobCategoryId = JobCategory::first();
        $user = User::create([
            'name' => 'Md Shamim',
            'email' => 'shamim@gmail.com',
            'phone' => '01714254785',
            'user_type' => UserType::EMPLOYER,
            'password' => bcrypt('123456'), // Use bcrypt to hash the password
            'job_category_id' => $jobCategoryId->id,
        ]);

        $user->assignRole($role);
    }
}
