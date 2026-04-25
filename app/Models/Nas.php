<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nas extends Model
{
    protected $fillable = [
        'nasname',
        'shortname',
        'type',
        'ports',
        'secret',
        'server',
        'community',
        'description',
        'router_id',
    ];

    /**
     * Attributes that should never be included in serialised output.
     */
    protected $hidden = [
        'secret',
    ];

    // ---------------------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------------------

    /**
     * The router this NAS device is associated with.
     */
    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }
}
