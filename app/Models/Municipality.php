<?php

namespace App\Models;

use App\Enums\MunicipalityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Municipality extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'contact_email',
        'contact_phone',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'status'   => MunicipalityStatus::class,
    ];

    public function users() {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
