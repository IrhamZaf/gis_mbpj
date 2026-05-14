<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public const ROLE_ADMIN = 'admin';

    public const ROLE_SURVEYOR = 'surveyor';

    public const ROLE_ENGINEER = 'engineer';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function reportedIncidents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Incident::class, 'reported_by');
    }

    public function assignedIncidents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Incident::class, 'assigned_engineer');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSurveyor(): bool
    {
        return $this->role === self::ROLE_SURVEYOR;
    }

    public function isEngineer(): bool
    {
        return $this->role === self::ROLE_ENGINEER;
    }
}
