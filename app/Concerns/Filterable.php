<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Get searchable columns.
     *
     * @return array<int, string>
     */
    public function searchable(): array
    {
        return ['name', 'code'];
    }

    /**
     * Get filterable columns.
     *
     * @return array<int, string>
     */
    public function filterable(): array
    {
        return ['unit_id', 'fiscal_year_id', 'status'];
    }

    /**
     * Apply filters to the query.
     *
     * @param  Builder<static>  $query
     * @param  array<string, mixed>  $filters
     * @param  array<string>  $searchableColumns
     * @return Builder<static>
     */
    public function scopeFilter(Builder $query, array $filters = [], array $searchableColumns = []): Builder
    {
        $searchColumns = ! empty($searchableColumns) ? $searchableColumns : $this->searchable();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $index => $column) {
                    if ($index === 0) {
                        $q->where($column, 'like', "%{$search}%");
                    } else {
                        $q->orWhere($column, 'like', "%{$search}%");
                    }
                }
            });
        }

        $exactFilters = $this->filterable();

        foreach ($exactFilters as $filterKey) {
            if (! empty($filters[$filterKey])) {
                $query->where($filterKey, $filters[$filterKey]);
            }
        }

        return $query;
    }
}
