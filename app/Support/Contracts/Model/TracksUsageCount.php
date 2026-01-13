<?php

namespace App\Support\Contracts\Model;

use Illuminate\Database\Eloquent\Builder;

/**
 * Interface for models that require tracking usage counts.
 *
 * Important: All models used in 'MiscModelController' must implement this interface!
 */
interface TracksUsageCount
{
    /**
     * Scope: Eagerly load all related model counts for optimization.
     */
    public function scopeWithRelatedUsageCounts($query): Builder;

    /**
     * Get the total usage count by summing all related model references.
     */
    public function getUsageCountAttribute(): int;
}
