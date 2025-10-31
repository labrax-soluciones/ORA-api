<?php

namespace App\Models;

use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model {
    use HasFactory, SoftDeletes;

    protected $table = 'vehicles';

    protected $fillable = [
        'user_id',
        'brand',
        'model',
        'color',
        'license_plate',
        'status',
        'year',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    // Relaciones
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Scopes útiles
    public function scopeActive($q) {
        return $q->where('status', VehicleStatus::Active->value);
    }
    public function scopeInactive($q) {
        return $q->where('status', VehicleStatus::Inactive->value);
    }
    public function scopeBlocked($q) {
        return $q->where('status', VehicleStatus::Blocked->value);
    }

    // Helper de presentación
    public function getDisplayNameAttribute(): string {
        $parts = array_filter([$this->brand, $this->model, $this->color]);
        $base = count($parts) ? implode(' ', $parts) : 'Vehículo';
        return trim($base . ' • ' . $this->license_plate);
    }
}
