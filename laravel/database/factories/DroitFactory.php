<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Droit;

class DroitFactory extends Factory
{
    protected $model = Droit::class;

    public function definition()
    {
        return [
            'Droit' => $this->faker->word,
            'Lecture' => $this->faker->boolean,
            'Ajouter' => $this->faker->boolean,
            'Modifier' => $this->faker->boolean,
            'Supprimer' => $this->faker->boolean,
            'ID_Role' => \App\Models\Role::factory(),
        ];
    }
}