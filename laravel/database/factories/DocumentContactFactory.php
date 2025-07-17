<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DocumentContact;

class DocumentContactFactory extends Factory
{
    protected $model = DocumentContact::class;

    public function definition()
    {
        return [
            'Nom_Doc' => $this->faker->word,
            'Doc' => $this->faker->imageUrl(),
            'ID_Contact' => \App\Models\Contact::factory(),
        ];
    }
}