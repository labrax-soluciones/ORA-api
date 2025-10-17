<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkingZone extends Model
{
    protected $fillable = [
        'municipality_id',
        'parking_zone_type_id',
        'name',
        'slug',
        'description',
        'capacity',
        'status',
        'geometry',
        'metadata',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'geometry' => 'array',
        'metadata' => 'array',
    ];

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ParkingZoneType::class, 'parking_zone_type_id');
    }
}
