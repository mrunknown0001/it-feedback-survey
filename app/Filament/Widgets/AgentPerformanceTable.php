<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\InteractsWithDashboardFilters;
use App\Models\Agent;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class AgentPerformanceTable extends BaseWidget
{
    use InteractsWithDashboardFilters;

    protected static ?string $heading = 'Agent Performance Overview';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Human-readable period label used in the PDF filename/header. */
    private function periodLabel(): ?string
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

    /**
     * Build the base agent query with date-constrained aggregates.
     * Agent-ID filtering is applied on top by callers.
     */
    private function buildAgentQuery(): Builder
    {
        $range        = $this->getDateRange();
        $issueTypeIds = $this->getIssueTypeIds();
        $query        = Agent::query();

        $constraint = function ($q) use ($range, $issueTypeIds): void {
            if ($range) {
                [$from, $to] = $range;
                if ($from && $to) {
                    $q->whereBetween('feedbacks.created_at', [$from, $to]);
                } elseif ($from) {
                    $q->where('feedbacks.created_at', '>=', $from);
                } else {
                    $q->where('feedbacks.created_at', '<=', $to);
                }
            }
            if (! empty($issueTypeIds)) {
                $q->whereIn('feedbacks.issue_type_id', $issueTypeIds);
            }
        };

        return $query
            ->withCount(['feedbacks' => $constraint])
            ->withAvg(['feedbacks' => $constraint], 'overall_rating');
    }

    // -------------------------------------------------------------------------
    // Table definition
    // -------------------------------------------------------------------------

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $agentIds = $this->getAgentIds();

                return $this->buildAgentQuery()
                    ->when(! empty($agentIds), fn ($q) => $q->whereIn('id', $agentIds));
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Agent')
                    ->searchable(),

                Tables\Columns\TextColumn::make('department'),

                Tables\Columns\TextColumn::make('feedbacks_count')
                    ->label('Total Feedback')
                    ->sortable(),

                Tables\Columns\TextColumn::make('avg_rating')
                    ->label('Avg. Rating')
                    ->getStateUsing(fn (Agent $record) => number_format($record->feedbacks_avg_overall_rating ?? 0, 2) . ' / 5.00')
                    ->badge()
                    ->color(fn (Agent $record) => match (true) {
                        ($record->feedbacks_avg_overall_rating ?? 0) >= 4.5 => 'success',
                        ($record->feedbacks_avg_overall_rating ?? 0) >= 3   => 'warning',
                        ($record->feedbacks_avg_overall_rating ?? 0) > 0    => 'danger',
                        default                                              => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function () {
                        $agentIds = $this->getAgentIds();

                        $agents = $this->buildAgentQuery()
                            ->when(! empty($agentIds), fn ($q) => $q->whereIn('id', $agentIds))
                            ->orderBy('feedbacks_count', 'desc')
                            ->get();

                        $periodLabel = $this->periodLabel();

                        $pdf = Pdf::loadView('exports.agent-performance-pdf', [
                            'agents'      => $agents,
                            'periodLabel' => $periodLabel,
                            'generatedAt' => Carbon::now()->format('d M Y, h:i A'),
                        ])->setPaper('a4', 'landscape');

                        $slug     = $periodLabel ? '-' . str($periodLabel)->slug() : '';
                        $filename = 'agent-performance' . $slug . '-' . Carbon::now()->format('Ymd') . '.pdf';

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            $filename,
                            ['Content-Type' => 'application/pdf'],
                        );
                    }),
            ])
            ->defaultSort('feedbacks_count', 'desc')
            ->paginated([25, 50, 100]);
    }
}
