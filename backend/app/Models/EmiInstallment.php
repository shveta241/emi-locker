<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmiInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'amount',
        'due_date',
        'status', // PENDING, PAID, OVERDUE
        'paid_at',
        'payment_method',
        'transaction_reference',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
