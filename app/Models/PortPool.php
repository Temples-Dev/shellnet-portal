<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortPool extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'port_pools';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'public_ip',
        'range_port',
        'router_id',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The router that owns this port pool.
     */
    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }
}
