<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Pointage;

class PointageFactory extends Factory
{
    protected $model = Pointage::class;

    public function definition()
    {
        return [
            'Date' => $this->faker->date,
        ];
    }
}