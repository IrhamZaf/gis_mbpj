<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'unit_id',
        'phone',
        'status',
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

    public function isTa(): bool
    {
        return $this->role === 'ta';
    }

    public function isDirector(): bool
    {
        return $this->role === 'director';
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'Superadmin',
            'surveyor'   => 'Surveyor / Vendor',
            'engineer'   => 'Engineer MBSJ',
            'ta'         => 'TA (Pembantu Teknik)',
            'director'   => 'Pengarah',
            default      => ucfirst($this->role),
        };
    }

    public function getDefaultDesignationAttribute(): string
    {
        return match ($this->role) {
            'ta'       => 'Pembantu Teknik',
            'engineer' => 'Jurutera',
            'director' => 'Pengarah Kejuruteraan',
            'surveyor' => 'Surveyor',
            default    => $this->role_label,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->isActive()
            ? '<span class="badge bg-label-success">Aktif</span>'
            : '<span class="badge bg-label-secondary">Nyahaktif</span>';
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function requiresUnit(): bool
    {
        return in_array($this->role, ['surveyor', 'engineer', 'ta'], true);
    }
}
