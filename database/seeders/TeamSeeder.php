<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['name' => 'Rashed Hossain', 'role' => 'CEO & Founder', 'description' => 'Rashed founded CodexeHub with a vision to help businesses grow through reliable, well-engineered software. He leads company strategy and client partnerships.'],
            ['name' => 'Nusrat Jahan', 'role' => 'Project Manager', 'description' => 'Nusrat keeps every project on track, coordinating between clients and the engineering team to make sure deadlines and quality bars are both met.'],
            ['name' => 'Sabbir Ahmed', 'role' => 'Lead Developer', 'description' => 'Sabbir architects and builds the core of most CodexeHub projects, with a strong focus on clean, maintainable, and scalable code.'],
            ['name' => 'Tanvir Hasan', 'role' => 'UI/UX Designer', 'description' => 'Tanvir designs interfaces that are as functional as they are beautiful, turning complex requirements into intuitive user experiences.'],
            ['name' => 'Mariya Islam', 'role' => 'QA Engineer', 'description' => 'Mariya ensures every release meets a high quality bar through thorough manual and automated testing before it reaches production.'],
            ['name' => 'Fahim Rahman', 'role' => 'DevOps Engineer', 'description' => 'Fahim manages infrastructure, deployments, and CI/CD pipelines, keeping every CodexeHub project running smoothly and securely.'],
        ];

        foreach ($members as $index => $member) {
            TeamMember::updateOrCreate(
                ['name' => $member['name']],
                [
                    'role' => $member['role'],
                    'description' => $member['description'],
                    'status' => 'active',
                    'sort_order' => $index,
                ]
            );
        }
    }
}
