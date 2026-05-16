<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public const ROLE_ADMIN = 'admin';

    /** @deprecated Dijaga untuk akaun lama sahaja — operasi MBPJ guna surveyor dilantik (vendor). */
    public const ROLE_SURVEYOR = 'surveyor';

    public const ROLE_ENGINEER = 'engineer';

    /** Akaun surveyor dilantik (luar / kontraktor). */
    public const ROLE_VENDOR = 'vendor';

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

    public function reportedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'reported_by');
    }

    public function assignedIncidents(): HasMany
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

    public function isVendor(): bool
    {
        return $this->role === self::ROLE_VENDOR;
    }

    /** Surveyor dilantik MBPJ (peranan `vendor`). */
    public function isExternalSurveyor(): bool
    {
        return $this->isVendor();
    }

    public function canCreateIncidents(): bool
    {
        return $this->isAdmin() || $this->isEngineer() || $this->isVendor();
    }

    /** Muat naik / senarai hantaran survey (bukan jurutera semak). */
    public function canAccessSurveyUploadModule(): bool
    {
        return $this->isAdmin() || $this->isVendor();
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_ENGINEER => 'Jurutera',
            self::ROLE_VENDOR => 'Surveyor dilantik',
            self::ROLE_SURVEYOR => 'Surveyor MBPJ (legasi)',
            default => (string) $this->role,
        };
    }
}
