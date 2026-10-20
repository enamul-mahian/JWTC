<?php

namespace Database\Seeders;

use App\Enum\RoleType;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => RoleType::SUPER_ADMIN],
            ['name' => RoleType::ADMIN],
            ['name' => RoleType::EMPLOYER],
            ['name' => RoleType::JOB_SEEKER],
        ];

        foreach($roles as $role){
            Role::create($role);
        }

        // $admin_permissions = [
        //     ['name' =>  'role.index'],
        //     ['name' => 'role.create'],
        //     ['name' => 'role.edit'],
        //     ['name' => 'role.delete'],
        //     ['name' => 'job-categories index'],
        //     ['name' => 'job-categories create'],
        //     ['name' => 'job-categories edit'],
        //     ['name' => 'job-categories delete'],
        // ];
        
        // $jobseeker_permissions = [
        //     ['name' => 'job-apply'],
        //     ['name' => 'my-library'],
        //     ['name' => 'employer-cv-view'],
        //     ['name' => 'job-shortlist'],
        //     ['name' => 'job-applied'],
        //     ['name' => 'manage-profile'],
        //     ['name' => 'view-profile'],
        // ];

        // foreach ($jobseeker_permissions as $permission) {
        //     Permission::create($permission);
        // }
        
        // foreach ($admin_permissions as $permission) {
        //     Permission::create($permission);
        // }
    }
}