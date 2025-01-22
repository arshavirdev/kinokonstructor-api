<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $city = fake()->randomElement(['Москва', 'Московская область', 'Санкт-Петербург', 'Екатеринбург', 'Новосибирск',]);
        return [
            'name' => fake()->words(3, true),

            'owner_id' => 1,
            'tags' => fake()->words(4),
            'region_id' => 1,
            'city' => $city,
            'latlng' => join(',', fake()->localCoordinates()),
            'description' => fake()->text()
        ];
    }
}
