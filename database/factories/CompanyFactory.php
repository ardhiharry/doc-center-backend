<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'address' => fake()->address(),
            'director_name' => fake()->name(),
            'director_signature' => null,
            'established_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
        ];
    }
}
