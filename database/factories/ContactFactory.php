<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Contact;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition()
    {
        return [
            'Nom' => $this->faker->lastName,
            'Prenom' => $this->faker->firstName,
            'Birthday' => $this->faker->date,
            'N_assurance' => $this->faker->word,
            'Cnss' => $this->faker->word,
            'Telephone' => $this->faker->phoneNumber,
            'Email' => $this->faker->unique()->safeEmail,
            'Adresse' => $this->faker->address,
            'preferences' => $this->faker->sentence,
        ];
    }
}