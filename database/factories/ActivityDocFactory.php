<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivityCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityDoc>
 */
class ActivityDocFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(3, true),
            'date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'location' => fake()->city(),
            'meet_of_person' => [
                fake()->name(),
                fake()->name(),
                fake()->name(),
            ],
            'agenda' => [
                fake()->sentence(),
                fake()->sentence(),
                fake()->sentence(),
            ],
            'activity' => [
                fake()->sentence(),
                fake()->sentence(),
                fake()->sentence(),
            ],
            'files' => null,
            'tags' => [
                fake()->word(),
                fake()->word(),
                fake()->word(),
            ],
            'activity_id' => Activity::factory(),
        ];
    }
}
