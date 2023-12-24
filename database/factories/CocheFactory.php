<?php

namespace Database\Factories;

use App\Models\Coche;
use Illuminate\Database\Eloquent\Factories\Factory;

class CocheFactory extends Factory
{
    protected $model = Coche::class;

    public function definition()
    {
        return [
            'alias' => $this->faker->word,
            'descripcion' => $this->faker->sentence,
            'codigo' => $this->faker->unique()->randomNumber(6),
            'user_id' => \App\Models\User::factory(), // Asegúrate de tener el Factory para User
        ];
    }
}