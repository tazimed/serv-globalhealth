<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\JourFerie;

class JourFerieFactory extends Factory
{
    protected $model = JourFerie::class;

    public function definition()
    {
        return [
            'Date_debut' => $this->faker->date,
            'Date_fin' => $this->faker->date,
        ];
    }
}