<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileEducationFactory extends Factory
{
    static $speciality = [
        'Актерское искусство',
        'Драматургия',
        'Вокальное искусство',
        'Звукорежиссура аудиовизуальных искусств',
        'Режиссура кино и телевидения',
        'Продюсерство',
        'Кинооператорство',
    ];
    static $unis = [
        'ГИТР Институт кино и телевидения',
        'ВГИК Всероссийский государственный университет кинематографии',
        'МГИК Московский государственный институт культуры',
        'Всероссийский государственный университет кинематографии имени С. А. Герасимова'
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $now = new Carbon();
        $start = $now->year - fake()->numberBetween(8, 20);
        $end = $start + 5;
        return [
            'institution' => fake()->randomElement(static::$unis),
            'speciality' => fake()->randomElement(static::$speciality),
            'start' => $start,
            'end' => $end,
        ];

    }
}
