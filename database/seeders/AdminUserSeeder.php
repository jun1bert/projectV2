<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
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

        $adminIdentity = ['email' => env('SEED_ADMIN_EMAIL', 'admin@spa.com')];
        $adminData = [
            'name' => env('SEED_ADMIN_NAME', 'System Admin'),
            'password' => $password,
            'role' => 'admin',
        ];

        if (app()->environment('production')) {
            User::firstOrCreate($adminIdentity, $adminData);
        } else {
            User::updateOrCreate($adminIdentity, $adminData);
        }

        if (app()->environment('production')) {
            return;
        }

        User::updateOrCreate(
            ['email' => 'manager@spa.com'],
            [
                'name' => 'Spa Manager',
                'phone' => '09170000001',
                'password' => $password,
                'role' => 'management',
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff1@spa.com'],
            [
                'name' => 'Alyssa Cruz',
                'phone' => '09170000002',
                'password' => $password,
                'role' => 'staff',
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff2@spa.com'],
            [
                'name' => 'Mika Santos',
                'phone' => '09170000003',
                'password' => $password,
                'role' => 'staff',
            ]
        );

        User::updateOrCreate(
            ['email' => 'reception@spa.com'],
            [
                'name' => 'Reception Desk',
                'phone' => '09170000004',
                'password' => $password,
                'role' => 'reception',
            ]
        );
    }
}
