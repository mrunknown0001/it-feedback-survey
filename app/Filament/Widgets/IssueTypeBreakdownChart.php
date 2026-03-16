<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\InteractsWithDashboardFilters;
use App\Models\IssueType;
use Filament\Widgets\ChartWidget;

class IssueTypeBreakdownChart extends ChartWidget
{
    use InteractsWithDashboardFilters;

    protected static ?string $heading = 'Top 10 Issue Types';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $range        = $this->getDateRange();
        $agentIds     = $this->getAgentIds();
        $issueTypeIds = $this->getIssueTypeIds();

        $results = IssueType::query()
            ->when(! empty($issueTypeIds), fn ($q) => $q->whereIn('id', $issueTypeIds))
            ->withCount(['feedbacks' => function ($q) use ($range, $agentIds): void {
                $this->applyDateConstraint($q, $range);
                if (! empty($agentIds)) {
                    $q->whereHas('agents', fn ($q2) => $q2->whereIn('agents.id', $agentIds));
                }
            }])
            ->orderByDesc('feedbacks_count')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => 'Feedback Count',
                    'data'            => $results->pluck('feedbacks_count')->toArray(),
                    'backgroundColor' => [
                        '#06b6d4', '#0891b2', '#0e7490', '#155e75', '#164e63',
                        '#0284c7', '#0369a1', '#1d4ed8', '#4f46e5', '#7c3aed',
                    ],
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $results->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => ['stepSize' => 1],
                ],
            ],
        ];
    }
}
