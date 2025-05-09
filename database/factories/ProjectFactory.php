<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected static $counter = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => 'A' . self::$counter++,
            'client' => fake()->name(),
            'ppk' => fake()->name(),
            'support_teams' => [
                fake()->name(),
                fake()->name(),
            ],
            'value' => fake()->numberBetween(50_000_000, 10_000_000_000),
            'company_id' => Company::factory(),
            'project_leader_id' => User::factory(),
            'start_date' => $startDate = fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'end_date' => Carbon::parse($startDate)->addDays(rand(0, 10))->format('Y-m-d'),
        ];
    }
}
