<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileExperienceFactory extends Factory
{
    static $positionPrefix = ['Младший', 'Старший', 'Ведущий', 'Главный'];
    static $positions = ['Режиссер', 'Оператор', 'Звукорежиссер', 'Сценарист', 'Костюмер', 'Гример'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $now = new Carbon();
        $start = $now->year - fake()->numberBetween(8, 20);
        $end = $start + fake()->numberBetween(1, 8);
        return [
            'company' => fake()->company(),
            'position' => fake()->randomElement(static::$positionPrefix) . ' ' . fake()->randomElement(static::$positions),

            'start' => $start,
            'end' => $end,
        ];
    }
}
