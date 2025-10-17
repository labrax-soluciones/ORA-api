<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkingZoneTypeSchedule extends Model
{
    protected $table = 'parking_zone_type_schedules';

    protected $fillable = [
        'parking_zone_type_id',
        'day_of_week',
        'start_time',
        'end_time',
        'timezone',
        'settings',
        'is_holiday',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'settings' => 'array',
        'is_holiday' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ParkingZoneType::class, 'parking_zone_type_id');
    }
}

