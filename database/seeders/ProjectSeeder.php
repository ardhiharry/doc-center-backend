<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::create([
            'project_name' => 'Project Test',
            'company_name' => 'Company Test',
            'company_address' => 'Address Test',
            'director_name' => 'Director Test',
            'director_phone' => '1234567890',
            'start_date' => '2023-01-01',
            'end_date' => '2023-12-31',
        ]);
    }
}
