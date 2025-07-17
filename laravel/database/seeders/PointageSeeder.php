<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pointage;

class PointageSeeder extends Seeder
{
    public function run()
    {
        $pointages = [
            ['Date' => now()],
            // Add more pointages as needed
        ];

        foreach ($pointages as $pointageData) {
            Pointage::create($pointageData);
        }
    }
}