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

        $cacheKey = 'widget_stats_' . md5(serialize([$range, $agentIds, $issueTypeIds]));

        [$totalFeedbacks, $avgRating, $satisfactionPct, $recentAvg, $activeAgents, $activeQuestions]
            = cache()->remember($cacheKey, 300, function () use ($range, $agentIds, $issueTypeIds) {
                // Base query scoped to date range + agents + issue types
                $base = Feedback::query();

                if (! empty($agentIds)) {
                    $base->whereHas('agents', fn ($q) => $q->whereIn('agents.id', $agentIds));
                }
                if (! empty($issueTypeIds)) {
                    $base->whereIn('issue_type_id', $issueTypeIds);
                }
                $this->applyDateConstraint($base, $range);

                // Single query for all main aggregates
                $agg = (clone $base)->selectRaw(
                    'COUNT(*) as total,
                     AVG(overall_rating) as avg_rating,
                     COUNT(CASE WHEN overall_rating >= 4 THEN 1 END) as high_ratings'
                )->first();

                $total      = $agg->total ?? 0;
                $avg        = round($agg->avg_rating ?? 0, 2);
                $satPct     = $total > 0 ? round(($agg->high_ratings / $total) * 100) : 0;

                // Last 7 days — scoped to agents + issue types (not date range)
                $recentBase = Feedback::query()->where('created_at', '>=', now()->subDays(7));
                if (! empty($agentIds)) {
                    $recentBase->whereHas('agents', fn ($q) => $q->whereIn('agents.id', $agentIds));
                }
                if (! empty($issueTypeIds)) {
                    $recentBase->whereIn('issue_type_id', $issueTypeIds);
                }
                $recentAvg = round($recentBase->avg('overall_rating') ?? 0, 2);

                return [
                    $total,
                    $avg,
                    $satPct,
                    $recentAvg,
                    Agent::active()->count(),
                    Question::active()->count(),
                ];
            });

        $activeAgents    = $activeAgents ?? 0;
        $activeQuestions = $activeQuestions ?? 0;

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
