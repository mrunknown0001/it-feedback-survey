<?php

namespace App\Filament\Agent\Widgets;

use App\Models\Feedback;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class MyStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $agentId = auth()->user()->agent_id;

        $base = fn () => Feedback::whereHas(
            'agents',
            fn (Builder $q) => $q->where('agents.id', $agentId)
        );

        $total = $base()->count();
        $avgRating = round($base()->avg('overall_rating') ?? 0, 2);

        $recentAvg = round(
            $base()->where('created_at', '>=', now()->subDays(7))->avg('overall_rating') ?? 0,
            2
        );

        $satisfactionPct = $total > 0
            ? round(($base()->where('overall_rating', '>=', 4)->count() / $total) * 100)
            : 0;

        $lastMonth = round(
            $base()->where('created_at', '>=', now()->subDays(30))->avg('overall_rating') ?? 0,
            2
        );

        return [
            Stat::make('My Total Feedback', $total)
                ->description('Surveys mentioning you')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('My Avg. Rating', $avgRating . ' / 5.00')
                ->description('Overall average score')
                ->descriptionIcon('heroicon-m-star')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),

            Stat::make('Satisfaction Rate', $satisfactionPct . '%')
                ->description('Ratings of 4 or higher')
                ->descriptionIcon('heroicon-m-face-smile')
                ->color($satisfactionPct >= 70 ? 'success' : ($satisfactionPct >= 50 ? 'warning' : 'danger')),

            Stat::make('Last 7 Days Avg.', $recentAvg . ' / 5.00')
                ->description('Recent performance')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Last 30 Days Avg.', $lastMonth . ' / 5.00')
                ->description('Monthly performance')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('gray'),
        ];
    }
}
