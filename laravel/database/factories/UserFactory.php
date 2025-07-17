<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'Nom' => $this->faker->lastName,
            'Prenom' => $this->faker->firstName,
            'Email' => $this->faker->unique()->safeEmail,
            'Password' => Hash::make('password'),
            'Photo' => $this->faker->imageUrl(),
            'Post' => $this->faker->jobTitle,
            'Tel' => $this->faker->phoneNumber,
            'Adresse' => $this->faker->address,
            'Specialisation' => $this->faker->sentence,
            'Salaire' => $this->faker->randomFloat(2, 1000, 5000),
            'Heur_sup_prime' => $this->faker->randomFloat(2, 10, 50),
            'Delai_rappel' => $this->faker->numberBetween(1, 30),
            'Sex' => $this->faker->randomElement(['Male', 'Female']),
            'ID_Role' => \App\Models\Role::factory(),
        ];
    }
}