<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use HasFactory, Notifiable;

    // -------------------------------------------------------------------------
    // Enum-style constants
    // -------------------------------------------------------------------------

    public const STATUSES = ['Active', 'Inactive', 'Banned', 'Disabled', 'Limited', 'Suspended'];

    public const SERVICE_TYPES = ['Hotspot', 'PPPoE', 'VPN', 'Others'];

    public const ACCOUNT_TYPES = ['Personal', 'Business'];

    // -------------------------------------------------------------------------
    // Mass-assignment / hidden
    // -------------------------------------------------------------------------

    /**
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'photo',
        'pppoe_username',
        'pppoe_password',
        'pppoe_ip',
        'fullname',
        'email',
        'phone',
        'address',
        'city',
        'district',
        'state',
        'zip',
        'coordinates',
        'account_type',
        'balance',
        'service_type',
        'auto_renewal',
        'status',
        'created_by',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pppoe_password',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance'      => 'decimal:2',
            'auto_renewal' => 'boolean',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Determine whether the customer account is currently active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'Active';
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'Active');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%' . $term . '%';

        return $query->where(function (Builder $q) use ($like): void {
            $q->where('username', 'like', $like)
              ->orWhere('fullname',  'like', $like)
              ->orWhere('email',     'like', $like)
              ->orWhere('phone',     'like', $like);
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(CustomerField::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CustomerMessage::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
                    ->where('status', 'on')
                    ->latestOfMany('recharged_at');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }
}
