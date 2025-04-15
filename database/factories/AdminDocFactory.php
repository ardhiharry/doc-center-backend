<?php

namespace Database\Factories;

use App\Models\AdminDocCategory;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminDoc>
 */
class AdminDocFactory extends Factory
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
            'project_id' => Project::factory(),
            'admin_doc_category_id' => AdminDocCategory::factory(),
        ];
    }
}
