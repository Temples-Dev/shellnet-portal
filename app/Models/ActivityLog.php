<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    /**
     * Disable the updated_at column — activity logs are append-only.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'type',
        'description',
        'user_id',
        'user_type',
        'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ---------------------------------------------------------------------------
    // Static helpers
    // ---------------------------------------------------------------------------

    /**
     * Record a new activity log entry.
     *
     * @param  string       $type        A short category string (e.g. "login", "invoice").
     * @param  string       $description Human-readable description of the activity.
     * @param  int|null     $userId      ID of the acting user, if available.
     * @param  string|null  $userType    Morph type of the acting user, if applicable.
     * @param  string|null  $ip          IP address of the request origin.
     * @return static
     */
    public static function record(
        string $type,
        string $description,
        ?int $userId = null,
        ?string $userType = null,
        ?string $ip = null
    ): static {
        return static::create([
            'type'        => $type,
            'description' => $description,
            'user_id'     => $userId,
            'user_type'   => $userType,
            'ip_address'  => $ip,
        ]);
    }
}
