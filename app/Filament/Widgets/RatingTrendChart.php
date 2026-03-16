<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RatingTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Daily Average Rating (Last 30 Days)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->toDateString());

        $ratings = Feedback::selectRaw('DATE(created_at) as date, AVG(overall_rating) as avg_rating')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('avg_rating', 'date');

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
