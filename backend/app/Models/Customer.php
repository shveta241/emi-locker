<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'aadhaar_number',
        'user_id',
    ];

    public function retailer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
