<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paiement;
use App\Models\User;

class PaiementSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        $paiements = [
            ['Date' => now(), 'Type' => 'Salary', 'Absence_sup' => 0, 'ID_User' => $users->first()->ID_User],
            // Add more paiements as needed
        ];

        foreach ($paiements as $paiementData) {
            Paiement::create($paiementData);
        }
    }
}