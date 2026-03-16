<?php

namespace App\Filament\Widgets;

use App\Models\Agent;
use App\Models\Feedback;
use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FeedbackStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalFeedbacks = Feedback::count();
        $avgRating = round(Feedback::avg('overall_rating') ?? 0, 2);
        $activeAgents = Agent::active()->count();
        $activeQuestions = Question::active()->count();

        $recentAvg = round(
            Feedback::where('created_at', '>=', now()->subDays(7))->avg('overall_rating') ?? 0,
            2
        );

        $satisfactionPct = $totalFeedbacks > 0
            ? round((Feedback::where('overall_rating', '>=', 4)->count() / $totalFeedbacks) * 100)
            : 0;

        return [
            Stat::make('Total Feedback Received', $totalFeedbacks)
                ->description('All submitted surveys')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Overall Avg. Rating', $avgRating . ' / 5.00')
                ->description('Across all submissions')
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

            Stat::make('Active IT Agents', $activeAgents)
                ->description('Available support personnel')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('gray'),

            Stat::make('Survey Questions', $activeQuestions)
                ->description('Active questions on the form')
                ->descriptionIcon('heroicon-m-question-mark-circle')
                ->color('gray'),
        ];
    }
}
