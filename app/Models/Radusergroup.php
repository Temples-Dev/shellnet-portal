<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Radusergroup extends Model
{
    protected $table = 'radusergroup';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'groupname',
        'priority',
    ];

    // ---------------------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------------------

    /**
     * Limit the query to group memberships for a specific RADIUS username.
     */
    public function scopeForUser(Builder $query, string $username): Builder
    {
        return $query->where('username', $username);
    }
}
