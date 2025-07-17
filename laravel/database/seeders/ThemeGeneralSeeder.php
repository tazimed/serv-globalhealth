<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ThemeGeneral;

class ThemeGeneralSeeder extends Seeder
{
    public function run()
    {
        $themes = [
            ['Couleurs' => '#ffffff', 'Horaire_Travail' => 8],
            // Add more themes as needed
        ];

        foreach ($themes as $themeData) {
            ThemeGeneral::create($themeData);
        }
    }
}