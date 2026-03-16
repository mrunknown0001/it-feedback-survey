<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\InteractsWithDashboardFilters;
use App\Models\Agent;
use App\Models\Feedback;
use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FeedbackStatsOverview extends BaseWidget
{
    use InteractsWithDashboardFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $range        = $this->getDateRange();
        $agentIds     = $this->getAgentIds();
        $issueTypeIds = $this->getIssueTypeIds();

        // Base query scoped to date range + agents + issue types
        $base = Feedback::query();

        if (! empty($agentIds)) {
            $base->whereHas('agents', fn ($q) => $q->whereIn('agents.id', $agentIds));
        }

        if (! empty($issueTypeIds)) {
            $base->whereIn('issue_type_id', $issueTypeIds);
        }

        $this->applyDateConstraint($base, $range);

        $totalFeedbacks = (clone $base)->count();
        $avgRating      = round((clone $base)->avg('overall_rating') ?? 0, 2);

        $satisfactionPct = $totalFeedbacks > 0
            ? round(((clone $base)->where('overall_rating', '>=', 4)->count() / $totalFeedbacks) * 100)
            : 0;

        // Last 7 days — scoped to agents + issue types (not date range) so it always reflects recent activity
        $recentBase = Feedback::query()->where('created_at', '>=', now()->subDays(7));
        if (! empty($agentIds)) {
            $recentBase->whereHas('agents', fn ($q) => $q->whereIn('agents.id', $agentIds));
        }
        if (! empty($issueTypeIds)) {
            $recentBase->whereIn('issue_type_id', $issueTypeIds);
        }
        $recentAvg = round($recentBase->avg('overall_rating') ?? 0, 2);

        $activeAgents   = Agent::active()->count();
        $activeQuestions = Question::active()->count();

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
