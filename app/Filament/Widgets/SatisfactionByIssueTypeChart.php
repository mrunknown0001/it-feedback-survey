<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\InteractsWithDashboardFilters;
use App\Models\IssueType;
use Filament\Widgets\ChartWidget;

class SatisfactionByIssueTypeChart extends ChartWidget
{
    use InteractsWithDashboardFilters;

    protected static ?string $heading = 'Avg. Rating by Issue Type';

    protected static ?string $description = 'Average satisfaction score per issue category (higher is better)';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $range        = $this->getDateRange();
        $agentIds     = $this->getAgentIds();
        $issueTypeIds = $this->getIssueTypeIds();

        $results = IssueType::query()
            ->when(! empty($issueTypeIds), fn ($q) => $q->whereIn('id', $issueTypeIds))
            ->withAvg(['feedbacks' => function ($q) use ($range, $agentIds): void {
                $this->applyDateConstraint($q, $range);
                if (! empty($agentIds)) {
                    $q->whereHas('agents', fn ($q2) => $q2->whereIn('agents.id', $agentIds));
                }
            }], 'overall_rating')
            ->get()
            ->filter(fn ($r) => ($r->feedbacks_avg_overall_rating ?? 0) > 0)
            ->sortByDesc('feedbacks_avg_overall_rating')
            ->values();

        $backgroundColors = $results->map(fn ($r) => match (true) {
            ($r->feedbacks_avg_overall_rating ?? 0) >= 4   => 'rgba(34,197,94,0.85)',
            ($r->feedbacks_avg_overall_rating ?? 0) >= 3   => 'rgba(234,179,8,0.85)',
            default                                         => 'rgba(239,68,68,0.85)',
        })->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Avg. Rating',
                    'data'            => $results->map(fn ($r) => round($r->feedbacks_avg_overall_rating ?? 0, 2))->toArray(),
                    'backgroundColor' => $backgroundColors,
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
            'indexAxis' => 'y',
            'plugins'   => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => [
                    'min'   => 0,
                    'max'   => 5,
                    'ticks' => ['stepSize' => 1],
                ],
            ],
        ];
    }
}
