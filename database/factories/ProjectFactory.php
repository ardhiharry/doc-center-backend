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
    protected static $contractCounter = 1;

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
            'contract_number' => (function () {
                static $counter = 1;
                $prefix = 'B-' . self::$contractCounter++;
                $middle = 'C.6/Cpl.2';

                $date = fake()->dateTimeBetween('-3 month', '+4 month');
                $month = $date->format('m');
                $year = $date->format('Y');

                return "{$prefix}/{$middle}/{$month}/{$year}";
            })(),
            'contract_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'client' => fake()->name(),
            'ppk' => fake()->name(),
            'support_teams' => [
                fake()->name(),
                fake()->name(),
            ],
            'value' => fake()->numberBetween(50_000_000, 10_000_000_000),
            'status' => fake()->randomElement(['WAITING', 'ON PROGRESS', 'CLOSED']),
            'progress' => fake()->numberBetween(0, 100),
            'company_id' => Company::factory(),
            'project_leader_id' => User::factory(),
            'start_date' => $startDate = fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'end_date' => Carbon::parse($startDate)->addDays(rand(0, 10))->format('Y-m-d'),
            'maintenance_date' => Carbon::parse($startDate)->addYears(rand(1, 5))->format('Y-m-d'),
        ];
    }
}
