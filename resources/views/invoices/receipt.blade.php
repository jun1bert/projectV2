<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt #{{ $invoice->id }}</title>

<style>
@page {
    size: 80mm auto;
    margin: 0;
}

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;
}

body {
    width: 76mm;
    margin: 0 auto;
    padding: 4mm 2mm;
    background: #fff;
    color: #4d4037;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    line-height: 1.45;
}

.receipt {
    width: 100%;
}

.center {
    text-align: center;
}

.logo {
    width: 62mm;
    max-width: 100%;
    margin-bottom: 6px;
}

.receipt-title {
    margin-bottom: 2px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.muted {
    color: #817267;
    font-size: 10px;
}

.divider {
    margin: 7px 0;
    border: none;
    border-top: 1px dashed #a48d78;
}

.section-title {
    margin-bottom: 4px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
}

.row {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    padding: 2px 0;
}

.row span:first-child {
    color: #817267;
}

.row span:last-child {
    font-weight: 600;
    text-align: right;
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 10.5px;
}

td {
    padding: 3px 0;
    vertical-align: top;
    word-break: break-word;
}

thead td {
    border-bottom: 1px solid #a48d78;
    padding-bottom: 4px;
    font-weight: 700;
}

.col-item { width: 44%; }
.col-qty { width: 12%; text-align: right; }
.col-price { width: 22%; text-align: right; }
.col-sub { width: 22%; text-align: right; }

.total-box {
    margin-top: 4px;
}

.total-row {
    margin-top: 4px;
    border-top: 1px solid #a48d78;
    padding-top: 5px;
    font-size: 13px;
    font-weight: 800;
}

.notes {
    color: #4d4037;
    font-size: 10px;
}

.footer-msg {
    margin-top: 8px;
    font-size: 11px;
    font-weight: 600;
}

.actions {
    margin-top: 16px;
    text-align: center;
}

.actions button {
    border: none;
    border-radius: 8px;
    background: #a48d78;
    color: #fff;
    cursor: pointer;
    font-size: 13px;
    padding: 10px 18px;
}

@media screen {
    body {
        width: 302px;
        border: 1px solid #e6dac8;
        box-shadow: 0 4px 14px rgba(77, 64, 55, .12);
        padding: 16px 12px;
    }
}

@media print {
    .actions {
        display: none;
    }

    body {
        width: 76mm;
        border: none;
        box-shadow: none;
        padding: 4mm 2mm;
    }
}
</style>
</head>

<body>
<div class="receipt">
    <div class="center">
        <img src="{{ asset('images/martinis-logo.png') }}" class="logo" alt="Martinis and Manicures Logo">
        <div class="receipt-title">Official Receipt</div>
        <div class="muted">Thank you for choosing us</div>
    </div>

    <hr class="divider">

    <div class="section-title">Transaction Details</div>

    <div class="row">
        <span>Receipt No.</span>
        <span>#{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</span>
    </div>

    <div class="row">
        <span>Date</span>
        <span>{{ $invoice->created_at->format('M d, Y h:i A') }}</span>
    </div>

    <div class="row">
        <span>Payment</span>
        <span>{{ ucfirst($invoice->payment_method) }}</span>
    </div>

    @if($invoice->appointment)
    <hr class="divider">

    <div class="section-title">Customer Details</div>

    <div class="row">
        <span>Name</span>
        <span>{{ $invoice->appointment->full_name }}</span>
    </div>

    <div class="row">
        <span>Contact</span>
        <span>{{ $invoice->appointment->contact_number }}</span>
    </div>

    @if($invoice->appointment->email)
    <div class="row">
        <span>Email</span>
        <span>{{ $invoice->appointment->email }}</span>
    </div>
    @endif
    @endif

    <hr class="divider">

    <div class="section-title">Service</div>

    <div class="row">
        <span>{{ $invoice->appointment->service->name ?? 'Service' }}</span>
        <span>PHP {{ number_format($invoice->service_total, 2) }}</span>
    </div>

    <hr class="divider">

    <div class="total-box">
        <div class="row">
            <span>Subtotal</span>
            <span>PHP {{ number_format($invoice->service_total, 2) }}</span>
        </div>

        <div class="row total-row">
            <span>Grand Total</span>
            <span>PHP {{ number_format($invoice->grand_total, 2) }}</span>
        </div>
    </div>

    @if($invoice->notes)
    <hr class="divider">
    <div class="notes">
        <strong>Notes:</strong> {{ $invoice->notes }}
    </div>
    @endif

    <hr class="divider">

    <div class="center footer-msg">Thank you for your visit!</div>
    <div class="center muted">Please come again</div>

    <div class="actions">
        <button type="button" onclick="window.print()">Print Receipt</button>
    </div>
</div>
</body>
</html>
