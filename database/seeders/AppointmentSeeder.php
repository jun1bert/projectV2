<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::where('is_active', true)->get()->keyBy('name');
        $staff = User::where('role', 'staff')->get()->keyBy('email');

        if ($services->isEmpty()) {
            return;
        }

        $appointments = [
            [
                'full_name' => 'Maria Santos',
                'contact_number' => '09171234567',
                'email' => 'maria.santos@example.com',
                'service' => 'Swedish Massage',
                'date' => now()->toDateString(),
                'time' => '09:00',
                'notes' => 'Online booking. Prefers a quiet room.',
                'status' => 'confirmed',
                'booking_type' => 'online',
                'assigned_staff_email' => 'staff1@spa.com',
                'payment_status' => 'unpaid',
            ],
            [
                'full_name' => 'Pedro Reyes',
                'contact_number' => '09281234567',
                'email' => null,
                'service' => 'Foot Reflexology',
                'date' => now()->toDateString(),
                'time' => '10:30',
                'notes' => 'Walk-in client requested available wellness staff.',
                'status' => 'confirmed',
                'booking_type' => 'walk-in',
                'assigned_staff_email' => 'staff2@spa.com',
                'payment_status' => 'unpaid',
            ],
            [
                'full_name' => 'Ana Cruz',
                'contact_number' => '09351234567',
                'email' => 'ana.cruz@example.com',
                'service' => 'Back and Shoulder Massage',
                'date' => now()->subDay()->toDateString(),
                'time' => '14:00',
                'notes' => 'Completed sample with paid receipt flow.',
                'status' => 'completed',
                'booking_type' => 'walk-in',
                'assigned_staff_email' => 'staff1@spa.com',
                'payment_status' => 'paid',
            ],
            [
                'full_name' => 'Mark Dela Rosa',
                'contact_number' => '09461234567',
                'email' => 'mark.delarosa@example.com',
                'service' => 'Deep Tissue Massage',
                'date' => now()->addDay()->toDateString(),
                'time' => '15:30',
                'notes' => 'Client called to cancel.',
                'status' => 'cancelled',
                'booking_type' => 'online',
                'assigned_staff_email' => null,
                'payment_status' => 'unpaid',
            ],
            [
                'full_name' => 'Sarah Mendoza',
                'contact_number' => '09571234567',
                'email' => 'sarah.mendoza@example.com',
                'service' => 'Prenatal Massage',
                'date' => now()->addDay()->toDateString(),
                'time' => '11:00',
                'notes' => 'Confirmed but intentionally unassigned for reception review.',
                'status' => 'confirmed',
                'booking_type' => 'online',
                'assigned_staff_email' => null,
                'payment_status' => 'unpaid',
            ],
            [
                'full_name' => 'Christine Lopez',
                'contact_number' => '09791234567',
                'email' => null,
                'service' => 'Aromatherapy Massage',
                'date' => now()->addDays(2)->toDateString(),
                'time' => '16:00',
                'notes' => 'Pending online request.',
                'status' => 'pending',
                'booking_type' => 'online',
                'assigned_staff_email' => null,
                'payment_status' => 'unpaid',
            ],
        ];

        foreach ($appointments as $appointment) {
            $service = $services[$appointment['service']] ?? $services->first();
            $assignedStaff = $appointment['assigned_staff_email']
                ? ($staff[$appointment['assigned_staff_email']] ?? null)
                : null;

            Appointment::updateOrCreate(
                [
                    'contact_number' => $appointment['contact_number'],
                    'date' => $appointment['date'],
                    'time' => $appointment['time'],
                ],
                [
                    'full_name' => $appointment['full_name'],
                    'email' => $appointment['email'],
                    'service_id' => $service->id,
                    'notes' => $appointment['notes'],
                    'status' => $appointment['status'],
                    'booking_type' => $appointment['booking_type'],
                    'assigned_to' => $assignedStaff?->id,
                    'payment_status' => $appointment['payment_status'],
                    'completion_notified_at' => null,
                ]
            );
        }
    }
}
