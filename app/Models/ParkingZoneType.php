<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkingZoneType extends Model
{
    protected $fillable = [
        'municipality_id',
        'name',
        'slug',
        'color_hex',
        'max_stay_minutes',
        'outside_schedule_policy',
        'status',
        'settings',
    ];

    protected $casts = [
        'max_stay_minutes' => 'integer',
        'settings' => 'array',
    ];

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(ParkingZone::class, 'parking_zone_type_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ParkingZoneTypeSchedule::class);
    }
}
