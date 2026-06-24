<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Business Report – Martinis &amp; Manicures</title>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

@page {
    size: 8.5in 13in;
    margin: 0.6in 0.55in;
}

body {
    font-family: "DejaVu Sans", "Helvetica Neue", Arial, sans-serif;
    font-size: 10px;
    color: #2f2a26;
    background: #ffffff;
    line-height: 1.7;
    padding: 0.6in 0.55in;
    max-width: 8.5in;
    margin: 0 auto;
}

/* ─── PAGE WRAPPER ─── */
.page {
    width: 100%;
    padding: 0;
}

/* ─── HEADER ─── */
.header {
    background: #faf8f5;
    border-bottom: 3.5px solid #b8924b;
    padding: 20px 22px;
    margin-bottom: 26px;
}

.header-table,
.kpi-table,
.two-col-table,
.signature-table {
    width: 100%;
    border-collapse: collapse;
}

.header-left h1 {
    font-size: 24px;
    font-weight: 700;
    color: #2f241f;
    letter-spacing: -0.3px;
    margin-bottom: 4px;
}

.header-left p {
    color: #7a7168;
    font-size: 10px;
    letter-spacing: 0.3px;
}

.header-right {
    text-align: right;
    font-size: 9px;
    color: #7a7168;
    line-height: 2;
    vertical-align: top;
}

.header-right strong {
    color: #2f241f;
}

.badge {
    display: inline-block;
    margin-top: 8px;
    padding: 4px 11px;
    background: #b8924b;
    color: #ffffff;
    border-radius: 20px;
    font-size: 7.5px;
    font-weight: 700;
    letter-spacing: .7px;
    text-transform: uppercase;
}

/* ─── CONFIDENTIAL BOX ─── */
.confidential-box {
    background: #faf7f2;
    border: 1px solid #e4d9c9;
    border-left: 5px solid #b8924b;
    padding: 11px 16px;
    margin-bottom: 22px;
    font-size: 8.5px;
    color: #6b6258;
    line-height: 1.6;
}

/* ─── EXECUTIVE SUMMARY ─── */
.summary {
    background: #fbf8f2;
    border: 1px solid #eadfce;
    border-left: 5px solid #b8924b;
    padding: 14px 18px;
    margin-bottom: 26px;
    color: #3c3630;
    font-size: 10px;
    line-height: 1.75;
}

.summary strong {
    color: #2f241f;
}

/* ─── KPI CARDS ─── */
.kpi-table {
    margin-bottom: 26px;
}

.kpi-table td {
    width: 25%;
    padding: 0 8px;
}

.kpi-table td:first-child { padding-left: 0; }
.kpi-table td:last-child  { padding-right: 0; }

.kpi-card {
    border: 1px solid #ddd5c8;
    border-top: 3.5px solid #b8924b;
    background: #ffffff;
    padding: 14px 16px;
    min-height: 72px;
}

.kpi-label {
    font-size: 7.5px;
    color: #8a8178;
    text-transform: uppercase;
    letter-spacing: .7px;
    margin-bottom: 8px;
}

.kpi-value {
    font-size: 22px;
    font-weight: 700;
    color: #2f241f;
}

