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
        $prefixes = ['08', '+628', '628'];
        $prefix = fake()->randomElement($prefixes);
        $number = fake()->numberBetween(1, 9) . fake()->numerify('#######');

        return [
            'name' => fake()->unique()->company(),
            'address' => fake()->address(),
            'director_name' => fake()->name(),
            'director_phone' => $prefix . $number,
            'director_signature' => null
        ];
    }
}
