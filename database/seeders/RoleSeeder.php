<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Delete all records from the roles table
        DB::table('roles')->delete();

        // Reset auto-increment
        DB::statement('ALTER TABLE roles AUTO_INCREMENT = 1');

        // Insert the roles with specific IDs matching the image
        $roles = [
            ['ID_Role' => 1, 'Role' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['ID_Role' => 2, 'Role' => 'utilisateur', 'created_at' => now(), 'updated_at' => now()],
            ['ID_Role' => 3, 'Role' => 'blanditiis', 'created_at' => now(), 'updated_at' => now()],
        ];

        // Use insertOrIgnore to prevent errors if the role already exists
        DB::table('roles')->insertOrIgnore($roles);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
