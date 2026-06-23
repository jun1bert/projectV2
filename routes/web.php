<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CommissionRuleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AppointmentPaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome', [
        'services' => \App\Models\Service::all()
    ]);
});

/*
|--------------------------------------------------------------------------
| AUTH DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [AppointmentController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| APPOINTMENTS (CUSTOMER)
|--------------------------------------------------------------------------
*/
Route::post('/appointments', [AppointmentController::class, 'store'])
    ->name('appointments.store');

/*
|--------------------------------------------------------------------------
| APPOINTMENTS VIEW (ADMIN / STAFF / MANAGEMENT)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,staff,management'])->group(function () {

    Route::get('/appointments', [AppointmentController::class, 'index'])
        ->name('appointments.index');

    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| APPOINTMENT MANAGEMENT (ADMIN / MANAGEMENT ONLY)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,management'])->group(function () {

    Route::post('/appointments/{id}/status', [AppointmentController::class, 'updateStatus'])
        ->name('appointments.updateStatus');
});

/*
|--------------------------------------------------------------------------
| STAFF MODULE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,management'])->group(function () {

    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::put('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
});

/*
|--------------------------------------------------------------------------
| SERVICES MODULE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,management'])->group(function () {

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');
});

/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| WALK-IN APPOINTMENT
|--------------------------------------------------------------------------
*/
Route::post('/appointments/walk-in', [AppointmentController::class, 'storeWalkIn'])
    ->middleware(['auth', 'role:admin,management,staff'])
    ->name('appointments.walkin.store');

/*
|--------------------------------------------------------------------------
| COMMISSION SYSTEM (FIXED - SINGLE SOURCE OF TRUTH)
|--------------------------------------------------------------------------
| Restricted to admin/management — this exposes staff pay data and lets
| someone mark commissions as paid, so plain 'auth' alone isn't enough.
*/
Route::middleware(['auth', 'role:admin,management'])->group(function () {

    Route::get('/commissions', [CommissionController::class, 'index']);

    Route::get('/commissions/{staffId}/details', [CommissionController::class, 'details']);

    Route::post('/commissions/{id}/mark-paid', [CommissionController::class, 'markPaid']);
});

/*
|--------------------------------------------------------------------------
| COMMISSION RULES
|--------------------------------------------------------------------------
| Business configuration — admin/management only.
*/
Route::middleware(['auth', 'role:admin,management'])->group(function () {

    Route::get('/commission-rules', [CommissionRuleController::class, 'index']);
    Route::post('/commission-rules', [CommissionRuleController::class, 'store']);
    Route::post('/commission-rules/{id}/toggle', [CommissionRuleController::class, 'toggle']);
    Route::delete('/commission-rules/{id}', [CommissionRuleController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| INVENTORY MODULE
|--------------------------------------------------------------------------
| Staff need read/search access for the payment item-picker, but only
| admin/management should be able to create, edit, or delete products.
*/
Route::middleware(['auth', 'role:admin,management,staff'])->group(function () {

    Route::get('/inventory', [ProductController::class, 'index']);
    Route::get('/inventory/search', [ProductController::class, 'search']);
    Route::get('/inventory/{id}', [ProductController::class, 'show']);
});

Route::middleware(['auth', 'role:admin,management'])->group(function () {

    Route::post('/inventory', [ProductController::class, 'store']);
    Route::post('/inventory/stock-in', [ProductController::class, 'stockIn']);
    Route::put('/inventory/{id}', [ProductController::class, 'update']);
    Route::delete('/inventory/{id}', [ProductController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| APPOINTMENT PAYMENTS (ADMIN / MANAGEMENT / STAFF ONLY)
|--------------------------------------------------------------------------
*/
Route::post('/appointments/{id}/payment', [AppointmentPaymentController::class, 'store'])
    ->middleware(['auth', 'role:admin,management,staff']);

/*
|--------------------------------------------------------------------------
| RECEIPT (PRINTABLE)
|--------------------------------------------------------------------------
*/
Route::get('/invoices/{id}/receipt', [InvoiceController::class, 'receipt'])
    ->middleware(['auth', 'role:admin,management,staff'])
    ->name('invoices.receipt');

Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');
});


Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
require __DIR__.'/auth.php';