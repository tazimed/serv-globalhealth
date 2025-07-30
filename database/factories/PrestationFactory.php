<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Prestation;

class PrestationFactory extends Factory
{
    protected $model = Prestation::class;

    public function definition()
    {
        return [
            'Prestations' => $this->faker->word,
            'Durees' => $this->faker->numberBetween(1, 24),
            'Prix' => $this->faker->randomFloat(2, 10, 100),
            'ID_Categories' => \App\Models\Category::factory(),
        ];
    }
}