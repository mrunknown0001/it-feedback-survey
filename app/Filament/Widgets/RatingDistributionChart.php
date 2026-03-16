<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\InteractsWithDashboardFilters;
use App\Models\Feedback;
use Filament\Widgets\ChartWidget;

class RatingDistributionChart extends ChartWidget
{
    use InteractsWithDashboardFilters;

    protected static ?string $heading = 'Rating Distribution';

    protected static ?string $description = 'Breakdown of all submitted ratings (1 = Poor, 5 = Excellent)';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $range        = $this->getDateRange();
        $agentIds     = $this->getAgentIds();
        $issueTypeIds = $this->getIssueTypeIds();

        $base = Feedback::query()->whereNotNull('overall_rating');

        if (! empty($agentIds)) {
            $base->whereHas('agents', fn ($q) => $q->whereIn('agents.id', $agentIds));
        }

        if (! empty($issueTypeIds)) {
            $base->whereIn('issue_type_id', $issueTypeIds);
        }

        $this->applyDateConstraint($base, $range);

        $counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        foreach ($base->pluck('overall_rating') as $rating) {
            $star           = max(1, min(5, (int) round($rating)));
            $counts[$star]++;
        }

        return [
            'datasets' => [
                [
                    'data'            => array_values($counts),
                    'backgroundColor' => [
                        'rgba(239,68,68,0.85)',
                        'rgba(249,115,22,0.85)',
                        'rgba(234,179,8,0.85)',
                        'rgba(34,197,94,0.85)',
                        'rgba(6,182,212,0.85)',
                    ],
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => ['1 – Poor', '2 – Fair', '3 – Average', '4 – Good', '5 – Excellent'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
