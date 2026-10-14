<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    // -------------------------------------------------------------------------
    // Enum-style constants
    // -------------------------------------------------------------------------

    public const USER_TYPES = ['SuperAdmin', 'Admin', 'Report', 'Agent', 'Sales'];

    public const STATUSES = ['Active', 'Inactive'];

    // -------------------------------------------------------------------------
    // Mass-assignment / hidden
    // -------------------------------------------------------------------------

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'photo',
        'phone',
        'city',
        'subdistrict',
        'ward',
        'user_type',
        'status',
        'data',
        'last_login',
        'login_token',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'login_token',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'last_login' => 'datetime',
            'data' => 'array',
            'password' => 'hashed',
        ];
    }

    // -------------------------------------------------------------------------
    // Role helpers
    // -------------------------------------------------------------------------

    public function isSuperAdmin(): bool
    {
        return $this->user_type === 'SuperAdmin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->user_type, ['SuperAdmin', 'Admin'], true);
    }

    public function canManageNetwork(): bool
    {
        return in_array($this->user_type, ['SuperAdmin', 'Admin'], true);
    }

    public function canViewReports(): bool
    {
        return in_array($this->user_type, ['SuperAdmin', 'Admin', 'Report'], true);
    }

    // -------------------------------------------------------------------------
    // Accessors / helpers
    // -------------------------------------------------------------------------

    /**
     * Get the user's initials.
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn (string $word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'created_by');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'admin_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'admin_id');
    }

    public function vouchersGenerated(): HasMany
    {
        return $this->hasMany(Voucher::class, 'generated_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CustomerMessage::class, 'admin_id');
    }
}
