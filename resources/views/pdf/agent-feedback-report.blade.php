<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #1e40af;
            color: #ffffff;
            padding: 20px 30px;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0 0 4px 0;
        }

        .header p {
            margin: 2px 0;
            font-size: 10px;
            opacity: 0.85;
        }

        .content {
            padding: 0 30px 30px;
        }

        .summary-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 20px;
        }

        .summary-box h2 {
            margin: 0 0 10px 0;
            font-size: 13px;
            color: #1e40af;
        }

        .stats-row {
            display: flex;
            gap: 12px;
        }

        .stat-item {
            flex: 1;
            text-align: center;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
        }

        .stat-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead tr {
            background-color: #1e40af;
            color: #ffffff;
        }

        thead th {
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        tbody td {
            padding: 8px 10px;
            vertical-align: top;
        }

        .rating-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }

        .rating-high { background: #d1fae5; color: #065f46; }
        .rating-mid  { background: #fef3c7; color: #92400e; }
        .rating-low  { background: #fee2e2; color: #991b1b; }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            border-bottom: 2px solid #bfdbfe;
            padding-bottom: 6px;
            margin: 20px 0 12px 0;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>IT Support Feedback Report</h1>
        <p><strong>Agent:</strong> {{ $agent->name ?? 'N/A' }} ({{ $agent->employee_id ?? 'N/A' }})</p>
        <p><strong>Department:</strong> {{ $agent->department ?? 'N/A' }}</p>
        <p><strong>Generated:</strong> {{ $generated }}</p>
    </div>

    <div class="content">

        {{-- Summary Stats --}}
        @php
            $total = $feedbacks->count();
            $avg = $total > 0 ? round($feedbacks->avg('overall_rating'), 2) : 0;
            $satisfied = $total > 0 ? round($feedbacks->where('overall_rating', '>=', 4)->count() / $total * 100) : 0;
        @endphp

        <div class="summary-box">
            <h2>Performance Summary</h2>
            <table style="border:none; margin:0;">
                <tr>
                    <td style="text-align:center; border:none; padding:0 16px 0 0;">
                        <div class="stat-value">{{ $total }}</div>
                        <div class="stat-label">Total Surveys</div>
                    </td>
                    <td style="text-align:center; border:none; padding:0 16px;">
                        <div class="stat-value">{{ $avg }} / 5.00</div>
                        <div class="stat-label">Avg. Rating</div>
                    </td>
                    <td style="text-align:center; border:none; padding:0 0 0 16px;">
                        <div class="stat-value">{{ $satisfied }}%</div>
                        <div class="stat-label">Satisfaction Rate</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Feedback Table --}}
        <div class="section-title">Feedback Records</div>

        @if ($feedbacks->isEmpty())
            <p style="color:#6b7280; font-style:italic;">No feedback records found.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Respondent</th>
                        <th>Position</th>
                        <th>Issue Type</th>
                        <th>Rating</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($feedbacks as $feedback)
                        @php
                            $r = $feedback->overall_rating;
                            $rClass = $r >= 4.5 ? 'rating-high' : ($r >= 3 ? 'rating-mid' : 'rating-low');
                        @endphp
                        <tr>
                            <td>{{ $feedback->id }}</td>
                            <td>{{ $feedback->respondent_name }}</td>
                            <td>{{ $feedback->position }}</td>
                            <td>{{ $feedback->issueType?->name ?? '—' }}</td>
                            <td>
                                <span class="rating-badge {{ $rClass }}">
                                    {{ number_format($r, 2) }} ★
                                </span>
                            </td>
                            <td>{{ $feedback->created_at->format('M j, Y') }}</td>
                        </tr>

                        {{-- Issue Description --}}
                        @if ($feedback->issue_description)
                            <tr>
                                <td colspan="6" style="padding: 2px 10px 8px 10px; font-size:10px; color:#4b5563; background:#f9fafb;">
                                    <em>Issue: {{ $feedback->issue_description }}</em>
                                </td>
                            </tr>
                        @endif

                        {{-- Survey Responses --}}
                        @foreach ($feedback->responses as $response)
                            <tr style="background:#f1f5f9;">
                                <td style="border:none;"></td>
                                <td colspan="4" style="font-size:10px; color:#374151; padding: 3px 10px;">
                                    <strong>Q:</strong> {{ $response->question?->question_text ?? '—' }}
                                    @if ($response->rating_value)
                                        &nbsp;&nbsp;<span class="rating-badge {{ $response->rating_value >= 4 ? 'rating-high' : ($response->rating_value >= 3 ? 'rating-mid' : 'rating-low') }}">{{ $response->rating_value }} / 5</span>
                                    @endif
                                    @if ($response->text_value)
                                        <br><em style="color:#6b7280;">{{ $response->text_value }}</em>
                                    @endif
                                </td>
                                <td style="border:none;"></td>
                            </tr>
                        @endforeach

                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="footer">
            IT Technical Support Service &bull; Feedback Survey System &bull; Generated {{ $generated }}
        </div>
    </div>
</body>
</html>
