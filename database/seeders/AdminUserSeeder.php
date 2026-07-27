<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@codexehub.com';
        $password = Str::password(14);

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'CodexeHub Admin',
                'password' => $password,
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command?->info("Admin user ready — email: {$email} | password: {$password}");
    }
}
