<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ThemeGeneral;

class ThemeGeneralFactory extends Factory
{
    protected $model = ThemeGeneral::class;

    public function definition()
    {
        return [
            'Couleurs' => $this->faker->colorName,
            'Horaire_Travail' => $this->faker->numberBetween(1, 24),
        ];
    }
}