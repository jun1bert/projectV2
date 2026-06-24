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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\GalleryController;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/', [WelcomeController::class, 'index'])
    ->name('welcome');

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

    Route::get('/appointments/{id}', [AppointmentController::class, 'show'])
        ->name('appointments.show');
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
| WALK-IN APPOINTMENT
|--------------------------------------------------------------------------
*/
Route::post('/appointments/walk-in', [AppointmentController::class, 'storeWalkIn'])
    ->middleware(['auth', 'role:admin,management,staff'])
    ->name('appointments.walkin.store');

/*
|--------------------------------------------------------------------------
| STAFF MODULE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,management'])->group(function () {

    Route::get('/staff', [StaffController::class, 'index'])
        ->name('staff.index');

    Route::post('/staff', [StaffController::class, 'store'])
        ->name('staff.store');

    Route::put('/staff/{id}', [StaffController::class, 'update'])
        ->name('staff.update');

    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])
        ->name('staff.destroy');
});

/*
|--------------------------------------------------------------------------
| SERVICES MODULE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,management'])->group(function () {

    Route::get('/services', [ServiceController::class, 'index'])
        ->name('services.index');

    Route::post('/services', [ServiceController::class, 'store'])
        ->name('services.store');

    Route::put('/services/{id}', [ServiceController::class, 'update'])
        ->name('services.update');

    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])
        ->name('services.destroy');
});

/*
|--------------------------------------------------------------------------
| COMMISSION SYSTEM
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,management'])->group(function () {

    Route::get('/commissions', [CommissionController::class, 'index'])
        ->name('commissions.index');

    Route::get('/commissions/{staffId}/details', [CommissionController::class, 'details'])
        ->name('commissions.details');

    Route::post('/commissions/{id}/mark-paid', [CommissionController::class, 'markPaid'])
        ->name('commissions.markPaid');
});

/*
|--------------------------------------------------------------------------
| COMMISSION RULES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,management'])->group(function () {

    Route::get('/commission-rules', [CommissionRuleController::class, 'index'])
        ->name('commission-rules.index');

    Route::post('/commission-rules', [CommissionRuleController::class, 'store'])
        ->name('commission-rules.store');

    Route::post('/commission-rules/{id}/toggle', [CommissionRuleController::class, 'toggle'])
        ->name('commission-rules.toggle');

    Route::delete('/commission-rules/{id}', [CommissionRuleController::class, 'destroy'])
        ->name('commission-rules.destroy');
});

/*
|--------------------------------------------------------------------------
| INVENTORY MODULE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,management,staff'])->group(function () {

    Route::get('/inventory', [ProductController::class, 'index'])
        ->name('inventory.index');

    Route::get('/inventory/search', [ProductController::class, 'search'])
        ->name('inventory.search');

    Route::get('/inventory/{id}', [ProductController::class, 'show'])
        ->name('inventory.show');
});

Route::middleware(['auth', 'role:admin,management'])->group(function () {

    Route::post('/inventory', [ProductController::class, 'store'])
        ->name('inventory.store');

    Route::post('/inventory/stock-in', [ProductController::class, 'stockIn'])
        ->name('inventory.stockIn');

    Route::put('/inventory/{id}', [ProductController::class, 'update'])
        ->name('inventory.update');

    Route::delete('/inventory/{id}', [ProductController::class, 'destroy'])
        ->name('inventory.destroy');
});

/*
|--------------------------------------------------------------------------
| APPOINTMENT PAYMENTS
|--------------------------------------------------------------------------
*/
Route::post('/appointments/{id}/payment', [AppointmentPaymentController::class, 'store'])
    ->middleware(['auth', 'role:admin,management,staff'])
    ->name('appointments.payment.store');

/*
|--------------------------------------------------------------------------
| RECEIPT
|--------------------------------------------------------------------------
*/
Route::get('/invoices/{id}/receipt', [InvoiceController::class, 'receipt'])
    ->middleware(['auth', 'role:admin,management,staff'])
    ->name('invoices.receipt');

/*
|--------------------------------------------------------------------------
| REPORTS
|--------------------------------------------------------------------------
| Admin / Management Only
*/
Route::middleware(['auth', 'role:admin,management'])->group(function () {

    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/reports/download', [ReportController::class, 'downloadReport'])
        ->name('reports.download');
});

/*
|--------------------------------------------------------------------------
| GALLERY MODULE
|--------------------------------------------------------------------------
*/
Route::prefix('gallery')
    ->name('gallery.')
    ->middleware(['auth', 'role:admin,management'])
    ->group(function () {

        Route::get('/', [GalleryController::class, 'index'])
            ->name('index');

        Route::post('/', [GalleryController::class, 'store'])
            ->name('store');

        Route::patch('/{image}/toggle-publish', [GalleryController::class, 'togglePublish'])
            ->name('togglePublish');

        Route::delete('/{image}', [GalleryController::class, 'destroy'])
            ->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
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

require __DIR__.'/auth.php';