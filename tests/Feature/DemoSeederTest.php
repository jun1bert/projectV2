<?php

use App\Models\Appointment;
use App\Models\Client;
use App\Models\ConsentForm;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('creates repeatable demo data with realistic client identities', function () {
    Storage::fake('local');
    $this->seed(DemoSeeder::class);
    $this->seed(DemoSeeder::class);

    expect(Appointment::count())->toBe(8)
        ->and(Client::count())->toBe(7)
        ->and(Invoice::count())->toBe(2)
        ->and(ConsentForm::count())->toBe(1)
        ->and(User::where('role', 'staff')->count())->toBe(2)
        ->and(Client::where('full_name', 'Maria Santos')->count())->toBe(2)
        ->and(Client::where('contact_number', '09171234567')->first()->appointments()->count())->toBe(2);

    Storage::disk('local')->assertExists('consents/demo-signature.svg');
});
