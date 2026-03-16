<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }

        .header { background: #1e293b; color: #fff; padding: 18px 24px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: bold; letter-spacing: 0.5px; }
        .header .meta { font-size: 10px; color: #94a3b8; margin-top: 4px; }

        .period-badge {
            display: inline-block;
            background: #0891b2;
            color: #fff;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 3px;
            margin-top: 6px;
        }

        table { width: 100%; border-collapse: collapse; margin: 0 24px; width: calc(100% - 48px); }
        thead tr { background: #0f172a; color: #e2e8f0; }
        thead th { padding: 9px 10px; text-align: left; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(odd)  { background: #fff; }
        tbody td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }

        .badge { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }
        .badge-gray    { background: #f1f5f9; color: #475569; }

        .dot-active   { color: #16a34a; font-size: 14px; }
        .dot-inactive { color: #dc2626; font-size: 14px; }

        .footer { margin-top: 20px; padding: 8px 24px; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>

<div class="header">
    <h1>Agent Performance Overview</h1>
    <div class="meta">IT Technical Support Service &mdash; Feedback Analytics</div>
    @if($periodLabel)
        <div class="period-badge">{{ $periodLabel }}</div>
    @else
        <div class="period-badge">All Time</div>
    @endif
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Agent</th>
            <th>Department</th>
            <th>Total Feedback</th>
            <th>Avg. Rating</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($agents as $i => $agent)
            @php
                $avg = round($agent->feedbacks_avg_overall_rating ?? 0, 2);
                $badgeClass = match(true) {
                    $avg >= 4.5 => 'badge-success',
                    $avg >= 3   => 'badge-warning',
                    $avg > 0    => 'badge-danger',
                    default     => 'badge-gray',
                };
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $agent->name }}</strong></td>
                <td>{{ $agent->department ?? '—' }}</td>
                <td>{{ $agent->feedbacks_count }}</td>
                <td>
                    <span class="badge {{ $badgeClass }}">
                        {{ number_format($avg, 2) }} / 5.00
                    </span>
                </td>
                <td>
                    @if($agent->is_active)
                        <span class="dot-active">&#x25CF;</span> Active
                    @else
                        <span class="dot-inactive">&#x25CF;</span> Inactive
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center; padding: 20px; color: #94a3b8;">No data found for the selected filters.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Generated on {{ $generatedAt }} &bull; {{ $agents->count() }} agent(s) listed
    @if($periodLabel) &bull; Period: {{ $periodLabel }} @endif
</div>

</body>
</html>
