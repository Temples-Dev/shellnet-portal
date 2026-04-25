<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    /**
     * Supported plan delivery types.
     *
     * @var list<string>
     */
    public const TYPES = ['Hotspot', 'PPPoE', 'Balance', 'VPN'];

    /**
     * Supported validity period units.
     *
     * @var list<string>
     */
    public const VALIDITY_UNITS = ['Mins', 'Hrs', 'Days', 'Months', 'Period'];

    /**
     * Supported data/time limit modes.
     *
     * @var list<string>
     */
    public const LIMIT_TYPES = ['Time_Limit', 'Data_Limit', 'Both_Limit'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'bandwidth_id',
        'price',
        'price_old',
        'type',
        'is_limited',
        'limit_type',
        'time_limit',
        'time_unit',
        'data_limit',
        'data_unit',
        'validity',
        'validity_unit',
        'shared_users',
        'router_id',
        'is_radius',
        'pool',
        'expired_date',
        'enabled',
        'prepaid',
        'plan_type',
        'device',
        'on_login',
        'on_logout',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price'      => 'decimal:2',
        'price_old'  => 'decimal:2',
        'is_limited' => 'boolean',
        'is_radius'  => 'boolean',
        'enabled'    => 'boolean',
        'prepaid'    => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Limit the query to enabled plans.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * Limit the query to plans of a specific type (e.g. 'Hotspot', 'PPPoE').
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Price formatted with two decimal places, e.g. "29.99".
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 2);
    }

    /**
     * Human-readable validity label, e.g. "30 Days".
     */
    public function getValidityLabelAttribute(): string
    {
        return "{$this->validity} {$this->validity_unit}";
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The bandwidth profile attached to this plan.
     */
    public function bandwidth(): BelongsTo
    {
        return $this->belongsTo(Bandwidth::class);
    }

    /**
     * The router that delivers this plan.
     */
    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    /**
     * Subscriptions purchased under this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Vouchers generated for this plan.
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    /**
     * Payment transactions made for this plan.
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
