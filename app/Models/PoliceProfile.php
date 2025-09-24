<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoliceProfile extends Model
{
    use HasFactory;

    protected $table = 'police_profiles';

    protected $fillable = [
        'user_id',
        'municipality_id',
        'badge_number',
        'rank',
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
