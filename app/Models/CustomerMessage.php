<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerMessage extends Model
{
    // -------------------------------------------------------------------------
    // Timestamp configuration — no updated_at column
    // -------------------------------------------------------------------------

    public const UPDATED_AT = null;

    // -------------------------------------------------------------------------
    // Mass-assignment
    // -------------------------------------------------------------------------

    /**
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'admin_id',
        'subject',
        'body',
        'from',
        'read_at',
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
            'read_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Determine whether the message has been read.
     */
    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
