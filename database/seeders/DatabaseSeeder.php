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
        // Create Users
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

        User::factory(10)->create([
            'role' => 'USER'
        ]);

        // Create Companies
        Company::factory(5)->create()->each(function ($company) {
            // Each company has 2-3 projects
            Project::factory(rand(2, 3))->create([
                'company_id' => $company->id
            ])->each(function ($project) {

                // Assign random users (USER role) to teams in this project
                $users = User::where('role', 'USER')->inRandomOrder()->take(rand(2, 4))->pluck('id');
                foreach ($users as $userId) {
                    Team::factory()->create([
                        'project_id' => $project->id,
                        'user_id' => $userId
                    ]);
                }

                // Create AdminDocCategory and AdminDocs
                $adminDocCategory = AdminDocCategory::factory()->create();
                AdminDoc::factory(rand(1, 3))->create([
                    'project_id' => $project->id,
                    'admin_doc_category_id' => $adminDocCategory->id
                ]);

                // Create Activities with ActivityDocs
                Activity::factory(rand(1, 2))->create([
                    'project_id' => $project->id
                ])->each(function ($activity) {
                    $activityDocCategory = ActivityDocCategory::factory()->create();
                    ActivityDoc::factory(rand(1, 3))->create([
                        'activity_id' => $activity->id,
                        'activity_doc_category_id' => $activityDocCategory->id
                    ]);
                });
            });
        });
    }
}
