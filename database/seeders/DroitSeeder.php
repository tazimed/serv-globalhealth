<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Droit;
use App\Models\Role;

class DroitSeeder extends Seeder
{
    public function run()
    {
        $roles = Role::all();

        $droits = [
            ['Droit' => 'View', 'Lecture' => true, 'Ajouter' => false, 'Modifier' => false, 'Supprimer' => false],
            ['Droit' => 'Edit', 'Lecture' => true, 'Ajouter' => true, 'Modifier' => true, 'Supprimer' => false],
            // Add more droits as needed
        ];

        foreach ($droits as $droitData) {
            foreach ($roles as $role) {
                $droitData['ID_Role'] = $role->ID_Role;
                Droit::create($droitData);
            }
        }
    }
}