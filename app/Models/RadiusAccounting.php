<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadiusAccounting extends Model
{
    /**
     * The underlying RADIUS accounting table.
     */
    protected $table = 'radius_accounting';

    /**
     * Disable the updated_at column — accounting records are written once.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'session_id',
        'username',
        'realm',
        'nas_id',
        'nas_ip',
        'nas_port_id',
        'nas_port_type',
        'framed_ip',
        'session_time',
        'input_octets',
        'output_octets',
        'status_type',
        'mac_address',
    ];

    protected $casts = [
        'session_time'  => 'integer',
        'input_octets'  => 'integer',
        'output_octets' => 'integer',
        'created_at'    => 'datetime',
    ];

    // ---------------------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------------------

    /**
     * Return the combined byte count (download + upload).
     */
    public function getTotalOctetsAttribute(): int
    {
        return (int) $this->input_octets + (int) $this->output_octets;
    }

    /**
     * Return a human-readable session duration derived from session_time (seconds).
     *
     * Examples: "2h 15m", "45m", "30s"
     */
    public function getSessionDurationAttribute(): string
    {
        $seconds = (int) $this->session_time;

        if ($seconds <= 0) {
            return '0s';
        }

        $hours   = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs    = $seconds % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }

        if ($minutes > 0) {
            $parts[] = "{$minutes}m";
        }

        if ($secs > 0 || empty($parts)) {
            $parts[] = "{$secs}s";
        }

        return implode(' ', $parts);
    }
}
