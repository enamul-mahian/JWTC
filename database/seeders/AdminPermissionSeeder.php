<?php

namespace Database\Seeders;

use App\Enum\RoleType;
use App\Models\User;
use App\Enum\StatusType;
use App\Models\Dashboard\JobCategory;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $permissions = [
            'role.index',
            'role.create',
            'role.edit',
            'role.delete',

            'user.index',
            'user.create',
            'user.edit',
            'user.delete',

            'job-categories.index',
            'job-categories.create',
            'job-categories.edit',
            'job-categories.delete',

            'company-size.index',
            'company-size.create',
            'company-size.edit',
            'company-size.delete',

            'industry-type.index',
            'industry-type.create',
            'industry-type.edit',
            'industry-type.delete',

            'plan.index',
            'plan.create',
            'plan.edit',
            'plan.delete',

            'job-type.index',
            'job-type.create',
            'job-type.edit',
            'job-type.delete',

            'location.index',
            'location.create',
            'location.edit',
            'location.delete',

            'degree-level.index',
            'degree-level.create',
            'degree-level.edit',
            'degree-level.delete',

            'skills.index',
            'skills.create',
            'skills.edit',
            'skills.delete',

            'degree-name.index',
            'degree-name.create',
            'degree-name.edit',
            'degree-name.delete',

            'page.index',
            'page.create',
            'page.edit',
            'page.delete',

            'active-job',
            'inactive-job',
            'employer-list',

            'employer-job-history',
            'employer-cv-downloads',

            'application-setting',
            'email-setting',

            'cache-clear',

            'active-jobSeeker',
            'inactive-jobSeeker',
            'jobSeeker-history',

            'sendMassMail',
            'contact-message',
            'admin-dashboard',
            'ads.index',

            'payment-gateway-credential.create',
        ];

        $super_admin = Role::create(['name' => RoleType::SUPER_ADMIN]);
        $admin = Role::create(['name' => RoleType::ADMIN]);

        foreach ($permissions as $permissionName) {
            $permission = Permission::create(['name' => $permissionName]);
            $super_admin->givePermissionTo($permission);
            $admin->givePermissionTo($permission);
        }
        $jobCategoryId = JobCategory::first();

        $admin_user = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'phone' => '01714254785',
            'user_type' => RoleType::ADMIN,
            'password' => bcrypt('123456'), // Use bcrypt to hash the password
            'status' => StatusType::ACTIVE,
            'job_category_id' => $jobCategoryId->id,
        ]);

        $super_user = User::create([
            'name' => 'Super Admin',
            'email' => 'super@gmail.com',
            'phone' => '01714254785',
            'user_type' => RoleType::ADMIN,
            'password' => bcrypt('123456'), // Use bcrypt to hash the password
            'status' => StatusType::ACTIVE,
            'job_category_id' => $jobCategoryId->id,
        ]);

        $admin_user->assignRole($admin);
        $super_user->assignRole($super_admin);
    }
}
