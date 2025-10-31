<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email

 * @mixin \Eloquent
 */
class User extends Authenticatable implements JWTSubject {
    use HasFactory, Notifiable, HasRoles;

    protected string $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',

        'first_name',
        'last_name',
        'phone',
        'id_document',
        'avatar_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted() {
        static::saving(function ($user) {
            $full = trim(trim((string)$user->first_name) . ' ' . trim((string)$user->last_name));
            if ($full !== '') {
                $user->name = $full;
            }
        });
    }


    public function getJWTIdentifier() {
        return $this->getKey(); // Generalmente el ID
    }

    public function getJWTCustomClaims() {
        return []; // Puedes añadir claims personalizados si quieres
    }

    public function technicianProfile() {
        return $this->hasOne(TechnicianProfile::class);
    }
    public function policeProfile() {
        return $this->hasOne(PoliceProfile::class);
    }
    public function municipalAdminProfile() {
        return $this->hasOne(MunicipalAdminProfile::class);
    }


    public function profile() {
        return $this->hasOne(UserProfile::class);
    }

    public function vehicles() {
        return $this->hasMany(Vehicle::class);
    }


    public function getFullNameAttribute(): string {
        $full = trim(trim((string)$this->first_name) . ' ' . trim((string)$this->last_name));
        return $full !== '' ? $full : (string)($this->name ?? '');
    }
}
