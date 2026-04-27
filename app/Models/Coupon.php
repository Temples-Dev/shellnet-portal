<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'customer_id',
        'type',
        'value',
        'description',
        'max_usage',
        'usage_count',
        'status',
        'min_order_amount',
        'max_discount_amount',
        'starts_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value'               => 'decimal:2',
        'min_order_amount'    => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'starts_at'           => 'date',
        'expires_at'          => 'date',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope a query to coupons that are currently active and within their
     * valid date window.
     */
    public function scopeActive(Builder $query): Builder
    {
        $today = now()->toDateString();
        $now   = now()->toDateTimeString();

        return $query->where('status', 'active')
                     ->where(function (Builder $q) use ($now): void {
                         $q->whereNull('starts_at')
                           ->orWhere('starts_at', '<=', $now);
                     })
                     ->where(function (Builder $q) use ($today): void {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>=', $today);
                     });
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Determine whether this coupon is currently valid for use.
     *
     * A coupon is valid when:
     *  - status is 'active'
     *  - today falls within the optional starts_at / expires_at window
     *  - usage_count has not yet reached max_usage (or max_usage is unlimited)
     */
    public function getIsValidAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->starts_at && $this->starts_at->gt($today)) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->lt($today)) {
            return false;
        }

        if ($this->max_usage !== null && $this->usage_count >= $this->max_usage) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the coupon's validity period has passed.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    /**
     * Calculate the discount amount for a given order amount.
     *
     * Supports two coupon types:
     *  - 'percent': a percentage off the order amount
     *  - 'fixed'  : a flat amount off the order amount
     *
     * The result is capped by max_discount_amount when set, and will never
     * exceed the original order amount.
     *
     * @param  float  $amount  The order amount before discount.
     * @return float           The discount amount to deduct.
     */
    public function calculateDiscount(float $amount): float
    {
        if ($this->type === 'percent') {
            $discount = $amount * ((float) $this->value / 100);
        } else {
            // fixed
            $discount = (float) $this->value;
        }

        // Apply the max discount cap when configured.
        if ($this->max_discount_amount !== null && $this->max_discount_amount > 0) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }

        // A discount can never exceed the order amount itself.
        return min($discount, $amount);
    }
}
