<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityDoc;
use App\Models\ActivityCategory;
use App\Models\AdminDoc;
use App\Models\AdminDocCategory;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectTeam;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    private int $contractCounter = 1;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->createAdminUsers();
        $users = $this->createRegularUsers(10);
//        $this->createCompaniesWithProjects(5, $users);
    }

    private function createAdminUsers(): void
    {
        User::factory()->createMany([
            [
                'username' => 'superadmin',
                'password' => Hash::make('password123'),
                'name' => 'Super Admin',
                'role' => 'SUPERADMIN',
            ],
            [
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'name' => 'Admin',
                'role' => 'ADMIN',
            ],
            [
                'username' => 'ladur',
                'password' => Hash::make('password123'),
                'name' => 'Ladur Cobain',
                'role' => 'SUPERADMIN',
            ],
            [
                'username' => 'togi',
                'password' => Hash::make('password123'),
                'name' => 'Togi Togtog',
                'role' => 'USER',
            ],
        ]);
    }

    private function createRegularUsers(int $count)
    {
        return User::factory($count)->create([
            'role' => 'USER',
        ]);
    }

    private function createCompaniesWithProjects(int $count, $users): void
    {
        Company::factory($count)->create()->each(function ($company) use ($users) {
            $this->createProjectsForCompany($company, $users);
        });
    }

    private function createProjectsForCompany(Company $company, $users): void
    {
        for ($i = 0; $i < rand(2, 3); $i++) {
            $teamUsers = $users->random(rand(2, 4));
            $projectLeader = $teamUsers->random();
            $counter = 1;
            $amounts = [
                50000000,
                100000000,
                250000000,
                500000000,
                1000000000,
                2000000000,
                5000000000,
                10000000000,
            ];

            $startDate = fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d');
            $endDate = Carbon::parse($startDate)->addDays(rand(1, 10))->format('Y-m-d');
            $maintenanceDate = Carbon::parse($startDate)->addYears(rand(1, 5))->format('Y-m-d');

            $project = Project::create([
                'name' => fake()->unique()->words(2, true),
                'code' => 'A' . $counter++,
                'contract_number' => (function () {
                    static $counter = 1;
                    $prefix = 'B-' . $this->contractCounter++;
                    $middle = 'C.6/Cpl.2';

                    $date = fake()->dateTimeBetween('-1 month', '+1 month');
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
                'value' => fake()->randomElement($amounts),
                'status' => fake()->randomElement(['WAITING', 'ON PROGRESS', 'CLOSED']),
                'progress' => fake()->numberBetween(0, 100),
                'company_id' => $company->id,
                'project_leader_id' => $projectLeader->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'maintenance_date' => $maintenanceDate,
            ]);

            foreach ($teamUsers as $user) {
                ProjectTeam::create([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                ]);
            }

            $this->createAdminDocsForProject($project);
            $this->createActivitiesForProject($project);
        }
    }

    private function createAdminDocsForProject(Project $project): void
    {
        $adminDocCategory = AdminDocCategory::factory()->create();

        AdminDoc::factory(rand(1, 3))->create([
            'project_id' => $project->id,
            'admin_doc_category_id' => $adminDocCategory->id,
        ]);
    }

    private function createActivitiesForProject(Project $project): void
    {
        $teamUsers = $project->users;

        Activity::factory(rand(1, 2))->create([
            'project_id' => $project->id,
            'author_id' => $teamUsers->random()->id,
        ])->each(function ($activity) {
            $this->createActivityDocForActivity($activity);
        });
    }

    private function createActivityDocForActivity(Activity $activity): void
    {
        $activityCategory = ActivityCategory::factory()->create([
            'project_id' => $activity->project_id,
        ]);

        $activity->update([
            'activity_category_id' => $activityCategory->id,
        ]);

        ActivityDoc::factory()->create([
            'activity_id' => $activity->id,
        ]);
    }
}
