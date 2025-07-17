<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Notification;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition()
    {
        return [
            'Notification' => $this->faker->sentence,
            'Etat' => $this->faker->word,
            'ID_Contact' => \App\Models\Contact::factory(),
        ];
    }
}