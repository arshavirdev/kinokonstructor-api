<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => fake()->words(3, true),
            'format' => fake()->randomElement(['movie', 'series']),
            'genre_type' => fake()->randomElement(['documentary', 'fictional']),
            'chronography' => fake()->randomElement([40, 60, 90, 110, 120, 130, 140, 150, 160]),
            'series_count' => fake()->randomElement([1, 5, 10, 15, 20]),
            'genres' => fake()->randomElements([1, 2, 3, 4], 2),
            'logline' => fake()->realText(),
            'synopsis' => fake()->realText(),
            'relevance' => fake()->realText(),
            'additional' => fake()->realText(),
            'budget' => fake()->numberBetween(5, 500) * 100_000,
            'co_financing' => fake()->numberBetween(5, 250) * 10_000
        ];
    }
}
