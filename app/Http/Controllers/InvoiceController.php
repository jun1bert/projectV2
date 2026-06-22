<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function receipt($id)
    {
        $invoice = Invoice::with(['items', 'appointment.service'])->findOrFail($id);

        // Staff can only view receipts for appointments they're assigned to;
        // admin/management can view any. Adjust if your access rules differ.
        $user = Auth::user();
        $isStaffOwner = $user->role === 'staff'
            && $invoice->appointment
            && $invoice->appointment->assigned_to === $user->id;

        if (!in_array($user->role, ['admin', 'management']) && !$isStaffOwner) {
            abort(403);
        }

        return view('invoices.receipt', compact('invoice'));
    }
}