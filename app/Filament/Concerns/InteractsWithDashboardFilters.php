<?php

namespace App\Filament\Concerns;

use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithDashboardFilters
{
    use InteractsWithPageFilters;

    protected function getDateRange(): ?array
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

    protected function getAgentIds(): array
    {
        return $this->filters['agent_ids'] ?? [];
    }

    protected function getIssueTypeIds(): array
    {
        return $this->filters['issue_type_ids'] ?? [];
    }

    protected function getLocationIds(): array
    {
        return $this->filters['location_ids'] ?? [];
    }

    protected function quarterDates(string $quarter): array
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

    protected function applyDateConstraint(Builder $query, ?array $range, string $column = 'created_at'): Builder
    {
        if (! $range) {
            return $query;
        }

        [$from, $to] = $range;

        return match (true) {
            (bool) $from && (bool) $to => $query->whereBetween($column, [$from, $to]),
            (bool) $from               => $query->where($column, '>=', $from),
            default                    => $query->where($column, '<=', $to),
        };
    }
}
