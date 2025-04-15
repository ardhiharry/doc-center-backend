<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivityDocCategory;
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
            'title' => fake()->sentence(3),
            'file' => null,
            'description' => fake()->paragraph(),
            'tags' => json_encode(fake()->words(3)),
            'activity_doc_category_id' => ActivityDocCategory::factory(),
            'activity_id' => Activity::factory(),
        ];
    }
}
