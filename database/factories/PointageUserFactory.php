<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PointageUser;

class PointageUserFactory extends Factory
{
    protected $model = PointageUser::class;

    public function definition()
    {
        return [
            'ID_Pointage' => \App\Models\Pointage::factory(),
            'ID_User' => \App\Models\User::factory(),
            'Heur_Travail' => $this->faker->numberBetween(1, 24),
            'Abssance' => $this->faker->boolean,
        ];
    }
}