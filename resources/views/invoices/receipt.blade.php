<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt #{{ $invoice->id }}</title>

<style>
@page {
    size: 80mm auto;
    margin: 0;
}

* {
    box-sizing: border-box;
}

html, body {
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    line-height: 1.45;
    color: #111;
    width: 76mm;
    margin: 0 auto;
    padding: 4mm 2mm;
    background: #fff;
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
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 2px;
}

.muted {
    font-size: 10px;
    color: #444;
}

.divider {
    border: none;
    border-top: 1px dashed #111;
    margin: 7px 0;
}

.row {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    padding: 2px 0;
    font-size: 11px;
}

.row span:first-child {
    color: #444;
}

.row span:last-child {
    font-weight: 600;
    text-align: right;
}

.section-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    margin-bottom: 4px;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10.5px;
    table-layout: fixed;
}

td {
    padding: 3px 0;
    vertical-align: top;
    word-break: break-word;
}

thead td {
    font-weight: 700;
    border-bottom: 1px solid #111;
    padding-bottom: 4px;
}

.col-item  { width: 44%; }
.col-qty   { width: 12%; text-align: right; }
.col-price { width: 22%; text-align: right; }
.col-sub   { width: 22%; text-align: right; }

.total-box {
    margin-top: 4px;
}

.total-row {
    font-size: 13px;
    font-weight: 800;
    border-top: 1px solid #111;
    padding-top: 5px;
    margin-top: 4px;
}

.notes {
    font-size: 10px;
    color: #333;
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
    padding: 10px 18px;
    font-size: 13px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    background: #111;
    color: #fff;
}

.actions a {
    display: inline-block;
    margin-top: 8px;
    font-size: 12px;
    color: #555;
    text-decoration: none;
}

@media screen {
    body {
        width: 302px;
        border: 1px solid #ddd;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
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
        <img src="{{ asset('images/martinis-logo.png') }}" class="logo" alt="Martinis & Manicures Logo">

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
    @endif

    <hr class="divider">

    <div class="section-title">Items / Services</div>

    <table>
        <thead>
            <tr>
                <td class="col-item">Item</td>
                <td class="col-qty">Qty</td>
                <td class="col-price">Price</td>
                <td class="col-sub">Total</td>
            </tr>
        </thead>

        <tbody>
        @foreach($invoice->items as $item)
            <tr>
                <td class="col-item">{{ $item->name }}</td>
                <td class="col-qty">{{ $item->qty }}</td>
                <td class="col-price">{{ number_format($item->price, 2) }}</td>
                <td class="col-sub">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <hr class="divider">

    <div class="total-box">
        <div class="row">
            <span>Service Total</span>
            <span>₱{{ number_format($invoice->service_total, 2) }}</span>
        </div>

        <div class="row">
            <span>Items Total</span>
            <span>₱{{ number_format($invoice->items_total, 2) }}</span>
        </div>

        <div class="row total-row">
            <span>Grand Total</span>
            <span>₱{{ number_format($invoice->grand_total, 2) }}</span>
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
        <button onclick="window.print()">🖨 Print Receipt</button>
    </div>

</div>
</body>
</html>