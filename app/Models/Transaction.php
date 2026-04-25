<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'invoice',
        'customer_id',
        'username',
        'plan_name',
        'price',
        'type',
        'method',
        'router_id',
        'router_name',
        'note',
        'admin_id',
        'recharged_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price'        => 'decimal:2',
        'recharged_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Boot
    // -------------------------------------------------------------------------

    /**
     * Auto-generate a unique invoice number when creating a new transaction.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $transaction): void {
            if (empty($transaction->invoice)) {
                $transaction->invoice = substr('INV' . strtoupper(uniqid()), 0, 25);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope a query to transactions of a given type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to transactions made via a given payment method.
     */
    public function scopeByMethod(Builder $query, string $method): Builder
    {
        return $query->where('method', $method);
    }

    /**
     * Scope a query to transactions created within a date range.
     */
    public function scopeDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function paymentTransaction(): HasOne
    {
        return $this->hasOne(PaymentTransaction::class, 'invoice', 'invoice');
    }
}
