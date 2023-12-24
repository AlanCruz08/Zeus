<?php

namespace Database\Factories;

use App\Models\Registro;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistroFactory extends Factory
{
    protected $model = Registro::class;

    public function definition()
    {
        return [
            'valor' => $this->faker->randomFloat(2, 0, 100),
            'unidades' => $this->faker->randomElement(['abcd', 'efgh', 'ijkl', 'mnop', 'qrst']),
            'sensor_id' => \App\Models\Sensor::factory(), // Asegúrate de tener el Factory para Sensor
        ];
    }
}
