<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;


class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $admin       = Role::create(['ROLE_NAME' => 'Admin']);
        $instructor  = Role::create(['ROLE_NAME' => 'Instructor']);
        $participant = Role::create(['ROLE_NAME' => 'Participant']);

        // Create permissions
        $createCourse = Permission::create(['PERMISSION_NAME' => 'create-course']);
        $editCourse   = Permission::create(['PERMISSION_NAME' => 'edit-course']);
        $deleteCourse = Permission::create(['PERMISSION_NAME' => 'delete-course']);
        $viewCourse   = Permission::create(['PERMISSION_NAME' => 'view-course']);

        // Attach permissions to roles
        $admin->permissions()->attach([
            $createCourse->PERMISSION_ID,
            $editCourse->PERMISSION_ID,
            $deleteCourse->PERMISSION_ID,
            $viewCourse->PERMISSION_ID,
        ]);

        $instructor->permissions()->attach([
            $createCourse->PERMISSION_ID,
            $editCourse->PERMISSION_ID,
            $viewCourse->PERMISSION_ID,
        ]);

        $participant->permissions()->attach([
            $viewCourse->PERMISSION_ID,
        ]);
    }
}
