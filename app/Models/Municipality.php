<?php

namespace App\Models;

use App\Enums\MunicipalityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Municipality extends Model {
    use HasFactory, SoftDeletes;

    // Campos asignables en masa
    protected $fillable = [
        'name',
        'slug',
        'timezone',
        'default_locale',
        'locales',
        'sso_domains',
        'contact_email',
        'contact_phone',
        'status',
        'settings',
    ];

    // Casts a tipos nativos/enum
    protected $casts = [
        'locales'     => 'array',
        'sso_domains' => 'array',
        'settings'    => 'array',
        'status'      => MunicipalityStatus::class,
    ];

    // Relación con usuarios (asociación municipal)
    public function users() {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
