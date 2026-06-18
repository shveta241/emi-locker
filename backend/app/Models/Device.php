<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\FcmService;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'imei_1',
        'imei_2',
        'brand',
        'model',
        'fcm_token',
        'status',
        'bypass_code',
        'upi_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function emiInstallments(): HasMany
    {
        return $this->hasMany(EmiInstallment::class);
    }

    /**
     * Check if device has any overdue installments
     */
    public function isOverdue(): bool
    {
        return $this->emiInstallments()
            ->where('status', 'PENDING')
            ->where('due_date', '<', now()->toDateString())
            ->exists();
    }

    /**
     * Get the current pending/overdue EMI details
     */
    public function getActiveEmiDetails(): array
    {
        $dueInstallment = $this->emiInstallments()
            ->where('status', 'PENDING')
            ->orderBy('due_date', 'asc')
            ->first();

        if ($dueInstallment) {
            return [
                'amount' => $dueInstallment->amount,
                'due_date' => $dueInstallment->due_date,
            ];
        }

        return [
            'amount' => 0.00,
            'due_date' => null,
        ];
    }

    /**
     * Send LOCK command to the device via FCM push
     */
    public function triggerLock(): bool
    {
        if (empty($this->fcm_token)) {
            \Log::warning("Cannot lock device ID {$this->id}: FCM token missing.");
            return false;
        }

        $emi = $this->getActiveEmiDetails();

        $payload = [
            'action' => 'LOCK',
            'customer_name' => $this->customer->name,
            'amount_due' => (string) $emi['amount'],
            'upi_id' => $this->upi_id ?? config('services.fcm.default_upi_id', 'merchant@upi'),
            'bypass_code' => $this->bypass_code,
        ];

        $fcm = new FcmService();
        $sent = $fcm->sendPush($this->fcm_token, $payload);

        if ($sent) {
            $this->update(['status' => 'LOCKED']);
            \Log::info("Sent remote LOCK command to device IMEI: {$this->imei_1}");
        }

        return $sent;
    }

    /**
     * Send UNLOCK command to the device via FCM push
     */
    public function triggerUnlock(): bool
    {
        if (empty($this->fcm_token)) {
            \Log::warning("Cannot unlock device ID {$this->id}: FCM token missing.");
            return false;
        }

        $payload = [
            'action' => 'UNLOCK',
        ];

        $fcm = new FcmService();
        $sent = $fcm->sendPush($this->fcm_token, $payload);

        if ($sent) {
            $this->update(['status' => 'UNLOCKED']);
            \Log::info("Sent remote UNLOCK command to device IMEI: {$this->imei_1}");
        }

        return $sent;
    }
}
