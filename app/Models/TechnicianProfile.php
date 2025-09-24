<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianProfile extends Model
{
    use HasFactory;

    protected $table = 'technician_profiles';

    protected $fillable = [
        'user_id',
        'municipality_id',
        'department',
        'position',
        'phone',
        'id_document',
        'avatar_path',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }
}
