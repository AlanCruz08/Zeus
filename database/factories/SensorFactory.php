<?php

namespace Database\Factories;

use App\Models\Sensor;
use Illuminate\Database\Eloquent\Factories\Factory;

class SensorFactory extends Factory
{
    protected $model = Sensor::class;

    public function definition()
    {
        return [
            'key' => $this->faker->word,
            'descripcion' => $this->faker->sentence,
            'coche_id' => \App\Models\Coche::factory(), // Asegúrate de tener el Factory para Coche
        ];
    }
}
