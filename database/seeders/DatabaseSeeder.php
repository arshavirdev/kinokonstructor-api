<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $ocupations = (new OccupationSeeder())->run();
//        dd($ocupations->pluck('id'));

        \App\Models\User::factory()->create([
            'name' => 'Admin Admin',
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'role' => 'admin'
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Moderator',
            'username' => 'moderator',
            'email' => 'moderator@admin.com',
            'role' => 'moderator'
        ]);

        // Guests
        $guests = \App\Models\User::factory(3)->create(['role' => 'guest']);
        \App\Models\Profile::factory(2)->create(fn() => [
            'status' => fake()->randomElement(['draft', 'moderation']),
            'user_id' => fake()->unique()->randomElement($guests->pluck('id')),
            'occupation_id' => fake()->randomElement($ocupations->pluck('id'))
        ]);

        // Specialists
        $specialists = \App\Models\User::factory(7)->create(['role' => 'specialist']);
        $specialistProfiles = \App\Models\Profile::factory(7)->create(fn() => [
            'status' => 'accepted',
            'user_id' => fake()->unique()->randomElement($specialists->pluck('id')),
            'occupation_id' => rand(0, 1) === 1 ? fake()->randomElement($ocupations->pluck('id')) : 1
        ]);

        \App\Models\Location::factory(10)->create(fn() => ['owner_id' => fake()->randomElement($specialistProfiles->pluck('id'))]);
        // \App\Models\Project::factory(5)->create();
        \App\Models\News::factory(5)->create();
        \App\Models\Post::factory(5)->create();
    }
}
