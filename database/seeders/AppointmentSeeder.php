<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\ConsentForm;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Sample appointments cannot be seeded in production.');
        }

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
                'service' => 'Swedish Relaxation Massage - 1 Hour',
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
                'service' => 'Foot Reflex Dagdagay',
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
                'service' => 'Back Massage',
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
                'service' => 'Therapeutic Recovery Massage - 1 Hour',
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
                'service' => 'Microneedling Face',
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
                'service' => 'Martinis Signature Massage - 1 Hour',
                'date' => now()->addDays(2)->toDateString(),
                'time' => '16:00',
                'notes' => 'Pending online request.',
                'status' => 'pending',
                'booking_type' => 'online',
                'assigned_staff_email' => null,
                'payment_status' => 'unpaid',
            ],
            [
                'full_name' => 'Maria Santos',
                'contact_number' => '09661234567',
                'email' => 'another.maria@example.com',
                'service' => 'Classic Manicure',
                'date' => now()->addDays(3)->toDateString(),
                'time' => '13:30',
                'notes' => 'Different client with the same name for identity testing.',
                'status' => 'confirmed',
                'booking_type' => 'walk-in',
                'assigned_staff_email' => 'staff2@spa.com',
                'payment_status' => 'unpaid',
            ],
            [
                'full_name' => 'Maria Santos',
                'contact_number' => '09171234567',
                'email' => 'maria.santos@example.com',
                'service' => 'Foot Reflex Dagdagay',
                'date' => now()->subDays(7)->toDateString(),
                'time' => '11:30',
                'notes' => 'Previous visit for returning-client suggestion testing.',
                'status' => 'completed',
                'booking_type' => 'walk-in',
                'assigned_staff_email' => 'staff1@spa.com',
                'payment_status' => 'paid',
            ],
        ];

        foreach ($appointments as $appointment) {
            $service = $services[$appointment['service']] ?? $services->first();
            $assignedStaff = $appointment['assigned_staff_email']
                ? ($staff[$appointment['assigned_staff_email']] ?? null)
                : null;
            $contact = str_starts_with($appointment['contact_number'], '+63')
                ? '0'.substr($appointment['contact_number'], 3)
                : $appointment['contact_number'];
            $client = Client::updateOrCreate(
                ['contact_number' => $contact],
                [
                    'full_name' => $appointment['full_name'],
                    'email' => $appointment['email'],
                ]
            );

            $savedAppointment = Appointment::updateOrCreate(
                [
                    'contact_number' => $appointment['contact_number'],
                    'date' => $appointment['date'],
                    'time' => $appointment['time'],
                ],
                [
                    'full_name' => $appointment['full_name'],
                    'email' => $appointment['email'],
                    'client_id' => $client->id,
                    'service_id' => $service->id,
                    'price_at_booking' => $service->price,
                    'notes' => $appointment['notes'],
                    'status' => $appointment['status'],
                    'booking_type' => $appointment['booking_type'],
                    'completion_notified_at' => null,
                ]
            );

            $savedAppointment->assignedStaffMembers()->sync($assignedStaff ? [$assignedStaff->id] : []);

            if ($appointment['payment_status'] === 'paid') {
                Invoice::updateOrCreate(
                    ['appointment_id' => $savedAppointment->id],
                    [
                        'service_total' => $service->price,
                        'grand_total' => $service->price,
                        'amount_paid' => $service->price,
                        'payment_method' => 'cash',
                        'status' => 'paid',
                    ]
                );
            }

            if ($service->requires_consent) {
                $signaturePath = 'consents/demo-signature.svg';
                Storage::disk('local')->put($signaturePath, <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 420 120">
  <path d="M28 78c42-65 32 38 72-17 23-31 9 48 54 5 24-23 26 31 60 3 30-25 48 20 83-5 26-18 46 3 91-13" fill="none" stroke="#4d4037" stroke-width="4" stroke-linecap="round"/>
</svg>
SVG);

                ConsentForm::updateOrCreate(
                    ['appointment_id' => $savedAppointment->id],
                    [
                        'service_id' => $service->id,
                        'full_name' => $appointment['full_name'],
                        'contact_number' => $appointment['contact_number'],
                        'email' => $appointment['email'],
                        'consent_text' => 'I understand the service, have disclosed relevant health information, and consent to receive treatment.',
                        'signature_path' => $signaturePath,
                        'signed_at' => now(),
                    ]
                );
            }
        }
    }
}
