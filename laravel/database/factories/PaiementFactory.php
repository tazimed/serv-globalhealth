<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Paiement;

class PaiementFactory extends Factory
{
    protected $model = Paiement::class;

    public function definition()
    {
        return [
            'Date' => $this->faker->date,
            'Type' => $this->faker->word,
            'Absence_sup' => $this->faker->randomFloat(2, 0, 10),
            'ID_User' => \App\Models\User::factory(),
        ];
    }
}