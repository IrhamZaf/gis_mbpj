<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Role helpers ────────────────────────────────────
    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isSurveyor(): bool
    {
        return $this->role === 'surveyor';
    }

    public function isEngineer(): bool
    {
        return $this->role === 'engineer';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'Superadmin',
            'surveyor'   => 'Admin Surveyor',
            'engineer'   => 'Engineer MBPJ',
            default      => ucfirst($this->role),
        };
    }

    // ── Relationships ───────────────────────────────────
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}

