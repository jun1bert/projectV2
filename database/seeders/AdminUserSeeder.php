<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // ADMIN
        User::updateOrCreate(
            ['email' => 'admin@spa.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // MANAGER
        User::updateOrCreate(
            ['email' => 'manager@spa.com'],
            [
                'name' => 'Spa Manager',
                'password' => Hash::make('password123'),
                'role' => 'management',
            ]
        );

        // STAFF
        User::updateOrCreate(
            ['email' => 'staff1@spa.com'],
            [
                'name' => 'Staff Member 1',
                'password' => Hash::make('password123'),
                'role' => 'staff',
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff2@spa.com'],
            [
                'name' => 'Staff Member 2',
                'password' => Hash::make('password123'),
                'role' => 'staff',
            ]
        );
    }
}
