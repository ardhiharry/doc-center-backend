<?php

namespace Database\Factories;

use App\Models\ActivityCategory;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(2, true),
            'start_date' => $startDate = fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'end_date' => Carbon::parse($startDate)->addDays(rand(0, 10))->format('Y-m-d'),
            'activity_category_id' => ActivityCategory::factory(),
            'project_id' => Project::factory(),
            'author_id' => User::factory(),
        ];
    }
}
