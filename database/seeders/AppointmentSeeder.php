<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Service;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::all();

        if ($services->isEmpty()) {
            return;
        }

        $appointments = [
            [
                'full_name' => 'John Garcia',
                'contact_number' => '09830133744',
                'date' => '2026-06-23',
                'time' => '13:00',
                'notes' => 'Auto-generated test booking',
                'status' => 'confirmed',
            ],
            [
                'full_name' => 'Maria Santos',
                'contact_number' => '09171234567',
                'date' => '2026-06-24',
                'time' => '09:00',
                'notes' => 'Consultation appointment',
                'status' => 'pending',
            ],
            [
                'full_name' => 'Pedro Reyes',
                'contact_number' => '09281234567',
                'date' => '2026-06-24',
                'time' => '10:30',
                'notes' => 'Follow-up session',
                'status' => 'confirmed',
            ],
            [
                'full_name' => 'Ana Cruz',
                'contact_number' => '09351234567',
                'date' => '2026-06-25',
                'time' => '14:00',
                'notes' => 'First-time client',
                'status' => 'completed',
            ],
            [
                'full_name' => 'Mark Dela Rosa',
                'contact_number' => '09461234567',
                'date' => '2026-06-25',
                'time' => '15:30',
                'notes' => 'Requested afternoon schedule',
                'status' => 'cancelled',
            ],
            [
                'full_name' => 'Sarah Mendoza',
                'contact_number' => '09571234567',
                'date' => '2026-06-26',
                'time' => '08:30',
                'notes' => 'Priority booking',
                'status' => 'confirmed',
            ],
            [
                'full_name' => 'James Villanueva',
                'contact_number' => '09681234567',
                'date' => '2026-06-26',
                'time' => '11:00',
                'notes' => 'Walk-in converted to appointment',
                'status' => 'pending',
            ],
            [
                'full_name' => 'Christine Lopez',
                'contact_number' => '09791234567',
                'date' => '2026-06-27',
                'time' => '16:00',
                'notes' => 'Requested reminder call',
                'status' => 'confirmed',
            ],
        ];

        foreach ($appointments as $appointment) {
            Appointment::create([
                ...$appointment,
                'service_id' => $services->random()->id,
            ]);
        }
    }
}