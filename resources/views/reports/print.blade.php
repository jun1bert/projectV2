<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Business Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #4d4037;
            font-size: 12px;
        }

        h1, h2 {
            margin: 0;
            color: #A48D78;
        }

        .muted { color: #817267; }
        .section { margin-top: 24px; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .grid th {
    background: #E6DAC8;
    color: #4d4037;
    text-align: center;
    padding: 8px;
    font-size: 11px;
    vertical-align: middle;
}

.grid td {
    border-bottom: 1px solid #E6DAC8;
    padding: 8px;
    text-align: center;
    vertical-align: middle;
}

.right {
    text-align: center;
}
        .kpis { width: 100%; margin-top: 18px; border-collapse: collapse; }
        .kpis td {
            width: 25%;
            background: #F4F1EA;
            border: 6px solid #fff;
            padding: 12px;
        }
        .label { font-size: 10px; color: #817267; text-transform: uppercase; }
        .value { font-size: 18px; font-weight: bold; margin-top: 5px; }
    </style>
</head>
<body>
    <h1>Martinis &amp; Manicures</h1>
    <p class="muted">
        {{ ucfirst($reportType ?? 'summary') }} Report |
        {{ $from->format('M d, Y') }} - {{ $to->format('M d, Y') }}
    </p>

    <table class="kpis">
        <tr>
            <td><div class="label">Appointments</div><div class="value">{{ $totalAppointments }}</div></td>
            <td><div class="label">Completed</div><div class="value">{{ $completed }}</div></td>
            <td><div class="label">Pending</div><div class="value">{{ $pending }}</div></td>
            <td><div class="label">Revenue</div><div class="value">&#8369;{{ number_format($totalRevenue, 2) }}</div></td>
        </tr>
    </table>

    @if(($reportType ?? 'summary') === 'summary')
    <div class="section">
        <h2>Top Services</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Service</th>
                    <th class="right">Bookings</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topServices as $item)
                    <tr>
                        <td>{{ $item->service->name ?? 'Unavailable' }}</td>
                        <td class="right">{{ $item->total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">No service data for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Appointments</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                    <tr>
                        <td>{{ $appointment->full_name }}</td>
                        <td>{{ $appointment->service->name ?? 'Unavailable' }}</td>
                        <td>{{ $appointment->date }} {{ $appointment->formatted_time }}</td>
                        <td>{{ ucfirst($appointment->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No appointments for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @elseif($reportType === 'sales')
    <div class="section">
        <h2>Sales</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Contact</th>
                    <th>Service</th>
                    <th>Payment</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesRows as $appointment)
                    <tr>
                        <td>{{ $appointment->date }} {{ $appointment->formatted_time }}</td>
                        <td>{{ $appointment->full_name }}</td>
                        <td>
                            {{ $appointment->contact_number }}
                            @if($appointment->email)
                                <br>{{ $appointment->email }}
                            @endif
                        </td>
                        <td>{{ $appointment->service->name ?? 'Unavailable' }}</td>
                        <td>{{ ucfirst($appointment->invoice->payment_method ?? 'unpaid') }}</td>
                        <td class="right">&#8369;{{ number_format($appointment->invoice?->amount_paid ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No sales data for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @elseif($reportType === 'clients')
    <div class="section">
        <h2>Client List</h2>
        <p class="muted">Private client information. Handle and store securely.</p>
        <table class="grid">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th class="right">Visits</th>
                    <th>Last Visit</th>
                    <th class="right">Total Spent</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientRows as $client)
                    <tr>
                        <td>{{ $client['name'] }}</td>
                        <td>{{ $client['contact'] }}</td>
                        <td>{{ $client['email'] ?? 'N/A' }}</td>
                        <td class="right">{{ $client['visits'] }}</td>
                        <td>{{ $client['last_visit'] ?? 'N/A' }}</td>
                        <td class="right">&#8369;{{ number_format($client['total_spent'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No client data for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</body>
</html>
