<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Odp extends Model
{
    protected $fillable = [
        'name',
        'port_amount',
        'attenuation',
        'address',
        'coordinates',
        'coverage',
    ];

    protected $casts = [
        'port_amount'  => 'integer',
        'attenuation'  => 'float',
        'coverage'     => 'integer',
    ];

    /**
     * Parse the stored "lat,lng" string into an associative array.
     * Returns null when the coordinates field is empty or malformed.
     *
     * @return array{lat: float, lng: float}|null
     */
    public function getCoordinatesArrayAttribute(): ?array
    {
        if (empty($this->coordinates)) {
            return null;
        }

        $parts = array_map('trim', explode(',', $this->coordinates, 2));

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            return null;
        }

        return [
            'lat' => (float) $parts[0],
            'lng' => (float) $parts[1],
        ];
    }
}
