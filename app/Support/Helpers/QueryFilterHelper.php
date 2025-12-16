<?php

namespace App\Support\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QueryFilterHelper
{
    /**
     * Apply a set of filter functions based on attribute configuration.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $config
     * @return Builder
     */
    public static function applyFilters(Builder $query, Request $request, array $config)
    {
        $query = self::filterWhereEqual($request, $query, $config['whereEqual'] ?? []);
        $query = self::filterWhereIn($request, $query, $config['whereIn'] ?? []);
        $query = self::filterWhereInAmbigious($request, $query, $config['whereInAmbigious'] ?? []);
        $query = self::filterDate($request, $query, $config['date'] ?? []);
        $query = self::filterLike($request, $query, $config['like'] ?? []);
        $query = self::filterDateRange($request, $query, $config['dateRange'] ?? []);
        $query = self::filterBelongsToManyRelation($request, $query, $config['belongsToManyRelation'] ?? []);

        $query = self::filterRelationEqual($request, $query, $config['relationEqual'] ?? []);
        $query = self::filterRelationIn($request, $query, $config['relationIn'] ?? []);
        $query = self::filterRelationLike($request, $query, $config['relationLike'] ?? []);
        $query = self::filterRelationDateRange($request, $query, $config['relationDateRange'] ?? []);

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Record based filters
    |--------------------------------------------------------------------------
    */

    public static function filterWhereEqual(Request $request, Builder $query, array $attributes): Builder
    {
        foreach ($attributes as $attribute) {
            if ($request->filled($attribute)) {
                $query->where($attribute, $request->input($attribute));
            }
        }
        return $query;
    }

    public static function filterWhereIn(Request $request, Builder $query, array $attributes): Builder
    {
        foreach ($attributes as $attribute) {
            if ($request->filled($attribute)) {
                $query->whereIn($attribute, $request->input($attribute));
            }
        }
        return $query;
    }

    public static function filterWhereInAmbigious(Request $request, Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter) {
            if ($request->filled($filter['inputName'])) {
                $query->whereIn($filter['tableName'] . '.' . $filter['inputName'], $request->input($filter['inputName']));
            }
        }
        return $query;
    }

    public static function filterDate(Request $request, Builder $query, array $attributes): Builder
    {
        foreach ($attributes as $attribute) {
            if ($request->filled($attribute)) {
                $query->whereDate($attribute, $request->input($attribute));
            }
        }
        return $query;
    }

    public static function filterLike(Request $request, Builder $query, array $attributes): Builder
    {
        foreach ($attributes as $attribute) {
            if ($request->filled($attribute)) {
                $query->where($attribute, 'LIKE', '%' . $request->input($attribute) . '%');
            }
        }
        return $query;
    }

    /**
     * Apply date range filters to a query.
     *
     * Example input:
     *   created_at = "2024-01-01 - 2025-01-01"
     *
     * @param  Request $request
     * @param  Builder $query
     * @param  array   $attributes  List of date attributes (e.g. ['created_at', 'updated_at'])
     * @return Builder
     */
    public static function filterDateRange(Request $request, Builder $query, array $attributes): Builder
    {
        foreach ($attributes as $attribute) {
            if ($request->filled($attribute)) {
                [$fromDate, $toDate] = explode(' - ', $request->input($attribute));

                $fromDate = Carbon::createFromFormat('Y-m-d', trim($fromDate))->startOfDay();
                $toDate   = Carbon::createFromFormat('Y-m-d', trim($toDate))->endOfDay();

                $query->whereBetween($attribute, [$fromDate, $toDate]);
            }
        }

        return $query;
    }

    public static function filterBelongsToManyRelation(Request $request, Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter) {
            if ($request->filled($filter['inputName'])) {
                $query->whereHas($filter['relationName'], function ($q) use ($request, $filter) {
                    $q->whereIn("{$filter['relationTable']}.id", $request->input($filter['inputName']));
                });
            }
        }
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Relation based filters
    |--------------------------------------------------------------------------
    */

    public static function filterRelationEqual(Request $request, Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter) {
            if ($request->filled($filter['inputName'])) {
                $query->whereHas($filter['relationName'], function ($q) use ($request, $filter) {
                    $q->where($filter['relationAttribute'], $request->input($filter['inputName']));
                });
            }
        }
        return $query;
    }

    public static function filterRelationIn(Request $request, Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter) {
            if ($request->filled($filter['inputName'])) {
                $query->whereHas($filter['relationName'], function ($q) use ($request, $filter) {
                    $q->whereIn($filter['relationAttribute'], $request->input($filter['inputName']));
                });
            }
        }
        return $query;
    }

    public static function filterRelationLike(Request $request, Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter) {
            if ($request->filled($filter['inputName'])) {
                $query->whereHas($filter['relationName'], function ($q) use ($request, $filter) {
                    $q->where($filter['relationAttribute'], 'LIKE', '%' . $request->input($filter['inputName']) . '%');
                });
            }
        }
        return $query;
    }

    public static function filterRelationDateRange(Request $request, Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter) {
            if ($request->filled($filter['inputName'])) {
                [$fromDate, $toDate] = explode(' - ', $request->input($filter['inputName']));

                $fromDate = Carbon::createFromFormat('Y-m-d', trim($fromDate))->startOfDay();
                $toDate   = Carbon::createFromFormat('Y-m-d', trim($toDate))->endOfDay();

                $query->whereHas($filter['relationName'], function ($q) use ($fromDate, $toDate, $filter) {
                    $q->whereBetween($filter['relationAttribute'], [$fromDate, $toDate]);
                });
            }
        }

        return $query;
    }
}
