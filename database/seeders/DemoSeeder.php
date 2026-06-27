<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo data cannot be seeded in production.');
        }

        $this->call([
            ServiceSeeder::class,
            AdminUserSeeder::class,
            AppointmentSeeder::class,
        ]);
    }
}
