<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Customer;
use App\Models\EmiInstallment;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    /**
     * Register device (called on DPC setup or token refresh)
     */
    public function register(Request $request)
    {
        $request->validate([
            'imei' => 'required|string',
            'fcm_token' => 'nullable|string',
            'model' => 'nullable|string',
            'brand' => 'nullable|string',
        ]);

        $imei = $request->imei;

        // Try to find pre-registered device by IMEI 1 or 2
        $device = Device::where('imei_1', $imei)
            ->orWhere('imei_2', $imei)
            ->first();

        // Developer-friendly fallback: Auto-create device & customer if not found, to make testing easy
        if (!$device) {
            Log::info("Device with IMEI {$imei} not found. Auto-creating mock customer and device for testing.");
            
            $customer = Customer::create([
                'name' => 'John Doe (Test)',
                'phone' => '99999' . rand(10000, 99999),
                'aadhaar_number' => '123456789012',
            ]);

            $device = Device::create([
                'customer_id' => $customer->id,
                'imei_1' => $imei,
                'brand' => $request->brand ?? 'Google',
                'model' => $request->model ?? 'Pixel Emulator',
                'fcm_token' => $request->fcm_token,
                'status' => 'UNLOCKED',
                'bypass_code' => '998877',
                'upi_id' => 'testmerchant@upi',
            ]);

            // Add a mock pending EMI
            EmiInstallment::create([
                'device_id' => $device->id,
                'amount' => 1500.00,
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => 'PENDING',
            ]);
        } else {
            // Update device details
            $device->update([
                'fcm_token' => $request->fcm_token ?? $device->fcm_token,
                'brand' => $request->brand ?? $device->brand,
                'model' => $request->model ?? $device->model,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Device registered successfully',
            'bypass_code' => $device->bypass_code,
            'device_id' => $device->id,
        ]);
    }

    /**
     * Periodic check-in from the device to report online status and pull parameters
     */
    public function heartbeat(Request $request)
    {
        $request->validate([
            'imei' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        $device = Device::with('customer')->where('imei_1', $request->imei)
            ->orWhere('imei_2', $request->imei)
            ->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not found'
            ], 404);
        }

        // Update token if changed
        if ($request->filled('fcm_token') && $request->fcm_token !== $device->fcm_token) {
            $device->update(['fcm_token' => $request->fcm_token]);
        }

        // Sync local overdue status on the server
        if ($device->isOverdue() && $device->status === 'UNLOCKED') {
            Log::info("Device IMEI {$device->imei_1} has overdue EMIs. Flagging status to LOCKED.");
            $device->update(['status' => 'LOCKED']);
        }

        $emi = $device->getActiveEmiDetails();

        return response()->json([
            'status' => 'success',
            'is_locked' => ($device->status === 'LOCKED'),
            'customer_name' => $device->customer->name,
            'amount_due' => number_format($emi['amount'], 2, '.', ''),
            'due_date' => $emi['due_date'],
            'upi_id' => $device->upi_id ?? config('services.fcm.default_upi_id', 'merchant@upi'),
            'bypass_code' => $device->bypass_code,
        ]);
    }

    /**
     * Simple lock status query endpoint
     */
    public function lockStatus(Request $request)
    {
        $request->validate([
            'imei' => 'required|string',
        ]);

        $device = Device::where('imei_1', $request->imei)
            ->orWhere('imei_2', $request->imei)
            ->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'is_locked' => ($device->status === 'LOCKED'),
        ]);
    }
}
