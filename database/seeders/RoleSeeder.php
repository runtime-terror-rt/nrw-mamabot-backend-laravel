<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create or update roles with Sanctum guard
        Role::updateOrCreate(
            ['name' => 'Admin'],
            ['guard_name' => 'sanctum']
        );

        Role::updateOrCreate(
            ['name' => 'User'],
            ['guard_name' => 'sanctum']
        );

    }
}
