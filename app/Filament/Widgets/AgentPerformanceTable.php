<?php

namespace App\Filament\Widgets;

use App\Models\Agent;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class AgentPerformanceTable extends BaseWidget
{
    protected static ?string $heading = 'Agent Performance Overview';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function getQuarterDates(string $quarter): array
    {
        $year = Carbon::now()->year;

        return match ($quarter) {
            'q1'    => [Carbon::create($year, 1, 1)->startOfDay(),  Carbon::create($year, 3, 31)->endOfDay()],
            'q2'    => [Carbon::create($year, 4, 1)->startOfDay(),  Carbon::create($year, 6, 30)->endOfDay()],
            'q3'    => [Carbon::create($year, 7, 1)->startOfDay(),  Carbon::create($year, 9, 30)->endOfDay()],
            'q4'    => [Carbon::create($year, 10, 1)->startOfDay(), Carbon::create($year, 12, 31)->endOfDay()],
            default => [],
        };
    }

    private function quarterOptions(): array
    {
        $year = Carbon::now()->year;

        return [
            'q1' => "Q1 {$year} (Jan – Mar)",
            'q2' => "Q2 {$year} (Apr – Jun)",
            'q3' => "Q3 {$year} (Jul – Sep)",
            'q4' => "Q4 {$year} (Oct – Dec)",
        ];
    }

    /**
     * Returns [from, to] where either can be null (open-ended range).
     * Returns null when no date filter is active at all.
     */
    private function selectedDateRange(): ?array
    {
        $f       = $this->tableFilters['date_range'] ?? [];
        $quarter = $f['quarter'] ?? null;

        if ($quarter) {
            return $this->getQuarterDates($quarter);
        }

        $from = filled($f['date_from'] ?? null) ? Carbon::parse($f['date_from'])->startOfDay() : null;
        $to   = filled($f['date_to']   ?? null) ? Carbon::parse($f['date_to'])->endOfDay()     : null;

        return ($from || $to) ? [$from, $to] : null;
    }

    /** Human-readable period label used in PDF and filter chips. */
    private function periodLabel(): ?string
    {
        $f       = $this->tableFilters['date_range'] ?? [];
        $quarter = $f['quarter'] ?? null;

        if ($quarter) {
            return $this->quarterOptions()[$quarter] ?? null;
        }

        $from = filled($f['date_from'] ?? null) ? Carbon::parse($f['date_from']) : null;
        $to   = filled($f['date_to']   ?? null) ? Carbon::parse($f['date_to'])   : null;

        return match (true) {
            $from && $to => $from->format('d M Y') . ' – ' . $to->format('d M Y'),
            (bool) $from => 'From ' . $from->format('d M Y'),
            (bool) $to   => 'Until ' . $to->format('d M Y'),
            default      => null,
        };
    }

    /**
     * Build the base agent query with date-constrained aggregates.
     * Agent-ID filtering is left out so callers (e.g. PDF export) can apply it independently.
     */
    private function buildAgentQuery(): Builder
    {
        $range = $this->selectedDateRange();
        $query = Agent::query();

        if ($range) {
            [$from, $to] = $range;

            $constraint = function ($q) use ($from, $to): void {
                if ($from && $to) {
                    $q->whereBetween('feedbacks.created_at', [$from, $to]);
                } elseif ($from) {
                    $q->where('feedbacks.created_at', '>=', $from);
                } else {
                    $q->where('feedbacks.created_at', '<=', $to);
                }
            };

            $query
                ->withCount(['feedbacks' => $constraint])
                ->withAvg(['feedbacks' => $constraint], 'overall_rating');
        } else {
            $query
                ->withCount('feedbacks')
                ->withAvg('feedbacks', 'overall_rating');
        }

        return $query;
    }

    // -------------------------------------------------------------------------
    // Table definition
    // -------------------------------------------------------------------------

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->buildAgentQuery())
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
            ->filters([
                // -----------------------------------------------------------------
                // Date range filter — quarter shortcuts (current year) OR custom range
                // Selecting one side clears the other.
                // -----------------------------------------------------------------
                Filter::make('date_range')
                    ->label('Date Range')
                    ->form([
                        Select::make('quarter')
                            ->label('Quarter (current year)')
                            ->placeholder('Select quarter…')
                            ->options(fn () => $this->quarterOptions())
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
                    ])
                    // All date logic is handled in buildAgentQuery() via the query closure;
                    // this no-op prevents Filament from trying to apply its own WHERE clause.
                    ->modifyQueryUsing(fn (Builder $query) => $query)
                    ->indicateUsing(function (array $data): array {
                        $label = null;

                        if (filled($data['quarter'] ?? null)) {
                            $label = $this->quarterOptions()[$data['quarter']] ?? null;
                        } elseif (filled($data['date_from'] ?? null) || filled($data['date_to'] ?? null)) {
                            $from = filled($data['date_from'] ?? null) ? Carbon::parse($data['date_from'])->format('d M Y') : null;
                            $to   = filled($data['date_to']   ?? null) ? Carbon::parse($data['date_to'])->format('d M Y')   : null;

                            $label = match (true) {
                                $from && $to => "{$from} – {$to}",
                                (bool) $from => "From {$from}",
                                default      => "Until {$to}",
                            };
                        }

                        return $label ? [Indicator::make($label)->removeField('quarter')] : [];
                    }),

                // -----------------------------------------------------------------
                // Agent filter
                // -----------------------------------------------------------------
                SelectFilter::make('agent')
                    ->label('Agent')
                    ->options(fn () => Agent::orderBy('name')->pluck('name', 'id')->toArray())
                    ->modifyQueryUsing(fn (Builder $query, array $data) =>
                        filled($data['value']) ? $query->where('id', $data['value']) : $query
                    ),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function () {
                        $agentFilter = $this->tableFilters['agent']['value'] ?? null;

                        $agents = $this->buildAgentQuery()
                            ->when(filled($agentFilter), fn ($q) => $q->where('id', $agentFilter))
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
            ->defaultSort('feedbacks_count', 'desc');
    }
}
