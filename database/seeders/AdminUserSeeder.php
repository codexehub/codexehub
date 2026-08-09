<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
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
        $password = Hash::make('12345678'); // You can change the password as needed

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
