<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function receipt($id)
    {
        $invoice = Invoice::with(['appointment.service', 'appointment.servicePackage', 'payments.receiver'])->findOrFail($id);

        // Staff can only view receipts for appointments they're assigned to;
        // admin, management, and reception can view any.
        $user = Auth::user();
        $isStaffOwner = $user->role === 'staff'
            && $invoice->appointment
            && ($invoice->appointment->assignedStaffMembers()->where('users.id', $user->id)->exists()
                || ($invoice->appointment->service_package_id
                    && $invoice->appointment->servicePackage->appointments()
                        ->whereHas('assignedStaffMembers', fn ($query) => $query->where('users.id', $user->id))
                        ->exists()));

        if (! in_array($user->role, ['admin', 'management', 'reception']) && ! $isStaffOwner) {
            abort(403);
        }

        return view('invoices.receipt', compact('invoice'));
    }
}
