<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Rappel;

class RappelFactory extends Factory
{
    protected $model = Rappel::class;

    public function definition()
    {
        return [
            'Rappel' => $this->faker->sentence,
            'Etat' => $this->faker->word,
            'ID_User' => \App\Models\User::factory(),
        ];
    }
}