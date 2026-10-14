<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_transactions';

    // -------------------------------------------------------------------------
    // Status constants
    // -------------------------------------------------------------------------

    public const STATUS_UNPAID = 1;

    public const STATUS_PAID = 2;

    public const STATUS_FAILED = 3;

    public const STATUS_CANCELED = 4;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'username',
        'gateway',
        'gateway_trx_id',
        'plan_id',
        'plan_name',
        'router_id',
        'router_name',
        'price',
        'payment_method',
        'payment_channel',
        'pg_url',
        'pg_request',
        'pg_response',
        'reference',
        'invoice',
        'coupon_id',
        'discount_amount',
        'original_price',
        'currency',
        'status',
        'expired_at',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'original_price' => 'decimal:2',
        'pg_request' => 'array',
        'pg_response' => 'array',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope a query to only unpaid (pending) payment transactions.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_UNPAID);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Get a human-readable label for the current status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ((int) $this->status) {
            self::STATUS_PAID => 'Paid',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELED => 'Canceled',
            default => 'Unpaid',
        };
    }

    /**
     * Determine whether the payment has been completed successfully.
     */
    public function getIsPaidAttribute(): bool
    {
        return (int) $this->status === self::STATUS_PAID;
    }

    /**
     * Determine whether the payment window has expired without payment.
     */
    public function getIsExpiredAttribute(): bool
    {
        return (int) $this->status === self::STATUS_UNPAID
            && $this->expired_at
            && $this->expired_at->isPast();
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

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'invoice', 'invoice');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
