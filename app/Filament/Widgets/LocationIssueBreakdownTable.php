<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\InteractsWithDashboardFilters;
use App\Models\Location;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\View\View;

class LocationIssueBreakdownTable extends BaseWidget
{
    use InteractsWithDashboardFilters;

    protected static ?string $heading = 'Issues by Location / Area / Department';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function render(): View
    {
        if (! isset($this->table)) {
            $this->bootedInteractsWithTable();
        }

        return parent::render();
    }

    public function getTable(): Table
    {
        if (! isset($this->table)) {
            $this->bootedInteractsWithTable();
        }

        return parent::getTable();
    }

    public function table(Table $table): Table
    {
        $range        = $this->getDateRange();
        $agentIds     = $this->getAgentIds();
        $issueTypeIds = $this->getIssueTypeIds();
        $locationIds  = $this->getLocationIds();

        $applyConstraint = function ($q) use ($range, $agentIds, $issueTypeIds): void {
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
            if (! empty($agentIds)) {
                $q->whereHas('agents', fn ($aq) => $aq->whereIn('agents.id', $agentIds));
            }
            if (! empty($issueTypeIds)) {
                $q->whereIn('feedbacks.issue_type_id', $issueTypeIds);
            }
        };

        return $table
            ->query(
                Location::query()
                    ->when(! empty($locationIds), fn ($q) => $q->whereIn('id', $locationIds))
                    ->withCount(['feedbacks' => $applyConstraint])
                    ->withAvg(['feedbacks' => $applyConstraint], 'overall_rating')
                    ->having('feedbacks_count', '>', 0)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Location / Area / Department')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('feedbacks_count')
                    ->label('Total Issues')
                    ->sortable(),

                Tables\Columns\TextColumn::make('feedbacks_avg_overall_rating')
                    ->label('Avg. Rating')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' / 5.00' : '—')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 3   => 'warning',
                        $state > 0    => 'danger',
                        default       => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('satisfaction_rate')
                    ->label('Satisfaction Rate')
                    ->getStateUsing(function (Location $record) use ($range, $agentIds, $issueTypeIds) {
                        $q = $record->feedbacks();
                        if ($range) {
                            [$from, $to] = $range;
                            if ($from && $to) {
                                $q->whereBetween('created_at', [$from, $to]);
                            } elseif ($from) {
                                $q->where('created_at', '>=', $from);
                            } else {
                                $q->where('created_at', '<=', $to);
                            }
                        }
                        if (! empty($agentIds)) {
                            $q->whereHas('agents', fn ($aq) => $aq->whereIn('agents.id', $agentIds));
                        }
                        if (! empty($issueTypeIds)) {
                            $q->whereIn('issue_type_id', $issueTypeIds);
                        }
                        $total = (clone $q)->count();
                        if ($total === 0) return '—';
                        $high = (clone $q)->where('overall_rating', '>=', 4)->count();
                        return round(($high / $total) * 100) . '%';
                    }),
            ])
            ->defaultSort('feedbacks_count', 'desc')
            ->paginated([10, 25, 50]);
    }
}
