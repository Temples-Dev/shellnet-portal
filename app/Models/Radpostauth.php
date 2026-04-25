<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Radpostauth extends Model
{
    protected $table = 'radpostauth';

    /**
     * Disable the updated_at column — post-auth records are written once.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'username',
        'pass',
        'reply',
        'class',
    ];

    /**
     * Attributes that should never be included in serialised output.
     */
    protected $hidden = [
        'pass',
    ];

    // ---------------------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------------------

    /**
     * Limit the query to post-auth records for a specific RADIUS username.
     */
    public function scopeForUser(Builder $query, string $username): Builder
    {
        return $query->where('username', $username);
    }
}
