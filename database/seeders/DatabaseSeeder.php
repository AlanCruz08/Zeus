<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Coche;
use App\Models\Sensor;
use App\Models\Registro;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory(5)->create()->each(function ($user) {
            $user->coches()->saveMany(
                Coche::factory(2)->create()->each(function ($coche) {
                    $coche->sensors()->saveMany(
                        Sensor::factory(3)->create()->each(function ($sensor) {
                            $sensor->registros()->saveMany(Registro::factory(10)->create());
                        })
                    );
                })
            );
        });
    }
}
