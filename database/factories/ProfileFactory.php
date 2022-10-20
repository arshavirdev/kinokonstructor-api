<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Symfony\Component\String\Slugger\AsciiSlugger;

function randomNumber($length)
{
    $result = '';

    for ($i = 0; $i < $length; $i++) {
        $result .= mt_rand(0, 9);
    }

    return $result;
}

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $slugger = new AsciiSlugger();
        $gender = fake()->randomElement(['male', 'female']);
        $is_org = fake()->boolean();
        $is_entrepreneur = fake()->boolean();

        $firstname = fake()->firstName($gender);
        $lastname = fake()->lastName($gender);

        $nickname = strtolower($slugger->slug($firstname . ' ' . $lastname));
        return [
            'status' => fake()->randomElement(['draft', 'moderation', 'accpted']),
            'user_id' => fake()->numberBetween(1, 10),

//            'gender' => substr($gender, 0, 1),
            'firstname' => $firstname,
            'lastname' => $lastname,
            'middlename' => null,

            'city' => fake()->city(),
            'birthday' => fake()->date(),

            'phone' => fake()->unique()->phoneNumber(),

            'is_org' => $is_org,
            'org_reg_id' => $is_org ? randomNumber(12) : null,
            'org_position' => $is_org ? fake()->word() : null,

            'is_entrepreneur' => $is_entrepreneur,
            'entrepreneur_reg_id' => $is_entrepreneur ? randomNumber(10) : null,

            'portfolio' => fake()->text(),
            'mass_media_mentions' => fake()->text(),

            'socials_vk' => $nickname,
            'socials_tg' => $nickname,
            'socials_ok' => $nickname,
        ];
    }
}
