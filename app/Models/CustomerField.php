<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerField extends Model
{
    // -------------------------------------------------------------------------
    // Mass-assignment
    // -------------------------------------------------------------------------

    /**
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'field_name',
        'field_value',
    ];

    // -------------------------------------------------------------------------
    // Timestamps
    // -------------------------------------------------------------------------

    public $timestamps = true;

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
