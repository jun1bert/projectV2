<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = env('SEED_USER_PASSWORD');

        if (app()->environment('production') && blank($defaultPassword)) {
            throw new \RuntimeException('Set SEED_USER_PASSWORD before seeding users in production.');
        }

        $password = Hash::make($defaultPassword ?: 'password123');

        User::updateOrCreate(
            ['email' => 'admin@spa.com'],
            [
                'name' => 'System Admin',
                'password' => $password,
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager@spa.com'],
            [
                'name' => 'Spa Manager',
                'password' => $password,
                'role' => 'management',
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff1@spa.com'],
            [
                'name' => 'Staff Member 1',
                'password' => $password,
                'role' => 'staff',
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff2@spa.com'],
            [
                'name' => 'Staff Member 2',
                'password' => $password,
                'role' => 'staff',
            ]
        );

        User::updateOrCreate(
            ['email' => 'reception@spa.com'],
            [
                'name' => 'Reception Desk',
                'password' => $password,
                'role' => 'reception',
            ]
        );
    }
}
