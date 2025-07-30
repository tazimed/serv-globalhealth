<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RendezVous;

class RendezVousFactory extends Factory
{
    protected $model = RendezVous::class;

    public function definition()
    {
        return [
            'Frequence' => $this->faker->word,
            'Date' => $this->faker->date,
            'Status' => $this->faker->word,
            'ID_User' => \App\Models\User::factory(),
            'ID_Contact' => \App\Models\Contact::factory(),
            'ID_Prestation' => \App\Models\Prestation::factory(),
        ];
    }
}