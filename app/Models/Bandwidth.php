<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bandwidth extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'rate_down',
        'rate_down_unit',
        'rate_up',
        'rate_up_unit',
        'burst',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate_down' => 'integer',
        'rate_up' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Human-readable rate label, e.g. "10 Mbps / 5 Mbps".
     * Format: down / up (as displayed to end-users).
     */
    public function getDisplayRateAttribute(): string
    {
        return "{$this->rate_down} {$this->rate_down_unit} / {$this->rate_up} {$this->rate_up_unit}";
    }

    /**
     * RouterOS rate-limit string in up/down order, e.g. "5M/10M".
     * Unit abbreviations: Mbps -> M, Kbps -> K, Gbps -> G (fallback: raw unit).
     */
    public function getRateLimitStringAttribute(): string
    {
        return $this->toRouterOsUnit($this->rate_up, $this->rate_up_unit)
            .'/'
            .$this->toRouterOsUnit($this->rate_down, $this->rate_down_unit);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Plans that use this bandwidth profile.
     */
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Convert a rate value + unit string into a RouterOS shorthand token.
     * e.g. (10, 'Mbps') -> '10M', (512, 'Kbps') -> '512K'.
     */
    private function toRouterOsUnit(int $rate, string $unit): string
    {
        $map = [
            'Mbps' => 'M',
            'Kbps' => 'K',
            'Gbps' => 'G',
            'Bps' => '',
        ];

        $suffix = $map[trim($unit)] ?? trim($unit);

        return $rate.$suffix;
    }
}
