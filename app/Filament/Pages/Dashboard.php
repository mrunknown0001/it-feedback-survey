<?php

namespace App\Filament\Pages;

use App\Models\Agent;
use App\Models\Feedback;
use App\Models\IssueType;
use App\Models\Location;
use App\Models\Question;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        $year = Carbon::now()->year;

        return $form
            ->schema([
                Select::make('quarter')
                    ->label('Quarter')
                    ->placeholder('All quarters')
                    ->options([
                        'q1' => "Q1 {$year} (Jan – Mar)",
                        'q2' => "Q2 {$year} (Apr – Jun)",
                        'q3' => "Q3 {$year} (Jul – Sep)",
                        'q4' => "Q4 {$year} (Oct – Dec)",
                    ])
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        if (filled($state)) {
                            $set('date_from', null);
                            $set('date_to', null);
                        }
                    }),

                DatePicker::make('date_from')
                    ->label('From')
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        if (filled($state)) {
                            $set('quarter', null);
                        }
                    }),

                DatePicker::make('date_to')
                    ->label('To')
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        if (filled($state)) {
                            $set('quarter', null);
                        }
                    }),

                Select::make('agent_ids')
                    ->label('Agents')
                    ->placeholder('All agents')
                    ->options(fn () => Agent::orderBy('name')->pluck('name', 'id')->toArray())
                    ->multiple()
                    ->searchable()
                    ->live(),

                Select::make('issue_type_ids')
                    ->label('Issue Types')
                    ->placeholder('All issue types')
                    ->options(fn () => IssueType::orderBy('name')->pluck('name', 'id')->toArray())
                    ->multiple()
                    ->searchable()
                    ->live(),

                Select::make('location_ids')
                    ->label('Locations')
                    ->placeholder('All locations')
                    ->options(fn () => Location::orderBy('sort_order')->orderBy('name')->pluck('name', 'id')->toArray())
                    ->multiple()
                    ->searchable()
                    ->live(),
            ])
            ->columns(4);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    $range     = $this->getDateRange();
                    $agentIds  = $this->getAgentIds();
                    $period    = $this->getPeriodLabel();

                    // ── KPI stats ────────────────────────────────────────────
                    $base = Feedback::query();
                    if (! empty($agentIds)) {
                        $base->whereHas('agents', fn ($q) => $q->whereIn('agents.id', $agentIds));
                    }
                    $this->applyDateConstraint($base, $range);

                    $totalFeedbacks  = (clone $base)->count();
                    $avgRating       = round((clone $base)->avg('overall_rating') ?? 0, 2);
                    $satisfactionPct = $totalFeedbacks > 0
                        ? round(((clone $base)->where('overall_rating', '>=', 4)->count() / $totalFeedbacks) * 100)
                        : 0;

                    $recentBase = Feedback::query()->where('created_at', '>=', now()->subDays(7));
                    if (! empty($agentIds)) {
                        $recentBase->whereHas('agents', fn ($q) => $q->whereIn('agents.id', $agentIds));
                    }
                    $recentAvg = round($recentBase->avg('overall_rating') ?? 0, 2);

                    $activeAgents    = Agent::active()->count();
                    $activeQuestions = Question::active()->count();

                    // ── Rating trend ─────────────────────────────────────────
                    if ($range) {
                        [$from, $to] = $range;
                        $trendFrom = $from ?? now()->subDays(29)->startOfDay();
                        $trendTo   = $to   ?? now()->endOfDay();
                    } else {
                        $trendFrom = now()->subDays(29)->startOfDay();
                        $trendTo   = now()->endOfDay();
                    }

                    $trendDays = collect();
                    $cur       = $trendFrom->copy()->startOfDay();
                    $limit     = $trendTo->copy()->startOfDay();
                    $cnt       = 0;
                    while ($cur->lte($limit) && $cnt < 366) {
                        $trendDays->push($cur->toDateString());
                        $cur->addDay();
                        $cnt++;
                    }

                    $trendQuery = Feedback::selectRaw('DATE(created_at) as date, AVG(overall_rating) as avg_rating')
                        ->whereBetween('created_at', [$trendFrom, $trendTo])
                        ->groupBy('date');
                    if (! empty($agentIds)) {
                        $trendQuery->whereHas('agents', fn ($q) => $q->whereIn('agents.id', $agentIds));
                    }

                    $trendRatings = $trendQuery->pluck('avg_rating', 'date');
                    $trendData    = $trendDays->map(fn ($d) => [
                        'date'   => Carbon::parse($d)->format('d M Y'),
                        'rating' => round($trendRatings[$d] ?? 0, 2),
                    ])->values();

                    // ── Agent performance ────────────────────────────────────
                    $agentQuery = Agent::query();
                    if ($range) {
                        [$from, $to] = $range;
                        $constraint  = function ($q) use ($from, $to): void {
                            if ($from && $to) {
                                $q->whereBetween('feedbacks.created_at', [$from, $to]);
                            } elseif ($from) {
                                $q->where('feedbacks.created_at', '>=', $from);
                            } else {
                                $q->where('feedbacks.created_at', '<=', $to);
                            }
                        };
                        $agentQuery
                            ->withCount(['feedbacks' => $constraint])
                            ->withAvg(['feedbacks' => $constraint], 'overall_rating');
                    } else {
                        $agentQuery
                            ->withCount('feedbacks')
                            ->withAvg('feedbacks', 'overall_rating');
                    }
                    if (! empty($agentIds)) {
                        $agentQuery->whereIn('id', $agentIds);
                    }
                    $agents = $agentQuery->orderBy('feedbacks_count', 'desc')->get();

                    // ── Render PDF ───────────────────────────────────────────
                    $pdf = Pdf::loadView('exports.dashboard-pdf', [
                        'periodLabel'     => $period,
                        'generatedAt'     => Carbon::now()->format('d M Y, h:i A'),
                        'totalFeedbacks'  => $totalFeedbacks,
                        'avgRating'       => $avgRating,
                        'satisfactionPct' => $satisfactionPct,
                        'recentAvg'       => $recentAvg,
                        'activeAgents'    => $activeAgents,
                        'activeQuestions' => $activeQuestions,
                        'trendData'       => $trendData,
                        'trendFrom'       => $trendFrom->format('d M Y'),
                        'trendTo'         => $trendTo->format('d M Y'),
                        'agents'          => $agents,
                    ])->setPaper('a4', 'portrait');

                    $slug     = $period ? '-' . str($period)->slug() : '';
                    $filename = 'dashboard-report' . $slug . '-' . Carbon::now()->format('Ymd') . '.pdf';

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        $filename,
                        ['Content-Type' => 'application/pdf'],
                    );
                }),
        ];
    }

    // ── Filter helpers ────────────────────────────────────────────────────────

    private function getDateRange(): ?array
    {
        $quarter = $this->filters['quarter'] ?? null;

        if ($quarter) {
            return $this->quarterDates($quarter);
        }

        $from = filled($this->filters['date_from'] ?? null)
            ? Carbon::parse($this->filters['date_from'])->startOfDay()
            : null;
        $to = filled($this->filters['date_to'] ?? null)
            ? Carbon::parse($this->filters['date_to'])->endOfDay()
            : null;

        return ($from || $to) ? [$from, $to] : null;
    }

    private function getAgentIds(): array
    {
        return $this->filters['agent_ids'] ?? [];
    }

    private function getPeriodLabel(): ?string
    {
        $quarter = $this->filters['quarter'] ?? null;

        if ($quarter) {
            $year = Carbon::now()->year;

            return match ($quarter) {
                'q1'    => "Q1 {$year} (Jan – Mar)",
                'q2'    => "Q2 {$year} (Apr – Jun)",
                'q3'    => "Q3 {$year} (Jul – Sep)",
                'q4'    => "Q4 {$year} (Oct – Dec)",
                default => null,
            };
        }

        $from = filled($this->filters['date_from'] ?? null) ? Carbon::parse($this->filters['date_from']) : null;
        $to   = filled($this->filters['date_to']   ?? null) ? Carbon::parse($this->filters['date_to'])   : null;

        return match (true) {
            $from && $to => $from->format('d M Y') . ' – ' . $to->format('d M Y'),
            (bool) $from => 'From ' . $from->format('d M Y'),
            (bool) $to   => 'Until ' . $to->format('d M Y'),
            default      => null,
        };
    }

    private function quarterDates(string $quarter): array
    {
        $year = Carbon::now()->year;

        return match ($quarter) {
            'q1'    => [Carbon::create($year, 1,  1)->startOfDay(), Carbon::create($year,  3, 31)->endOfDay()],
            'q2'    => [Carbon::create($year, 4,  1)->startOfDay(), Carbon::create($year,  6, 30)->endOfDay()],
            'q3'    => [Carbon::create($year, 7,  1)->startOfDay(), Carbon::create($year,  9, 30)->endOfDay()],
            'q4'    => [Carbon::create($year, 10, 1)->startOfDay(), Carbon::create($year, 12, 31)->endOfDay()],
            default => [],
        };
    }

    private function applyDateConstraint(\Illuminate\Database\Eloquent\Builder $query, ?array $range): void
    {
        if (! $range) {
            return;
        }

        [$from, $to] = $range;

        match (true) {
            (bool) $from && (bool) $to => $query->whereBetween('created_at', [$from, $to]),
            (bool) $from               => $query->where('created_at', '>=', $from),
            default                    => $query->where('created_at', '<=', $to),
        };
    }
}
