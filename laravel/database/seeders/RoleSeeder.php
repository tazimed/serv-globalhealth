<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['Role' => 'Admin'],
            ['Role' => 'User'],
            // Add more roles as needed
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}