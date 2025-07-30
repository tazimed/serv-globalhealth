<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JourFerie;

class JourFerieSeeder extends Seeder
{
    public function run()
    {
        $joursFeries = [
            ['Date_debut' => now(), 'Date_fin' => now()->addDays(1)],
            // Add more jours ferie as needed
        ];

        foreach ($joursFeries as $jourFerieData) {
            JourFerie::create($jourFerieData);
        }
    }
}