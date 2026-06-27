<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        if (config('app.seed_service_catalog')) {
            $this->call(ServiceSeeder::class);
        }

        if (! app()->environment('production') && config('app.seed_demo_data')) {
            $this->call(AppointmentSeeder::class);
        }
    }
}
