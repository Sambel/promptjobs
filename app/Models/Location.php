<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'type',
        'region_parent',
        'timezone_based',
    ];

    protected $casts = [
        'timezone_based' => 'boolean',
    ];

    /**
     * Jobs that have this location
     */
    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'job_location');
    }

    /**
     * Scope to get only countries
     */
    public function scopeCountries($query)
    {
        return $query->where('type', 'country');
    }

    /**
     * Scope to get only regions
     */
    public function scopeRegions($query)
    {
        return $query->where('type', 'region');
    }

    /**
     * Scope to get locations by region
     */
    public function scopeInRegion($query, string $region)
    {
        return $query->where('region_parent', $region);
    }
}
