<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DocumentUser;

class DocumentUserFactory extends Factory
{
    protected $model = DocumentUser::class;

    public function definition()
    {
        return [
            'Nom_Doc' => $this->faker->word,
            'Doc' => $this->faker->imageUrl(),
            'ID_User' => \App\Models\User::factory(),
        ];
    }
}