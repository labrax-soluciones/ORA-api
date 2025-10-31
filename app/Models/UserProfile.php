<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model {
    use HasFactory;

    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'address_line1',
        'address_line2',
        'city',
        'province',
        'postal_code',
        'country',
        'date_of_birth',
        'secondary_phone',
        'meta',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'meta' => 'array',
    ];

    // Relaciones
    public function user() {
        return $this->belongsTo(User::class);
    }
}
