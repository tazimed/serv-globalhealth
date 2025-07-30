<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Conge;

class CongeFactory extends Factory
{
    protected $model = Conge::class;

    public function definition()
    {
        return [
            'Date_debut' => $this->faker->dateTimeThisMonth,
            'Date_fin' => $this->faker->dateTimeThisMonth('+1 week'),
            'Type' => $this->faker->word,
            'ID_User' => \App\Models\User::factory(),
        ];
    }
}