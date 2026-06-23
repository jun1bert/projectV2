<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report — {{ $from->format('M d') }} to {{ $to->format('M d, Y') }}</title>
    <style>
        /* ── RESET & BASE ───────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #fff;
            line-height: 1.5;
        }

        /* ── PRINT ──────────────────────────────────────────────────── */
        @media print {
            @page { size: A4; margin: 15mm 12mm; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            .avoid-break { page-break-inside: avoid; }
            body { font-size: 10px; }
        }

        @media screen {
            body { background: #f4f1ea; padding: 24px; }
            .page {
                background: #fff;
                max-width: 800px;
                margin: 0 auto 24px;
                padding: 32px 36px;
                border-radius: 12px;
                box-shadow: 0 2px 16px rgba(0,0,0,.08);
            }
        }

        /* ── TYPOGRAPHY ─────────────────────────────────────────────── */
        h1 { font-size: 20px; font-weight: 700; color: #3a2a22; letter-spacing: -.3px; }
        h2 { font-size: 13px; font-weight: 700; color: #3a2a22; margin-bottom: 10px; }
        h3 { font-size: 11px; font-weight: 600; color: #3a2a22; }

        /* ── LAYOUT HELPERS ─────────────────────────────────────────── */
        .row    { display: flex; gap: 16px; }
        .col    { flex: 1; min-width: 0; }
        .spacer { height: 20px; }

        /* ── HEADER ─────────────────────────────────────────────────── */
        .report-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding-bottom: 16px;
            border-bottom: 2px solid #c8a96a;
            margin-bottom: 20px;
        }
        .report-header .meta { text-align: right; }
        .report-header .meta p { font-size: 10px; color: #888; line-height: 1.8; }
        .report-header .meta strong { color: #3a2a22; }
        .badge-range {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 8px;
            background: #c8a96a;
            color: #fff;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ── KPI GRID ────────────────────────────────────────────────── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .kpi-card {
            border: 1px solid #e8e0d5;
            border-radius: 8px;
            padding: 10px 12px;
            background: #faf7f2;
        }
        .kpi-card .label {
            font-size: 9px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 4px;
        }
        .kpi-card .value {
            font-size: 18px;
            font-weight: 700;
            color: #3a2a22;
            line-height: 1;
        }
        .kpi-card .value.gold   { color: #c8a96a; }
        .kpi-card .value.green  { color: #16a34a; }
        .kpi-card .value.blue   { color: #2563eb; }

        /* ── SECTION CARD ─────────────────────────────────────────────── */
        .section {
            border: 1px solid #e8e0d5;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .section-header {
            background: #3a2a22;
            color: #fff;
            padding: 7px 12px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
        }
        .section-body { padding: 12px; }

        /* ── STATUS BARS ─────────────────────────────────────────────── */
        .status-row { margin-bottom: 8px; }
        .status-row:last-child { margin-bottom: 0; }
        .status-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3px;
        }
        .status-label-row .name {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 10px;
            color: #555;
        }
        .dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }
        .dot-amber  { background: #f59e0b; }
        .dot-green  { background: #16a34a; }
        .dot-red    { background: #ef4444; }
        .dot-blue   { background: #3b82f6; }
        .dot-gray   { background: #9ca3af; }
        .status-count { font-weight: 700; font-size: 10px; color: #3a2a22; }
        .bar-track { height: 5px; background: #ede9e3; border-radius: 10px; overflow: hidden; }
        .bar-fill  { height: 100%; border-radius: 10px; }

        /* ── TABLES ──────────────────────────────────────────────────── */
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        thead tr { background: #3a2a22; color: #fff; }
        thead th { padding: 6px 10px; text-align: left; font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
        thead th.right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f0ece6; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:nth-child(even) { background: #faf7f2; }
        tbody td { padding: 5px 10px; color: #333; vertical-align: top; }
        tbody td.right { text-align: right; }
        tfoot tr { border-top: 2px solid #c8a96a; background: #faf7f2; }
        tfoot td { padding: 6px 10px; font-weight: 700; font-size: 10px; color: #3a2a22; }
        tfoot td.right { text-align: right; }

        /* ── PILLS / BADGES ──────────────────────────────────────────── */
        .pill {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 600;
            border: 1px solid transparent;
        }
        .pill-pending   { background: #fffbeb; color: #b45309; border-color: #fcd34d; }
        .pill-confirmed { background: #f0fdf4; color: #16a34a; border-color: #86efac; }
        .pill-completed { background: #fdf7ee; color: #8a6a30; border-color: #c8a96a; }
        .pill-rejected  { background: #fef2f2; color: #b91c1c; border-color: #fca5a5; }
        .pill-cancelled { background: #f9fafb; color: #6b7280; border-color: #d1d5db; }
        .pill-paid      { background: #ecfdf5; color: #065f46; border-color: #6ee7b7; }

        /* ── PRINT TOOLBAR ───────────────────────────────────────────── */
        .print-toolbar {
            position: fixed;
            bottom: 24px;
            right: 24px;
            display: flex;
            gap: 8px;
            z-index: 100;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all .15s;
            text-decoration: none;
        }
        .btn-gold {
            background: #c8a96a;
            color: #fff;
        }
        .btn-gold:hover { background: #b8955a; }
        .btn-outline {
            background: #fff;
            color: #3a2a22;
            border: 1px solid #d1c5b4;
        }
        .btn-outline:hover { background: #faf7f2; }

        /* ── FOOTER ──────────────────────────────────────────────────── */
        .report-footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #e8e0d5;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #aaa;
        }
    </style>
</head>
<body>

{{-- PRINT TOOLBAR (screen only) --}}
<div class="print-toolbar no-print">
    <a href="{{ url()->previous() }}" class="btn btn-outline">
        ← Back
    </a>
    <button onclick="window.print()" class="btn btn-gold">
        🖨 Print / Save PDF
    </button>
</div>

<div class="page">

    {{-- ── HEADER ────────────────────────────────────────────────────── --}}
    <div class="report-header">
        <div>
            <h1>Business Report</h1>
            <p style="color:#888;font-size:10px;margin-top:3px;">Detailed performance overview</p>
        </div>
        <div class="meta">
            <p><strong>Period:</strong> {{ $from->format('M d, Y') }} — {{ $to->format('M d, Y') }}</p>
            <p><strong>Generated:</strong> {{ now()->format('M d, Y h:i A') }}</p>
            <span class="badge-range">{{ ucfirst(str_replace('_', ' ', $range)) }}</span>
        </div>
    </div>

    {{-- ── KPI CARDS ──────────────────────────────────────────────────── --}}
    <div class="kpi-grid avoid-break">
        <div class="kpi-card">
            <div class="label">Total Appointments</div>
            <div class="value">{{ $totalAppointments }}</div>
        </div>
        <div class="kpi-card">
            <div class="label">Completed</div>
            <div class="value green">{{ $completed }}</div>
        </div>
        <div class="kpi-card">
            <div class="label">Revenue</div>
            <div class="value gold">₱{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="label">Commissions</div>
            <div class="value blue">₱{{ number_format($totalCommission, 2) }}</div>
        </div>
    </div>

    {{-- ── TWO-COLUMN: STATUS + TOP SERVICES ─────────────────────────── --}}
    <div class="row avoid-break">

        {{-- STATUS BREAKDOWN --}}
        <div class="col">
            <div class="section">
                <div class="section-header">Appointment Status Breakdown</div>
                <div class="section-body">
                    @php
                        $statuses = [
                            ['label'=>'Pending',   'count'=>$pending,   'dot'=>'dot-amber', 'bar'=>'#f59e0b'],
                            ['label'=>'Confirmed', 'count'=>$confirmed, 'dot'=>'dot-blue',  'bar'=>'#3b82f6'],
                            ['label'=>'Completed', 'count'=>$completed, 'dot'=>'dot-green', 'bar'=>'#16a34a'],
                            ['label'=>'Cancelled', 'count'=>$cancelled, 'dot'=>'dot-gray',  'bar'=>'#9ca3af'],
                            ['label'=>'Rejected',  'count'=>$rejected,  'dot'=>'dot-red',   'bar'=>'#ef4444'],
                        ];
                    @endphp
                    @foreach($statuses as $s)
                    @php $pct = $totalAppointments > 0 ? round($s['count']/$totalAppointments*100) : 0; @endphp
                    <div class="status-row">
                        <div class="status-label-row">
                            <span class="name">
                                <span class="dot {{ $s['dot'] }}"></span>
                                {{ $s['label'] }}
                            </span>
                            <span class="status-count">{{ $s['count'] }} <span style="color:#aaa;font-weight:400;">({{ $pct }}%)</span></span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:{{ $pct }}%;background:{{ $s['bar'] }};"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- TOP SERVICES --}}
        <div class="col">
            <div class="section">
                <div class="section-header">Top Services</div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th class="right">Bookings</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($topServices as $i => $item)
                        <tr>
                            <td style="color:#c8a96a;font-weight:700;">{{ $i+1 }}</td>
                            <td>{{ $item->service->name ?? '—' }}</td>
                            <td class="right"><strong>{{ $item->total }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align:center;color:#aaa;padding:12px;">No data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ── STAFF COMMISSION BREAKDOWN ──────────────────────────────────── --}}
    <div class="section avoid-break">
        <div class="section-header">Staff Commission Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th class="right">Appointments</th>
                    <th class="right">Service Value</th>
                    <th class="right">Pending</th>
                    <th class="right">Paid</th>
                    <th class="right">Total Commission</th>
                </tr>
            </thead>
            <tbody>
            @forelse($staffCommissions as $staff)
                <tr>
                    <td><strong>{{ $staff['name'] }}</strong></td>
                    <td class="right">{{ $staff['count'] }}</td>
                    <td class="right">₱{{ number_format($staff['items']->sum('service_amount'), 2) }}</td>
                    <td class="right" style="color:#b45309;">₱{{ number_format($staff['pending'], 2) }}</td>
                    <td class="right" style="color:#16a34a;">₱{{ number_format($staff['paid'], 2) }}</td>
                    <td class="right"><strong>₱{{ number_format($staff['total'], 2) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#aaa;padding:12px;">No commission data for this period</td></tr>
            @endforelse
            </tbody>
            @if($staffCommissions->count())
            <tfoot>
                <tr>
                    <td><strong>Total</strong></td>
                    <td class="right">{{ $staffCommissions->sum('count') }}</td>
                    <td class="right">₱{{ number_format($staffCommissions->sum(fn($s)=>$s['items']->sum('service_amount')), 2) }}</td>
                    <td class="right" style="color:#b45309;">₱{{ number_format($staffCommissions->sum('pending'), 2) }}</td>
                    <td class="right" style="color:#16a34a;">₱{{ number_format($staffCommissions->sum('paid'), 2) }}</td>
                    <td class="right">₱{{ number_format($totalCommission, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- ── INVENTORY CONSUMPTION ────────────────────────────────────────── --}}
    <div class="section avoid-break">
        <div class="section-header">Inventory Consumed</div>
        <table>
            <thead>
                <tr>
                    <th>Product / Item</th>
                    <th class="right">Qty Used</th>
                    <th class="right">Total Cost</th>
                </tr>
            </thead>
            <tbody>
            @forelse($inventoryUsed as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="right">{{ $item['total_qty'] }}</td>
                    <td class="right">₱{{ number_format($item['total_cost'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;color:#aaa;padding:12px;">No inventory items recorded for this period</td></tr>
            @endforelse
            </tbody>
            @if($inventoryUsed->count())
            <tfoot>
                <tr>
                    <td><strong>Total</strong></td>
                    <td class="right">{{ $inventoryUsed->sum('total_qty') }}</td>
                    <td class="right"><strong>₱{{ number_format($totalInventoryCost, 2) }}</strong></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- ── PAGE BREAK BEFORE APPOINTMENTS ─────────────────────────────── --}}
    <div class="page-break"></div>

    {{-- ── APPOINTMENTS DETAIL TABLE ────────────────────────────────────── --}}
    <div class="section">
        <div class="section-header">Appointment Details ({{ $appointments->count() }} records)</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Contact</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Type</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th class="right">Amount</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody>
            @forelse($appointments as $i => $a)
                <tr class="avoid-break">
                    <td style="color:#aaa;">{{ $i+1 }}</td>
                    <td><strong>{{ $a->full_name }}</strong></td>
                    <td>{{ $a->contact_number }}</td>
                    <td>{{ $a->service->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($a->date)->format('M d, Y') }}</td>
                    <td>{{ $a->time }}</td>
                    <td>
                        <span style="font-size:9px;color:#888;">
                            {{ ucfirst($a->booking_type ?? 'online') }}
                        </span>
                    </td>
                    <td>{{ $a->assignedStaff->name ?? '—' }}</td>
                    <td><span class="pill pill-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
                    <td class="right">
                        @if($a->invoice)
                            ₱{{ number_format($a->invoice->grand_total, 2) }}
                        @elseif($a->service)
                            ₱{{ number_format($a->service->price, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if(($a->payment_status ?? null) === 'paid')
                            <span class="pill pill-paid">Paid</span>
                        @else
                            <span style="color:#aaa;font-size:9px;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" style="text-align:center;color:#aaa;padding:16px;">No appointments for this period</td></tr>
            @endforelse
            </tbody>
            @if($appointments->count())
            <tfoot>
                <tr>
                    <td colspan="9"><strong>Totals</strong></td>
                    <td class="right">
                        <strong>
                        ₱{{ number_format(
                            $appointments->sum(fn($a) =>
                                $a->invoice?->grand_total ?? $a->service?->price ?? 0
                            ), 2
                        ) }}
                        </strong>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- ── FOOTER ──────────────────────────────────────────────────────── --}}
    <div class="report-footer">
        <span>Generated by the system &nbsp;·&nbsp; {{ now()->format('M d, Y h:i A') }}</span>
        <span>Period: {{ $from->format('M d, Y') }} – {{ $to->format('M d, Y') }}</span>
    </div>

</div>

</body>
</html>