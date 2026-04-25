<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    /**
     * Disable the updated_at column — message logs are append-only.
     */
    const UPDATED_AT = null;

    // ---------------------------------------------------------------------------
    // Constants
    // ---------------------------------------------------------------------------

    const TYPES = ['sms', 'email', 'whatsapp', 'telegram'];

    // ---------------------------------------------------------------------------
    // Model configuration
    // ---------------------------------------------------------------------------

    protected $fillable = [
        'type',
        'recipient',
        'content',
        'status',
        'error',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ---------------------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------------------

    /**
     * Limit the query to logs of a specific message type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Limit the query to log entries that failed delivery.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }
}
