<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;
    /**
     * Valid subscription statuses.
     */
    public const STATUSES = ['on', 'off', 'expired'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'plan_id',
        'plan_name',
        'router_id',
        'router_name',
        'type',
        'method',
        'status',
        'recharged_at',
        'expires_at',
        'admin_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'recharged_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope a query to only include active (on) subscriptions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'on');
    }

    /**
     * Scope a query to subscriptions that have passed their expiry date
     * but are still marked as active.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now())
                     ->where('status', 'on');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Determine whether the subscription is currently active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'on';
    }

    /**
     * Determine whether the subscription has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at
            && $this->expires_at->isPast()
            && $this->status === 'on';
    }

    /**
     * Get the number of days remaining until expiry, or null if no date is set.
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        $diff = (int) now()->diffInDays($this->expires_at, false);

        return $diff >= 0 ? $diff : 0;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
