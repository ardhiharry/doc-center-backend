<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityDoc;
use App\Models\ActivityDocCategory;
use App\Models\AdminDoc;
use App\Models\AdminDocCategory;
use App\Models\Company;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->createAdminUsers();
        $this->createRegularUsers(10);
        $this->createCompaniesWithProjects(5);
    }

    private function createAdminUsers(): void
    {
        User::factory()->create([
            'username' => 'superadmin',
            'password' => Hash::make('password123'),
            'name' => 'Super Admin',
            'role' => 'SUPERADMIN'
        ]);

        User::factory()->create([
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'name' => 'Admin',
            'role' => 'ADMIN'
        ]);

        User::factory()->create([
            'username' => 'ladur',
            'password' => Hash::make('password123'),
            'name' => 'Ladur Cobain',
            'role' => 'SUPERADMIN'
        ]);

        User::factory()->create([
            'username' => 'togi',
            'password' => Hash::make('password123'),
            'name' => 'Togi Togtog',
            'role' => 'USER'
        ]);
    }

    private function createRegularUsers(int $count): void
    {
        User::factory($count)->create([
            'role' => 'USER'
        ]);
    }

    private function createCompaniesWithProjects(int $count): void
    {
        Company::factory($count)->create()->each(function ($company) {
            $this->createProjectsForCompany($company);
        });
    }

    private function createProjectsForCompany(Company $company): void
    {
        Project::factory(rand(2, 3))->create([
            'company_id' => $company->id
        ])->each(function ($project) {
            $this->assignUsersToTeam($project);
            $this->createAdminDocsForProject($project);
            $this->createActivitiesForProject($project);
        });
    }

    private function assignUsersToTeam(Project $project): void
    {
        $userIds = User::where('role', 'USER')->inRandomOrder()->take(rand(2, 4))->pluck('id');

        foreach ($userIds as $userId) {
            if (!Team::where('project_id', $project->id)->where('user_id', $userId)->exists()) {
                Team::factory()->create([
                    'project_id' => $project->id,
                    'user_id' => $userId
                ]);
            }
        }
    }

    private function createAdminDocsForProject(Project $project): void
    {
        $adminDocCategory = AdminDocCategory::factory()->create();

        AdminDoc::factory(rand(1, 3))->create([
            'project_id' => $project->id,
            'admin_doc_category_id' => $adminDocCategory->id
        ]);
    }

    private function createActivitiesForProject(Project $project): void
    {
        Activity::factory(rand(1, 2))->create([
            'project_id' => $project->id
        ])->each(function ($activity) {
            $this->createActivityDocsForActivity($activity);
        });
    }

    private function createActivityDocsForActivity(Activity $activity): void
    {
        $activityDocCategory = ActivityDocCategory::factory()->create();

        ActivityDoc::factory(rand(1, 3))->create([
            'activity_id' => $activity->id,
            'activity_doc_category_id' => $activityDocCategory->id
        ]);
    }
}
