<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaundryTransactionLine extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function baseRate(): BelongsTo
    {
        return $this->belongsTo(LaundryBaseRate::class, 'laundry_base_rate_id', 'id');
    }

    public function specialService(): BelongsTo
    {
        return $this->belongsTo(LaundrySpecialService::class, 'laundry_special_service_id', 'id');
    }
}
