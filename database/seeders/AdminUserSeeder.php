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
            ['email' => 'admin@mmspa.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // MANAGER
        User::updateOrCreate(
            ['email' => 'manager@mmspa.com'],
            [
                'name' => 'Spa Manager',
                'password' => Hash::make('password123'),
                'role' => 'management',
            ]
        );

        // STAFF
        User::updateOrCreate(
            ['email' => 'staff@mmspa.com'],
            [
                'name' => 'Front Desk Staff',
                'password' => Hash::make('password123'),
                'role' => 'staff',
            ]
        );
    }
}
