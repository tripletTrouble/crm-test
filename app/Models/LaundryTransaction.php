<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaundryTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function lines(): HasMany
    {
        return $this->hasMany(LaundryTransactionLine::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(LaundryCustomer::class);
    }
}
