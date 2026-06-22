<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt #{{ $invoice->id }}</title>
<style>
    /* ===================== THERMAL PRINTER SETUP =====================
       Default target: 80mm thermal paper (most common for POS printers).
       If your printer uses 58mm paper instead, change:
         - @page size below to "58mm auto"
         - body width below to 56mm
    */
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
        font-family: 'Courier New', Courier, monospace;
        font-size: 12px;
        line-height: 1.4;
        color: #000;
        width: 76mm;       /* 80mm paper minus small printer margins */
        margin: 0 auto;
        padding: 4mm 2mm;
    }

    .center { text-align: center; }

    .shop-name {
        font-size: 15px;
        font-weight: bold;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
        text-transform: uppercase;
    }

    .muted { font-size: 10px; }

    hr {
        border: none;
        border-top: 1px dashed #000;
        margin: 6px 0;
    }

    .row {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        padding: 1px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        table-layout: fixed;
    }

    td {
        padding: 2px 0;
        vertical-align: top;
        word-break: break-word;
    }

    .col-item  { width: 46%; }
    .col-qty   { width: 12%; text-align: right; }
    .col-price { width: 21%; text-align: right; }
    .col-sub   { width: 21%; text-align: right; }

    thead td {
        font-weight: bold;
        border-bottom: 1px dashed #000;
    }

    .total-row {
        font-weight: bold;
        font-size: 13px;
    }

    .footer-msg {
        margin-top: 6px;
        font-size: 11px;
    }

    /* On-screen-only controls — never printed */
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
        background: #2563eb;
        color: #fff;
    }

    .actions a {
        display: inline-block;
        margin-top: 8px;
        font-size: 12px;
        color: #555;
        text-decoration: none;
    }

    /* Give the receipt some visual breathing room on screen only,
       since on real thermal paper there's no need for it */
    @media screen {
        body {
            width: 302px; /* ~80mm at 96dpi, just for on-screen preview */
            border: 1px solid #ddd;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 16px 12px;
        }
    }

    @media print {
        .actions { display: none; }
        body {
            width: 76mm;
            border: none;
            box-shadow: none;
        }
    }
</style>
</head>
<body>

    <div class="center">
        <div class="shop-name">{{ config('app.name', 'Receipt') }}</div>
        <div class="muted">Official Receipt</div>
    </div>

    <hr>

    <div class="row"><span>Receipt #</span><span>{{ $invoice->id }}</span></div>
    <div class="row"><span>Date</span><span>{{ $invoice->created_at->format('M d, Y h:i A') }}</span></div>
    @if($invoice->appointment)
    <div class="row"><span>Customer</span><span>{{ $invoice->appointment->full_name }}</span></div>
    <div class="row"><span>Contact</span><span>{{ $invoice->appointment->contact_number }}</span></div>
    @endif
    <div class="row"><span>Payment</span><span>{{ ucfirst($invoice->payment_method) }}</span></div>

    <hr>

    <table>
        <thead>
            <tr>
                <td class="col-item">Item</td>
                <td class="col-qty">Qty</td>
                <td class="col-price">Price</td>
                <td class="col-sub">Sub</td>
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

    <hr>

    <div class="row"><span>Service Total</span><span>₱{{ number_format($invoice->service_total, 2) }}</span></div>
    <div class="row"><span>Items Total</span><span>₱{{ number_format($invoice->items_total, 2) }}</span></div>
    <div class="row total-row"><span>GRAND TOTAL</span><span>₱{{ number_format($invoice->grand_total, 2) }}</span></div>

    @if($invoice->notes)
    <hr>
    <div class="muted">Notes: {{ $invoice->notes }}</div>
    @endif

    <hr>

    <div class="center footer-msg">Thank you for your visit!</div>
    <div class="center muted">Please come again</div>

    <div class="actions">
        <button onclick="window.print()">🖨 Print Receipt</button>
        <br>
        <a href="{{ url('/appointments') }}">← Back to Appointments</a>
    </div>

</body>
</html>