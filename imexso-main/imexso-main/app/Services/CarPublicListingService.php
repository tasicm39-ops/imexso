<?php

namespace App\Services;

use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;

/**
 * Deduplicates public car list responses: one row per
 * (make, model, trim_level, fuel_type, professional_price, mileage, color), keeping the newest row by {@code created_at} then {@code id}.
 *
 * Optional index for large {@code cars} tables (add in your own migration if needed):
 * {@code (make, model, trim_level, fuel_type, professional_price, mileage, color, created_at, id)}
 */
final class CarPublicListingService
{
    /**
     * @param  Builder<Car>  $filteredQuery  Filters and country exclusion already applied; no eager loads or user sort order.
     * @return Builder<Car>
     */
    public function queryCanonicalCars(Builder $filteredQuery): Builder
    {
        $model = $filteredQuery->getModel();
        $table = $model->getTable();

        $ranked = $filteredQuery->clone();
        $ranked->getQuery()->columns = null;
        $ranked->selectRaw(
            "{$table}.id, ROW_NUMBER() OVER (
                PARTITION BY {$table}.make, {$table}.model, {$table}.trim_level, {$table}.fuel_type, {$table}.professional_price, {$table}.mileage, {$table}.color
                ORDER BY {$table}.created_at DESC, {$table}.id DESC
            ) as listing_rn"
        );

        return Car::query()
            ->whereIn("{$table}.id", function ($sub) use ($ranked) {
                $sub->fromSub($ranked->getQuery(), 'ranked_listings')
                    ->where('listing_rn', 1)
                    ->select('id');
            });
    }
}
