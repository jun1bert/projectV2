<?php

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function systemUser(string $role): User
{
    return User::factory()->create([
        'role' => $role,
    ]);
}

function systemService(array $attributes = []): Service
{
    return Service::create(array_merge([
        'name' => 'Swedish Massage',
        'price' => 1000,
        'duration' => 60,
        'description' => 'Relaxing service',
        'is_active' => true,
        'requires_consent' => false,
    ], $attributes));
}

it('allows an online client booking to be confirmed by reception', function () {
    $service = systemService();
    $reception = systemUser('reception');

    $this->post(route('appointments.store'), [
        'full_name' => 'Client Online',
        'contact_number' => '09171234567',
        'email' => 'client@example.com',
        'service_id' => $service->id,
        'date' => now()->addDay()->toDateString(),
        'time' => '09:00',
        'notes' => 'Online test booking',
    ])->assertRedirect('/#book');

    $appointment = Appointment::first();

    expect($appointment)
        ->not->toBeNull()
        ->and($appointment->status)->toBe('pending')
        ->and($appointment->booking_type)->toBe('online');

    $this->actingAs($reception)
        ->postJson(route('appointments.updateStatus', $appointment->id), [
            'status' => 'confirmed',
            'assigned_staff_ids' => [],
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect($appointment->fresh()->status)->toBe('confirmed');
});

it('keeps clients with the same name but different contact numbers separate', function () {
    $service = systemService();

    foreach ([['09170000001', '09:00'], ['09170000002', '09:30']] as [$contact, $time]) {
        $this->post(route('appointments.store'), [
            'full_name' => 'Same Name',
            'contact_number' => $contact,
            'email' => null,
            'service_id' => $service->id,
            'date' => now()->addDay()->toDateString(),
            'time' => $time,
        ])->assertRedirect('/#book');
    }

    expect(Appointment::where('full_name', 'Same Name')->pluck('client_id')->unique()->count())->toBe(2);
});

it('opens the appointment list without staff service specialization relationships', function () {
    $service = systemService();
    $reception = systemUser('reception');

    Appointment::create([
        'full_name' => 'Calendar Client',
        'contact_number' => '09172223333',
        'email' => 'calendar@example.com',
        'service_id' => $service->id,
        'date' => now()->toDateString(),
        'time' => '09:00',
        'status' => 'confirmed',
        'booking_type' => 'online',
    ]);

    $this->actingAs($reception)
        ->get(route('appointments.index'))
        ->assertOk()
        ->assertSee('Calendar Client');
});

it('allows reception to assign multiple staff members to a confirmed appointment', function () {
    $service = systemService();
    $reception = systemUser('reception');
    $staff = systemUser('staff');
    $secondStaff = systemUser('staff');

    $appointment = Appointment::create([
        'full_name' => 'Assign Client',
        'contact_number' => '09173334444',
        'email' => 'assign@example.com',
        'service_id' => $service->id,
        'date' => now()->addDay()->toDateString(),
        'time' => '10:00',
        'status' => 'confirmed',
        'booking_type' => 'online',
    ]);

    $this->actingAs($reception)
        ->postJson(route('appointments.updateStatus', $appointment->id), [
            'status' => 'confirmed',
            'assigned_staff_ids' => [$staff->id, $secondStaff->id],
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect($appointment->fresh()->assignedStaffMembers()->pluck('users.id')->all())
        ->toEqualCanonicalizing([$staff->id, $secondStaff->id]);
});

it('prevents staff from viewing or charging appointments assigned to someone else', function () {
    $service = systemService();
    $staff = systemUser('staff');
    $assignedStaff = systemUser('staff');
    $appointment = Appointment::create([
        'full_name' => 'Private Client',
        'contact_number' => '09175550000',
        'service_id' => $service->id,
        'date' => now()->toDateString(),
        'time' => '14:30',
        'status' => 'completed',
    ]);
    $appointment->assignedStaffMembers()->attach($assignedStaff->id);

    $this->actingAs($staff)
        ->get(route('appointments.index'))
        ->assertOk()
        ->assertDontSee('Private Client');

    $this->actingAs($staff)
        ->postJson(route('appointments.payment.store', $appointment->id), ['method' => 'cash'])
        ->assertForbidden();
});

it('does not allow inactive services to be booked', function () {
    $service = systemService(['is_active' => false]);

    $this->post(route('appointments.store'), [
        'full_name' => 'Inactive Service Client',
        'contact_number' => '09176660000',
        'service_id' => $service->id,
        'date' => now()->addDay()->toDateString(),
        'time' => '09:00',
    ])->assertSessionHasErrors('service_id');
});

it('allows reception to create a walk-in appointment without staff assignment', function () {
    $service = systemService(['name' => 'Foot Reflexology']);
    $reception = systemUser('reception');

    $this->actingAs($reception)
        ->postJson(route('appointments.walkin.store'), [
            'full_name' => 'Walk In Client',
            'contact_number' => '09181234567',
            'email' => null,
            'service_id' => $service->id,
            'date' => now()->toDateString(),
            'time' => '10:30',
            'assigned_staff_ids' => [],
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $appointment = Appointment::where('contact_number', '09181234567')->first();

    expect($appointment->status)
        ->toBe('confirmed')
        ->and($appointment->booking_type)->toBe('walk-in')
        ->and($appointment->assignedStaffMembers()->count())->toBe(0);
});

it('processes payment and displays receipt for a completed appointment', function () {
    $service = systemService(['price' => 1200]);
    $staff = systemUser('staff');

    $appointment = Appointment::create([
        'full_name' => 'Paid Client',
        'contact_number' => '09191234567',
        'email' => 'paid@example.com',
        'service_id' => $service->id,
        'date' => now()->toDateString(),
        'time' => '13:00',
        'status' => 'completed',
        'booking_type' => 'walk-in',
    ]);
    $appointment->assignedStaffMembers()->attach($staff->id);

    $this->actingAs(systemUser('reception'))
        ->postJson(route('appointments.payment.store', $appointment->id), [
            'method' => 'cash',
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $invoice = Invoice::where('appointment_id', $appointment->id)->first();

    expect($invoice)
        ->not->toBeNull()
        ->and((float) $invoice->grand_total)->toBe(1200.0)
        ->and($appointment->fresh()->payment_status)->toBe('paid');

    $this->actingAs(systemUser('management'))
        ->get(route('invoices.receipt', $invoice->id))
        ->assertOk()
        ->assertSee('Paid Client');
});

it('allows management to view reports and customer service history', function () {
    $service = systemService();
    $management = systemUser('management');

    Appointment::create([
        'full_name' => 'Report Client',
        'contact_number' => '09201234567',
        'email' => 'report@example.com',
        'service_id' => $service->id,
        'date' => now()->toDateString(),
        'time' => '15:00',
        'status' => 'completed',
        'booking_type' => 'online',
    ]);

    $this->actingAs($management)
        ->get(route('reports.index'))
        ->assertOk();

    $this->actingAs($management)
        ->get(route('reports.customer-services'))
        ->assertOk()
        ->assertSee('Report Client');
});
