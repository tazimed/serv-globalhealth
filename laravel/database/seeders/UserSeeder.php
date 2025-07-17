<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        $roles = Role::all();

        $users = [
            [
                'Nom' => 'Doe',
                'Prenom' => 'John',
                'Email' => 'john.doe@example.com',
                'Password' => Hash::make('password'),
                'Sex' => 'Male',
                'ID_Role' => $roles->first()->ID_Role,
                // Add other fields as needed
            ],
            // Add more users as needed
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}