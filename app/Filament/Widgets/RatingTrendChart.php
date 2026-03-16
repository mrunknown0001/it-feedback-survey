<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\InteractsWithDashboardFilters;
use App\Models\Feedback;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RatingTrendChart extends ChartWidget
{
    use InteractsWithDashboardFilters;

    protected static ?string $heading = 'Average Rating by Date';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $range        = $this->getDateRange();
        $agentIds     = $this->getAgentIds();
        $issueTypeIds = $this->getIssueTypeIds();

        if ($range) {
            [$from, $to] = $range;
            $from = $from ?? now()->subDays(29)->startOfDay();
            $to   = $to   ?? now()->endOfDay();
        } else {
            $from = now()->subDays(29)->startOfDay();
            $to   = now()->endOfDay();
        }

        // Build list of dates between from and to (cap at 366 days to avoid huge charts)
        $days    = collect();
        $current = $from->copy()->startOfDay();
        $limit   = $to->copy()->startOfDay();
        $count   = 0;

        while ($current->lte($limit) && $count < 366) {
            $days->push($current->toDateString());
            $current->addDay();
            $count++;
        }

        $query = Feedback::selectRaw('DATE(created_at) as date, AVG(overall_rating) as avg_rating')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date');

        if (! empty($agentIds)) {
            $query->whereHas('agents', fn ($q) => $q->whereIn('agents.id', $agentIds));
        }

        if (! empty($issueTypeIds)) {
            $query->whereIn('issue_type_id', $issueTypeIds);
        }

        $ratings = $query->pluck('avg_rating', 'date');

        return [
            'datasets' => [
                [
                    'label'           => 'Avg. Rating',
                    'data'            => $days->map(fn ($d) => round($ratings[$d] ?? 0, 2))->values()->toArray(),
                    'borderColor'     => '#06b6d4',
                    'backgroundColor' => 'rgba(6,182,212,0.15)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
