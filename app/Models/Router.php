<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Router extends Model
{
    /**
     * Default RouterOS API port when none is embedded in ip_address.
     */
    public const DEFAULT_API_PORT = 8728;

    /**
     * Allowed status values.
     *
     * @var list<string>
     */
    public const STATUSES = ['Online', 'Offline'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'ip_address',
        'username',
        'password',
        'description',
        'coordinates',
        'coverage',
        'status',
        'last_seen',
        'enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enabled'   => 'boolean',
        'last_seen' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Limit the query to enabled routers.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Whether the router's status is currently "Online".
     */
    public function getIsOnlineAttribute(): bool
    {
        return $this->status === 'Online';
    }

    /**
     * Extract the API port from ip_address (format "192.168.1.1:8728").
     * Falls back to the RouterOS default port 8728.
     */
    public function getApiPortAttribute(): int
    {
        if (str_contains((string) $this->ip_address, ':')) {
            [, $port] = explode(':', $this->ip_address, 2);

            return (int) $port ?: self::DEFAULT_API_PORT;
        }

        return self::DEFAULT_API_PORT;
    }

    /**
     * Return the IP address without any trailing ":port" suffix.
     */
    public function getCleanIpAttribute(): string
    {
        if (str_contains((string) $this->ip_address, ':')) {
            [$ip] = explode(':', $this->ip_address, 2);

            return $ip;
        }

        return (string) $this->ip_address;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Internet plans assigned to this router.
     */
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * IP address pools managed by this router.
     */
    public function ipPools(): HasMany
    {
        return $this->hasMany(IpPool::class);
    }

    /**
     * Port pools managed by this router.
     */
    public function portPools(): HasMany
    {
        return $this->hasMany(PortPool::class);
    }

    /**
     * Active subscriptions served through this router.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * NAS (Network Access Server) entries linked to this router.
     */
    public function nas(): HasMany
    {
        return $this->hasMany(Nas::class);
    }
}
