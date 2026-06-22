<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        Service::create([
            'name' => 'Massage Therapy',
            'price' => 800,
            'duration' => 60,
            'description' => 'Auto-generated service',
            'is_active' => 1,
        ]);
    }
}