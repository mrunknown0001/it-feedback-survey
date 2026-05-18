<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\InteractsWithDashboardFilters;
use App\Models\IssueType;
use App\Support\Branding;
use Filament\Widgets\ChartWidget;

class IssueTypeBreakdownChart extends ChartWidget
{
    use InteractsWithDashboardFilters;

    protected static ?string $heading = 'Top 10 Issue Types';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $range = $this->getDateRange();
        $agentIds = $this->getAgentIds();
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

        $primaryHex = Branding::primaryHex();
        $primaryRgb = Branding::primaryRgb();

        // Use the primary color with descending opacity so the palette
        // automatically follows whatever the admin has selected.
        $palette = collect(range(10, 1))->map(
            fn ($i) => "rgba({$primaryRgb}, ".round($i / 10, 2).')'
        )->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Feedback Count',
                    'data' => $results->pluck('feedbacks_count')->toArray(),
                    'backgroundColor' => $palette,
                    'borderRadius' => 4,
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
                    'ticks' => ['stepSize' => 1],
                ],
            ],
        ];
    }
}
