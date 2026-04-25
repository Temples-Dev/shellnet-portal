<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    // ---------------------------------------------------------------------------
    // Constants
    // ---------------------------------------------------------------------------

    const ROLES = ['Admin', 'Agent', 'Sales', 'Customer'];

    const POSITIONS = [
        1 => 'Top',
        2 => 'Left',
        3 => 'Right',
        4 => 'Bottom',
    ];

    // ---------------------------------------------------------------------------
    // Model configuration
    // ---------------------------------------------------------------------------

    protected $fillable = [
        'title',
        'widget',
        'content',
        'position',
        'order',
        'role',
        'enabled',
    ];

    protected $casts = [
        'enabled'  => 'boolean',
        'position' => 'integer',
        'order'    => 'integer',
    ];

    // ---------------------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------------------

    /**
     * Limit the query to enabled widgets only.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * Limit the query to widgets assigned to a specific role.
     */
    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    // ---------------------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------------------

    /**
     * Return the human-readable label for the widget's position.
     */
    public function getPositionLabelAttribute(): ?string
    {
        return self::POSITIONS[$this->position] ?? null;
    }
}
