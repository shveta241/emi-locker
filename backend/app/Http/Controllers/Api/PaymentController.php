<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\EmiInstallment;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Handle incoming gateway payment webhook (e.g., Razorpay, Paytm, UPI API gateway)
     */
    public function webhook(Request $request)
    {
        Log::info("Payment Webhook received: ", $request->all());

        // Parse standard parameters or metadata
        // For Razorpay, metadata might contain 'device_id' or 'imei'
        $imei = $request->input('payload.payment.entity.notes.imei') 
                ?? $request->input('imei')
                ?? $request->input('metadata.imei');
        
        $transactionRef = $request->input('payload.payment.entity.id')
                ?? $request->input('transaction_id')
                ?? 'TXN_' . uniqid();

        $amountPaid = $request->input('payload.payment.entity.amount') 
                ? ($request->input('payload.payment.entity.amount') / 100) // Razorpay sends paise
                : $request->input('amount');

        if (!$imei) {
            return response()->json([
                'status' => 'error',
                'message' => 'IMEI not found in payment payload metadata.'
            ], 400);
        }

        $device = Device::where('imei_1', $imei)
            ->orWhere('imei_2', $imei)
            ->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Associated device not found.'
            ], 404);
        }

        return $this->processPaymentClearance($device, $amountPaid, 'UPI_WEBHOOK', $transactionRef);
    }

    /**
     * Mock payment triggering endpoint for easy local testing
     */
    public function mockTrigger(Request $request)
    {
        $request->validate([
            'imei' => 'required|string',
            'amount' => 'nullable|numeric',
        ]);

        $device = Device::where('imei_1', $request->imei)
            ->orWhere('imei_2', $request->imei)
            ->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not found.'
            ], 404);
        }

        $pendingEmi = $device->emiInstallments()
            ->where('status', 'PENDING')
            ->orderBy('due_date', 'asc')
            ->first();

        $amount = $request->amount ?? ($pendingEmi ? $pendingEmi->amount : 1500.00);
        $txnId = 'MOCK_TXN_' . rand(100000, 999999);

        return $this->processPaymentClearance($device, $amount, 'MOCK_PORTAL', $txnId);
    }

    /**
     * Core logic to clear installments and auto-unlock device
     */
    private function processPaymentClearance(Device $device, $amount, string $method, string $txnRef)
    {
        // Fetch oldest pending installment
        $installment = $device->emiInstallments()
            ->where('status', 'PENDING')
            ->orderBy('due_date', 'asc')
            ->first();

        if (!$installment) {
            return response()->json([
                'status' => 'success',
                'message' => 'No pending EMIs found for this device.',
                'unlocked' => false
            ]);
        }

        // Update installment as paid
        $installment->update([
            'status' => 'PAID',
            'paid_at' => now(),
            'payment_method' => $method,
            'transaction_reference' => $txnRef,
        ]);

        Log::info("EMI Installment ID {$installment->id} marked as PAID for Device IMEI {$device->imei_1}. Amount: ₹{$amount}");

        // Check if there are any remaining overdue installments
        $hasMoreOverdue = $device->isOverdue();

        $unlocked = false;
        if (!$hasMoreOverdue) {
            // Trigger automatic unlock via FCM!
            $unlocked = $device->triggerUnlock();
            Log::info("Device IMEI {$device->imei_1} has no remaining overdue EMIs. Sent UNLOCK trigger command.");
        } else {
            // Still has overdue installments. Re-evaluate and trigger LOCK update to sync new amount due
            $device->triggerLock();
            Log::warn("Device IMEI {$device->imei_1} still has remaining overdue installments. Kept LOCKED.");
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payment processed successfully',
            'installment_id' => $installment->id,
            'unlocked' => $unlocked,
            'remaining_overdue' => $hasMoreOverdue
        ]);
    }
}
