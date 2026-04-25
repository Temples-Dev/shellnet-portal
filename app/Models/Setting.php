<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // -------------------------------------------------------------------------
    // Primary key
    // -------------------------------------------------------------------------

    protected $primaryKey = 'id';

    // -------------------------------------------------------------------------
    // Mass-assignment
    // -------------------------------------------------------------------------

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    /**
     * Retrieve the value stored under the given key, or a default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $record = static::where('key', $key)->first();

        return $record ? $record->value : $default;
    }

    /**
     * Persist a value under the given key (insert or update).
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key'   => $key],
            ['value' => $value],
        );
    }

    /**
     * Return all settings as an associative array keyed by setting key.
     *
     * @return array<string, mixed>
     */
    public static function all_settings(): array
    {
        return static::query()->pluck('value', 'key')->toArray();
    }
}
