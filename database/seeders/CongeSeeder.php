<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conge;
use App\Models\User;

class CongeSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        $conges = [
            ['Date_debut' => now(), 'Date_fin' => now()->addDays(5), 'Type' => 'Vacation', 'ID_User' => $users->first()->ID_User],
            // Add more conges as needed
        ];

        foreach ($conges as $congeData) {
            Conge::create($congeData);
        }
    }
}