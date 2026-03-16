<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }

        /* ── Header ─────────────────────────────────────────────── */
        .header { background: #1e293b; color: #fff; padding: 18px 24px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; font-weight: bold; letter-spacing: 0.5px; }
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

        /* ── Section titles ─────────────────────────────────────── */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #475569;
            border-bottom: 2px solid #0891b2;
            padding-bottom: 4px;
            margin: 0 24px 12px;
        }

        /* ── KPI grid ───────────────────────────────────────────── */
        .kpi-grid { margin: 0 24px 24px; }
        .kpi-row { width: 100%; margin-bottom: 8px; }
        .kpi-row:after { content: ""; display: table; clear: both; }
        .kpi-box {
            float: left;
            width: 30%;
            margin-right: 3%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0891b2;
            border-radius: 4px;
            padding: 10px 12px;
        }
        .kpi-box:last-child { margin-right: 0; }
        .kpi-value { font-size: 18px; font-weight: bold; color: #0f172a; }
        .kpi-label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }
        .kpi-desc  { font-size: 9px; color: #94a3b8; margin-top: 3px; }

        .kpi-green  { border-left-color: #16a34a; }
        .kpi-yellow { border-left-color: #ca8a04; }
        .kpi-red    { border-left-color: #dc2626; }
        .kpi-blue   { border-left-color: #0891b2; }
        .kpi-gray   { border-left-color: #94a3b8; }

        /* ── Tables ─────────────────────────────────────────────── */
        table { width: calc(100% - 48px); margin: 0 24px; border-collapse: collapse; }
        thead tr { background: #0f172a; color: #e2e8f0; }
        thead th { padding: 8px 10px; text-align: left; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(odd)  { background: #fff; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }

        /* ── Rating bar ─────────────────────────────────────────── */
        .bar-track { background: #e2e8f0; border-radius: 2px; height: 8px; width: 100%; }
        .bar-fill  { background: #0891b2; border-radius: 2px; height: 8px; }

        /* ── Agent badges ───────────────────────────────────────── */
        .badge { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }
        .badge-gray    { background: #f1f5f9; color: #475569; }

        .dot-active   { color: #16a34a; font-size: 14px; }
        .dot-inactive { color: #dc2626; font-size: 14px; }

        /* ── Footer ─────────────────────────────────────────────── */
        .footer { margin-top: 20px; padding: 8px 24px; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; }

        .page-break { page-break-before: always; }
        .mb-20 { margin-bottom: 20px; }
    </style>
</head>
<body>

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="header">
    <h1>Dashboard Report</h1>
    <div class="meta">IT Technical Support Service &mdash; Feedback Analytics</div>
    @if($periodLabel)
        <div class="period-badge">{{ $periodLabel }}</div>
    @else
        <div class="period-badge">All Time</div>
    @endif
</div>

{{-- ── KPI Stats ────────────────────────────────────────────────────────── --}}
<div class="section-title">Overview</div>
<div class="kpi-grid">
    <div class="kpi-row">
        <div class="kpi-box kpi-blue">
            <div class="kpi-value">{{ $totalFeedbacks }}</div>
            <div class="kpi-label">Total Feedback</div>
            <div class="kpi-desc">All submitted surveys</div>
        </div>
        <div class="kpi-box {{ $avgRating >= 4 ? 'kpi-green' : ($avgRating >= 3 ? 'kpi-yellow' : 'kpi-red') }}">
            <div class="kpi-value">{{ number_format($avgRating, 2) }} / 5.00</div>
            <div class="kpi-label">Overall Avg. Rating</div>
            <div class="kpi-desc">Across all submissions</div>
        </div>
        <div class="kpi-box {{ $satisfactionPct >= 70 ? 'kpi-green' : ($satisfactionPct >= 50 ? 'kpi-yellow' : 'kpi-red') }}">
            <div class="kpi-value">{{ $satisfactionPct }}%</div>
            <div class="kpi-label">Satisfaction Rate</div>
            <div class="kpi-desc">Ratings of 4 or higher</div>
        </div>
    </div>
    <div class="kpi-row">
        <div class="kpi-box kpi-blue">
            <div class="kpi-value">{{ number_format($recentAvg, 2) }} / 5.00</div>
            <div class="kpi-label">Last 7 Days Avg.</div>
            <div class="kpi-desc">Recent performance</div>
        </div>
        <div class="kpi-box kpi-gray">
            <div class="kpi-value">{{ $activeAgents }}</div>
            <div class="kpi-label">Active IT Agents</div>
            <div class="kpi-desc">Available support personnel</div>
        </div>
        <div class="kpi-box kpi-gray">
            <div class="kpi-value">{{ $activeQuestions }}</div>
            <div class="kpi-label">Survey Questions</div>
            <div class="kpi-desc">Active questions on the form</div>
        </div>
    </div>
</div>

{{-- ── Rating Trend ─────────────────────────────────────────────────────── --}}
<div class="section-title mb-20">
    Rating Trend &mdash; {{ $trendFrom }} to {{ $trendTo }}
</div>

@if($trendData->isEmpty())
    <p style="margin: 0 24px 20px; color: #94a3b8; font-size: 10px;">No data for this period.</p>
@else
    <table class="mb-20" style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="width: 22%;">Date</th>
                <th style="width: 18%;">Avg. Rating</th>
                <th>Distribution</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trendData->filter(fn ($r) => $r['rating'] > 0) as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ number_format($row['rating'], 2) }} / 5.00</td>
                    <td>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ round(($row['rating'] / 5) * 100) }}%;"></div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- ── Agent Performance ────────────────────────────────────────────────── --}}
<div class="page-break"></div>

<div class="header">
    <h1>Agent Performance</h1>
    <div class="meta">IT Technical Support Service &mdash; Feedback Analytics</div>
    @if($periodLabel)
        <div class="period-badge">{{ $periodLabel }}</div>
    @else
        <div class="period-badge">All Time</div>
    @endif
</div>

<div class="section-title">Agent Leaderboard</div>

<table>
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 25%;">Agent</th>
            <th style="width: 20%;">Department</th>
            <th style="width: 15%;">Total Feedback</th>
            <th style="width: 20%;">Avg. Rating</th>
            <th style="width: 15%;">Status</th>
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

{{-- ── Footer ───────────────────────────────────────────────────────────── --}}
<div class="footer">
    Generated on {{ $generatedAt }}
    &bull; {{ $agents->count() }} agent(s) listed
    @if($periodLabel) &bull; Period: {{ $periodLabel }} @endif
</div>

</body>
</html>
