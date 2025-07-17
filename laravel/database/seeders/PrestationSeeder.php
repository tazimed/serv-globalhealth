<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prestation;
use App\Models\Category;

class PrestationSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::all();

        $prestations = [
            ['Prestations' => 'Service 1', 'Durees' => 60, 'Prix' => 100, 'ID_Categories' => $categories->first()->ID_Categories],
            // Add more prestations as needed
        ];

        foreach ($prestations as $prestationData) {
            Prestation::create($prestationData);
        }
    }
}