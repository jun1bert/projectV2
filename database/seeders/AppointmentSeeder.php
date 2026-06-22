<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Service;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ FIXED SERVICE CREATION
        $service = Service::first();

        if (!$service) {
            $service = Service::create([
                'name' => 'Massage Therapy',
                'description' => 'Auto-generated service',
                'price' => 500,       // ✅ FIXED
                'duration' => 60,     // ✅ FIXED
                'is_active' => 1,
            ]);
        }

        Appointment::create([
            'full_name' => 'John Garcia',
            'contact_number' => '09830133744',
            'service_id' => $service->id,
            'date' => '2026-06-23',
            'time' => '13:00',
            'notes' => 'Auto-generated test booking',
            'status' => 'confirmed',
        ]);
    }
}