<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MunicipalAdminProfile extends Model {
    use HasFactory;

    protected $table = 'municipal_admin_profiles';

    protected $fillable = [
        'user_id',
        'municipality_id',
        'phone',
        'id_document',
        'avatar_path',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function municipality() {
        return $this->belongsTo(Municipality::class);
    }
}
