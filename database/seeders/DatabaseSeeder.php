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
        $occupations = (new OccupationSeeder())->run();
        $regions = (new RegionSeeder())->run();

        $admin = \App\Models\User::factory()->create([
            'name' => 'Admin Admin',
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'role' => 'admin'
        ]);

        $moderator = \App\Models\User::factory()->create([
            'name' => 'Moderator',
            'username' => 'moderator',
            'email' => 'moderator@admin.com',
            'role' => 'moderator'
        ]);

        $serviceProfiles = \App\Models\Profile::factory(2)
            ->has(\App\Models\ProfileEducation::factory(rand(1, 2)), 'education')
            ->has(\App\Models\ProfileExperience::factory(rand(1, 3)), 'experience')
//            ->has(\App\Models\ProfileCustomProjects::factory(2))
            ->create(fn() => [
                'status' => 'accepted',
                'is_verified' => true,
                'user_id' => fake()->unique()->randomElement([$admin->id, $moderator->id]),
            ]);

        // Guests
        $guests = \App\Models\User::factory(30)
//            ->has(\App\Models\Profile::factory(1)->afterMaking(function ($model) use ($occupations) {
//                $model->status = fake()->randomElement(['draft', 'moderation']);
//                $model->occupation_id = fake()->randomElement($occupations->pluck('id'));
//            }))
            ->create(['role' => 'guest']);
        \App\Models\Profile::factory(20)->create(fn() => [
            'status' => fake()->randomElement(['draft', 'moderation']),
            'user_id' => fake()->unique()->randomElement($guests->pluck('id')),
        ]);

        // Specialists
        $specialists = \App\Models\User::factory(90)->create(['role' => 'specialist']);
        $specialistProfiles = \App\Models\Profile::factory(90)
            ->has(\App\Models\ProfileEducation::factory(rand(1, 2)), 'education')
            ->has(\App\Models\ProfileExperience::factory(rand(1, 3)), 'experience')
//            ->has(\App\Models\ProfileCustomProjects::factory(2))
            ->create(fn() => [
                'status' => 'accepted',
                'is_verified' => rand(0, 10) === 10,
                'user_id' => fake()->unique()->randomElement($specialists->pluck('id')),
            ]);

        \App\Models\Profile::all()->each(function ($profile) use ($occupations) {
            $profile->occupations()->sync($occupations->random(rand(1, 3))->pluck('id')->toArray());
        });

        \App\Models\Location::factory(10)->create(fn() => [
            'owner_id' => fake()->randomElement($specialistProfiles->pluck('id')),
            'region_id' => fake()->randomElement($regions->pluck('id'))
        ]);

        \App\Models\Project::factory(20)->create(fn() => [
            'owner_id' => fake()->randomElement($specialistProfiles->pluck('id')),
            'status' => rand(0, 1) === 1 ? 'draft' : fake()->randomElement(['moderation', 'rejected', 'accepted'])
        ]);
    }
}
