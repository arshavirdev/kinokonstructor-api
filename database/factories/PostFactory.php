<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $type = fake()->randomElement(['article', 'video', 'pdf']);
        $post = [
            'type' => $type,
            'title' => fake()->words(3, true),
            'content' => fake()->realText(),
            'created_at' => fake()->date()
        ];
//        if ($type === 'article')
//            $post['content'] = fake()->realText();
        if ($type === 'video')
            $post['url'] = fake()->url();
        if ($type === 'pdf')
            $post['url'] = fake()->url();

        return $post;
    }
}
