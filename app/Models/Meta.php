<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Meta extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'metable_type',
        'metable_id',
        'key',
        'value',
    ];

    // ---------------------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------------------

    /**
     * The owning metable model (polymorphic).
     */
    public function metable(): MorphTo
    {
        return $this->morphTo();
    }

    // ---------------------------------------------------------------------------
    // Static helpers
    // ---------------------------------------------------------------------------

    /**
     * Retrieve a meta value for the given morph type, ID, and key.
     *
     * @param  string  $type  Fully-qualified class name or morph alias.
     * @param  int  $id  Primary key of the owner model.
     * @param  string  $key  Meta key to look up.
     * @param  mixed  $default  Value to return when no record is found.
     */
    public static function getFor(string $type, int $id, string $key, mixed $default = null): mixed
    {
        $record = static::where('metable_type', $type)
            ->where('metable_id', $id)
            ->where('key', $key)
            ->first();

        return $record ? $record->value : $default;
    }

    /**
     * Insert or update a meta value for the given morph type, ID, and key.
     *
     * @param  string  $type  Fully-qualified class name or morph alias.
     * @param  int  $id  Primary key of the owner model.
     * @param  string  $key  Meta key to store.
     * @param  mixed  $value  Value to persist.
     */
    public static function setFor(string $type, int $id, string $key, mixed $value): static
    {
        return static::updateOrCreate(
            [
                'metable_type' => $type,
                'metable_id' => $id,
                'key' => $key,
            ],
            ['value' => $value]
        );
    }
}