/* ─── COLOUR UTILITIES ─── */
.gold  { color: #b8924b; }
.green { color: #15803d; }
.blue  { color: #1d4ed8; }
.red   { color: #dc2626; }
.muted { color: #9a9188; }

/* ─── SECTION BLOCKS ─── */
.section {
    border: 1px solid #e4d9c9;
    margin-bottom: 26px;
    background: #ffffff;
}

.section-title {
    background: #2f241f;
    color: #ffffff;
    padding: 9px 16px;
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
}

.section-body {
    padding: 16px 18px;
}

/* ─── TWO-COLUMN LAYOUT ─── */
.two-col-table {
    margin-bottom: 26px;
}

.two-col-table td {
    width: 50%;
    vertical-align: top;
}

.two-col-table td:first-child { padding-right: 12px; }
.two-col-table td:last-child  { padding-left: 12px; }

/* ─── DATA TABLES ─── */
.report-table {
    width: 100%;
    border-collapse: collapse;
}

.report-table th {
    background: #2f241f;
    color: #ffffff;
    padding: 10px 14px;
    text-align: center;
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: .5px;
    font-weight: 700;
}

.report-table td {
    padding: 9px 14px;
    border-bottom: 1px solid #eee7dc;
    vertical-align: middle;
    font-size: 9px;
    line-height: 1.55;
    text-align: center;
}

.report-table tr:last-child td {
    border-bottom: none;
}

.report-table tr:nth-child(even) td {
    background: #fbf8f2;
}

.report-table tfoot td {
    border-top: 2px solid #b8924b;
    border-bottom: none;
    font-weight: 700;
    background: #f5efe4;
    padding: 11px 14px;
    font-size: 9.5px;
    text-align: center;
}

/* ─── STATUS BARS ─── */
.status-row {
    margin-bottom: 14px;
}

.status-label {
    width: 100%;
    margin-bottom: 5px;
    font-size: 9px;
}

.status-name  { float: left;  color: #4f4740; }
.status-count { float: right; font-weight: 700; color: #2f241f; }
.clear        { clear: both; }

.bar-track {
    height: 7px;
    background: #eee8df;
    border-radius: 20px;
    overflow: hidden;
}

.bar-fill {
    height: 7px;
    border-radius: 20px;
}

/* ─── DOT INDICATORS ─── */
.dot {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    margin-right: 6px;
    vertical-align: middle;
}

.dot-amber { background: #f59e0b; }
.dot-blue  { background: #3b82f6; }
.dot-green { background: #16a34a; }
.dot-gray  { background: #9ca3af; }
.dot-red   { background: #ef4444; }

/* ─── STATUS PILLS ─── */
.pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 7.5px;
    font-weight: 700;
    border: 1px solid transparent;
    white-space: nowrap;
}

.pill-pending   { background:#fffbeb; color:#b45309; border-color:#fcd34d; }
.pill-confirmed { background:#eff6ff; color:#2563eb; border-color:#93c5fd; }
.pill-completed { background:#f0fdf4; color:#15803d; border-color:#86efac; }
.pill-rejected  { background:#fef2f2; color:#b91c1c; border-color:#fca5a5; }
.pill-paid      { background:#ecfdf5; color:#047857; border-color:#6ee7b7; }

/* ─── ALIGNMENT ─── */
.right, .text-right { text-align: center; }
.center              { text-align: center; }
.small               { font-size: 8px; }

/* ─── PAGE BREAK ─── */
.page-break { page-break-before: always; }

/* ─── AVOID BREAK INSIDE ─── */
.avoid-break,
.section,
.summary,
.kpi-card,
tr {
    page-break-inside: avoid;
}

thead { display: table-header-group; }
tfoot { display: table-footer-group; }

/* ─── SIGNATURE BLOCK ─── */
.signature-table {
    margin-top: 64px;
}

.signature-table td {
    width: 50%;
    font-size: 9px;
    color: #333;
}

.signature-line {
    border-top: 1.5px solid #666;
    width: 210px;
    padding-top: 8px;
    color: #4b4038;
    font-size: 8.5px;
    letter-spacing: 0.2px;
}

/* ─── FOOTER ─── */
.footer {
    margin-top: 36px;
    padding-top: 10px;
    border-top: 1px solid #e5dccf;
    font-size: 7.5px;
    color: #a09890;
    line-height: 1.6;
}

.footer span { float: right; }
</style>
</head>

<body>
<div class="page">

    <!-- ══════════════════════════════════════
         PAGE 1 · SUMMARY REPORT
    ══════════════════════════════════════ -->

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <h1>Martinis &amp; Manicures</h1>
                    <p>Business Performance Report</p>
                </td>
                <td class="header-right">
                    <p><strong>Report ID:</strong> REP-{{ now()->format('YmdHis') }}</p>
                    <p><strong>Period:</strong> {{ $from->format('M d, Y') }} — {{ $to->format('M d, Y') }}</p>
                    <p><strong>Generated:</strong> {{ now()->format('M d, Y h:i A') }}</p>
                    <span class="badge">{{ ucfirst(str_replace('_', ' ', $range)) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="confidential-box avoid-break">
        <strong>CONFIDENTIAL BUSINESS REPORT</strong><br>
        This report contains operational, financial, appointment, staff commission,
        and inventory analytics for the selected reporting period.
        Distribution is restricted to authorised personnel only.
    </div>

    <div class="summary avoid-break">
        During the selected period, the business generated
        <strong>₱{{ number_format($totalRevenue, 2) }}</strong>
        from <strong>{{ $totalAppointments }}</strong> appointment{{ $totalAppointments !== 1 ? 's' : '' }}.
        A total of <strong>{{ $completed }}</strong> appointment{{ $completed !== 1 ? 's were' : ' was' }} completed,
        while <strong>{{ $pending }}</strong> remain{{ $pending !== 1 ? '' : 's' }} pending.
    </div>

    <!-- KPI Cards -->
    <table class="kpi-table avoid-break">
        <tr>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Total Appointments</div>
                    <div class="kpi-value">{{ $totalAppointments }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Completed</div>
                    <div class="kpi-value green">{{ $completed }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Revenue</div>
                    <div class="kpi-value gold">₱{{ number_format($totalRevenue, 2) }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Commissions</div>
                    <div class="kpi-value blue">₱{{ number_format($totalCommission, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Status Breakdown + Top Services -->
    <table class="two-col-table avoid-break">
        <tr>
            <td>
                <div class="section" style="margin-bottom:0;">
                    <div class="section-title">Appointment Status Breakdown</div>
                    <div class="section-body">
                        @php
                            $statuses = [
                                ['label'=>'Pending',   'count'=>$pending,   'dot'=>'dot-amber', 'bar'=>'#f59e0b'],
                                ['label'=>'Confirmed', 'count'=>$confirmed, 'dot'=>'dot-blue',  'bar'=>'#3b82f6'],
                                ['label'=>'Completed', 'count'=>$completed, 'dot'=>'dot-green', 'bar'=>'#16a34a'],
                                ['label'=>'Rejected',  'count'=>$rejected,  'dot'=>'dot-red',   'bar'=>'#ef4444'],
                            ];
                        @endphp

                        @foreach($statuses as $s)
                            @php $pct = $totalAppointments > 0 ? round($s['count'] / $totalAppointments * 100) : 0; @endphp
                            <div class="status-row">
                                <div class="status-label">
                                    <span class="status-name">
                                        <span class="dot {{ $s['dot'] }}"></span>{{ $s['label'] }}
                                    </span>
                                    <span class="status-count">{{ $s['count'] }} <span class="muted">({{ $pct }}%)</span></span>
                                    <div class="clear"></div>
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width:{{ $pct }}%; background:{{ $s['bar'] }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </td>

            <td>
                <div class="section" style="margin-bottom:0;">
                    <div class="section-title">Top Services</div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th style="width:28px;">#</th>
                                <th>Service</th>
                                <th class="right">Bookings</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($topServices as $i => $item)
                            <tr>
                                <td class="gold"><strong>{{ $i + 1 }}</strong></td>
                                <td>{{ $item->service->name ?? '—' }}</td>
                                <td class="right"><strong>{{ $item->total }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="center muted" style="padding:18px 0;">No service data available</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Financial Overview -->
    <div class="section avoid-break">
        <div class="section-title">Financial Overview</div>
        <table class="report-table">
            <tbody>
                <tr>
                    <td>Total Revenue</td>
                    <td class="right">₱{{ number_format($totalRevenue, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Commission Paid Out</td>
                    <td class="right">₱{{ number_format($totalCommission, 2) }}</td>
                </tr>
                <tr>
                    <td>Inventory Consumption</td>
                    <td class="right">₱{{ number_format($totalInventoryCost, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>Estimated Net Income</td>
                    <td class="right">₱{{ number_format($totalRevenue - $totalCommission - $totalInventoryCost, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Staff Commission Breakdown -->
    <div class="section avoid-break">
        <div class="section-title">Staff Commission Breakdown</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th class="right">Appointments</th>
                    <th class="right">Service Value</th>
                    <th class="right">Pending</th>
                    <th class="right">Paid</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
            @forelse($staffCommissions as $staff)
                <tr>
                    <td><strong>{{ $staff['name'] }}</strong></td>
                    <td class="right">{{ $staff['count'] }}</td>
                    <td class="right">₱{{ number_format($staff['items']->sum('service_amount'), 2) }}</td>
                    <td class="right">₱{{ number_format($staff['pending'], 2) }}</td>
                    <td class="right">₱{{ number_format($staff['paid'], 2) }}</td>
                    <td class="right"><strong>₱{{ number_format($staff['total'], 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center muted" style="padding:18px 0;">No commission data for this period</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Inventory Consumed -->
    <div class="section avoid-break">
        <div class="section-title">Inventory Consumed</div>
        <table class="report-table">
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
                <tr>
                    <td colspan="3" class="center muted" style="padding:18px 0;">No inventory items recorded for this period</td>
                </tr>
            @endforelse
            </tbody>

            @if($inventoryUsed->count())
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="right">{{ $inventoryUsed->sum('total_qty') }}</td>
                    <td class="right">₱{{ number_format($totalInventoryCost, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <!-- Signatures -->
    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-line">Prepared By</div>
            </td>
            <td class="text-right">
                <div class="signature-line" style="margin-left:auto;">Approved By</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated by system &nbsp;·&nbsp; {{ now()->format('M d, Y h:i A') }}
        <span>Period: {{ $from->format('M d, Y') }} – {{ $to->format('M d, Y') }}</span>
    </div>


    <!-- ══════════════════════════════════════
         PAGE 2 · APPOINTMENT DETAILS
    ══════════════════════════════════════ -->

    <div class="page-break"></div>

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <h1>Appointment Details</h1>
                    <p>{{ $appointments->count() }} record{{ $appointments->count() !== 1 ? 's' : '' }} included in this report</p>
                </td>
                <td class="header-right">
                    <p><strong>Martinis &amp; Manicures</strong></p>
                    <p>{{ $from->format('M d, Y') }} — {{ $to->format('M d, Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Appointment Details</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:26px;">#</th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Staff</th>
                    <th>Status</th>
                    <th class="right">Amount</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody>
            @forelse($appointments as $i => $a)
                <tr>
                    <td class="muted">{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $a->full_name }}</strong><br>
                        <span class="small muted">{{ $a->contact_number }}</span>
                    </td>
                    <td>{{ $a->service->name ?? '—' }}</td>
                    <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($a->date)->format('M d, Y') }}</td>
                    <td style="white-space:nowrap;">{{ $a->time }}</td>
                    <td>{{ $a->assignedStaff->name ?? '—' }}</td>
                    <td>
                        <span class="pill pill-{{ $a->status }}">{{ ucfirst($a->status) }}</span>
                    </td>
                    <td class="right" style="white-space:nowrap;">
                        @if($a->invoice)
                            ₱{{ number_format($a->invoice->grand_total, 2) }}
                        @elseif($a->service)
                            ₱{{ number_format($a->service->price, 2) }}
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if(($a->payment_status ?? null) === 'paid')
                            <span class="pill pill-paid">Paid</span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="center muted" style="padding:24px 0;">No appointments for this period</td>
                </tr>
            @endforelse
            </tbody>

            @if($appointments->count())
            <tfoot>
                <tr>
                    <td colspan="7">Total</td>
                    <td class="right">
                        ₱{{ number_format(
                            $appointments->sum(fn($a) =>
                                $a->invoice?->grand_total ?? $a->service?->price ?? 0
                            ), 2
                        ) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <div class="footer">
        Generated by system &nbsp;·&nbsp; {{ now()->format('M d, Y h:i A') }}
        <span>Period: {{ $from->format('M d, Y') }} – {{ $to->format('M d, Y') }}</span>
    </div>

</div>
</body>
</html>