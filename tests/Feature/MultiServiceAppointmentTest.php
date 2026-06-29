<?php

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('books two services in one appointment and snapshots their combined price', function () {
    $services = collect([
        Service::create(['name' => 'Manicure', 'category' => 'Nails', 'price' => 450, 'is_active' => true]),
        Service::create(['name' => 'Pedicure', 'category' => 'Nails', 'price' => 650, 'is_active' => true]),
    ]);

    $this->post(route('appointments.store'), [
        'full_name' => 'Multiple Service Client',
        'contact_number' => '09171234567',
        'service_ids' => $services->pluck('id')->all(),
        'date' => now()->addDay()->toDateString(),
        'time' => '09:00',
    ])->assertRedirect('/#book');

    $appointment = Appointment::with('services')->firstOrFail();

    expect($appointment->services)->toHaveCount(2)
        ->and($appointment->services_total)->toBe(1100.0)
        ->and((float) $appointment->price_at_booking)->toBe(1100.0);
});

it('books a group and accepts payment for selected clients', function () {
    $service = Service::create([
        'name' => 'Group Spa Service',
        'category' => 'Spa',
        'price' => 500,
        'is_active' => true,
    ]);

    $this->post(route('appointments.store'), [
        'full_name' => 'Group Client',
        'contact_number' => '09171234568',
        'service_ids' => [$service->id],
        'party_size' => 3,
        'date' => now()->addDay()->toDateString(),
        'time' => '09:30',
    ])->assertRedirect('/#book');

    $appointment = Appointment::firstOrFail();
    $appointment->update(['status' => 'completed']);
    $reception = User::factory()->create(['role' => 'reception']);

    $this->actingAs($reception)->postJson(route('appointments.payment.store', $appointment), [
        'method' => 'cash',
        'payment_type' => 'per_client',
        'client_count' => 2,
    ])->assertOk();

    $appointment->refresh();
    expect($appointment->party_size)->toBe(3)
        ->and($appointment->billing_invoice->grand_total)->toEqual('1500.00')
        ->and($appointment->billing_invoice->amount_paid)->toEqual('1000.00')
        ->and($appointment->billing_invoice->payments->first()->client_count)->toBe(2);
});

it('assigns different services to each client and charges the selected client total', function () {
    $serviceOne = Service::create(['name' => 'Manicure', 'category' => 'Nails', 'price' => 400, 'is_active' => true]);
    $serviceTwo = Service::create(['name' => 'Pedicure', 'category' => 'Nails', 'price' => 600, 'is_active' => true]);
    $reception = User::factory()->create(['role' => 'reception']);

    $this->post(route('appointments.store'), [
        'full_name' => 'Primary Client',
        'contact_number' => '09171234569',
        'participants' => [
            ['name' => 'Primary Client', 'service_ids' => [$serviceOne->id, $serviceTwo->id]],
            ['name' => 'Second Client', 'service_ids' => [$serviceTwo->id]],
        ],
        'date' => now()->addDay()->toDateString(),
        'time' => '10:00',
    ])->assertRedirect('/#book');

    $appointment = Appointment::with('participants.services')->firstOrFail();
    expect($appointment->price_at_booking)->toEqual('1600.00')
        ->and($appointment->participants[0]->total)->toBe(1000.0)
        ->and($appointment->participants[1]->total)->toBe(600.0);

    $appointment->update(['status' => 'completed']);
    $this->actingAs($reception)->postJson(route('appointments.payment.store', $appointment), [
        'method' => 'cash',
        'payment_type' => 'per_client',
        'client_count' => 1,
        'participant_ids' => [$appointment->participants[1]->id],
    ])->assertOk();

    expect((float) $appointment->fresh()->billing_invoice->amount_paid)->toBe(600.0);
});

it('defers required consent until the client arrives at the store', function () {
    $service = Service::create(['name' => 'Consent Service', 'category' => 'Spa', 'price' => 800, 'is_active' => true, 'requires_consent' => true]);

    $this->post(route('appointments.store'), [
        'full_name' => 'Arriving Client', 'contact_number' => '09171234560',
        'service_ids' => [$service->id], 'date' => now()->addDay()->toDateString(), 'time' => '11:00',
    ])->assertRedirect('/#book');

    $appointment = Appointment::firstOrFail();
    expect($appointment->consentForm)->toBeNull();

    $reception = User::factory()->create(['role' => 'reception']);
    $this->actingAs($reception)->postJson(route('appointments.consent.store', $appointment), [
        'consent_accepted' => true,
        'consent_signature' => 'data:image/png;base64,'.base64_encode('signature'),
    ])->assertOk();

    expect($appointment->fresh()->consentForm)->not->toBeNull();
});
