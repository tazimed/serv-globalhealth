<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['Categories' => 'Category 1'],
            ['Categories' => 'Category 2'],
            // Add more categories as needed
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }
    }
}