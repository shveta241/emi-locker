<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Customer;
use App\Models\EmiInstallment;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Render the admin dashboard view.
     */
    public function index()
    {
        // Auto-seed some mock data for a rich visual presentation if empty
        if (\Illuminate\Support\Facades\Schema::hasTable('devices') && Device::count() === 0) {
            $this->seedMockData();
        }

        return view('dashboard');
    }

    /**
     * Get all devices with relationships for frontend SPA polling/updates.
     */
    public function getDashboardData()
    {
        $devices = Device::with(['customer', 'emiInstallments' => function ($query) {
            $query->orderBy('due_date', 'asc');
        }])->get()->map(function ($device) {
            $activeEmi = $device->getActiveEmiDetails();
            $device->amount_due = $activeEmi['amount'];
            $device->due_date = $activeEmi['due_date'];
            $device->is_overdue = $device->isOverdue();
            return $device;
        });

        return response()->json([
            'status' => 'success',
            'devices' => $devices
        ]);
    }

    /**
     * Register a new device + customer + pending EMI installment from the web form.
     */
    public function registerMockDevice(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'brand' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'imei' => 'required|string|size:15|unique:devices,imei_1',
            'emi_amount' => 'required|numeric|min:1',
            'due_date' => 'required|date',
            'status' => 'required|in:LOCKED,UNLOCKED',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'aadhaar_number' => '1234' . rand(1000, 9999) . rand(1000, 9999),
        ]);

        $device = Device::create([
            'customer_id' => $customer->id,
            'imei_1' => $request->imei,
            'brand' => $request->brand,
            'model' => $request->model,
            'fcm_token' => 'fcm_token_web_' . uniqid(),
            'status' => $request->status,
            'bypass_code' => '998877',
            'upi_id' => 'emi.pay.' . rand(100, 999) . '@upi',
        ]);

        // Create the EMI installment.
        // If status is LOCKED, we make it overdue by adjusting the due date if needed.
        $dueDate = $request->due_date;
        $emiStatus = 'PENDING';
        if ($request->status === 'LOCKED') {
            $dueDate = now()->subDays(rand(1, 10))->toDateString();
        }

        EmiInstallment::create([
            'device_id' => $device->id,
            'amount' => $request->emi_amount,
            'due_date' => $dueDate,
            'status' => $emiStatus,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mock device registered successfully!',
            'device' => $device
        ]);
    }

    /**
     * Trigger remote LOCK push signal.
     */
    public function lockDevice($id)
    {
        $device = Device::findOrFail($id);
        
        // Ensure fcm_token is set for simulation
        if (empty($device->fcm_token)) {
            $device->update(['fcm_token' => 'fcm_token_web_' . uniqid()]);
        }

        $success = $device->triggerLock();

        if ($success) {
            return response()->json([
                'status' => 'success',
                'message' => "Remote LOCK command sent successfully to {$device->brand} {$device->model}!"
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to send LOCK command.'
        ], 500);
    }

    /**
     * Trigger remote UNLOCK push signal.
     */
    public function unlockDevice($id)
    {
        $device = Device::findOrFail($id);
        
        // Ensure fcm_token is set for simulation
        if (empty($device->fcm_token)) {
            $device->update(['fcm_token' => 'fcm_token_web_' . uniqid()]);
        }

        $success = $device->triggerUnlock();

        if ($success) {
            return response()->json([
                'status' => 'success',
                'message' => "Remote UNLOCK command sent successfully to {$device->brand} {$device->model}!"
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to send UNLOCK command.'
        ], 500);
    }

    /**
     * Seed initial mock data for rich visual experience.
     */
    private function seedMockData()
    {
        // 1. Seed active/unlocked device (Alice Smith)
        $customer1 = Customer::create([
            'name' => 'Alice Smith',
            'phone' => '+91 98765 43210',
            'aadhaar_number' => '492810394820',
        ]);

        $device1 = Device::create([
            'customer_id' => $customer1->id,
            'imei_1' => '358201948201948',
            'brand' => 'Samsung',
            'model' => 'Galaxy S24 Ultra',
            'fcm_token' => 'fcm_token_mock_alice',
            'status' => 'UNLOCKED',
            'bypass_code' => '887766',
            'upi_id' => 'alice.retailer@upi',
        ]);

        EmiInstallment::create([
            'device_id' => $device1->id,
            'amount' => 2499.00,
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'PENDING',
        ]);

        EmiInstallment::create([
            'device_id' => $device1->id,
            'amount' => 2499.00,
            'due_date' => now()->addDays(35)->toDateString(),
            'status' => 'PENDING',
        ]);

        // 2. Seed locked/overdue device (Bob Johnson)
        $customer2 = Customer::create([
            'name' => 'Bob Johnson',
            'phone' => '+91 91234 56789',
            'aadhaar_number' => '882049103948',
        ]);

        $device2 = Device::create([
            'customer_id' => $customer2->id,
            'imei_1' => '864201948201930',
            'brand' => 'Google',
            'model' => 'Pixel 8 Pro',
            'fcm_token' => 'fcm_token_mock_bob',
            'status' => 'LOCKED',
            'bypass_code' => '998877',
            'upi_id' => 'bob.retailer@upi',
        ]);

        // Add an overdue EMI
        EmiInstallment::create([
            'device_id' => $device2->id,
            'amount' => 1999.00,
            'due_date' => now()->subDays(4)->toDateString(),
            'status' => 'PENDING', // Will be re-evaluated as overdue
        ]);
        
        EmiInstallment::create([
            'device_id' => $device2->id,
            'amount' => 1999.00,
            'due_date' => now()->addDays(26)->toDateString(),
            'status' => 'PENDING',
        ]);
    }

    /**
     * Generate Android Enterprise Provisioning payload dynamically.
     */
    public function getProvisioningData()
    {
        $apkPath = public_path('download/app.apk');
        
        if (!file_exists($apkPath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'APK file not found on server.'
            ], 404);
        }

        // Calculate SHA-256 hash
        $hash = hash_file('sha256', $apkPath, true);
        
        // Android requires URL-safe base64 encoding (no padding =, replace + with -, / with _)
        $base64 = base64_encode($hash);
        $urlSafeBase64 = str_replace(['+', '/', '='], ['-', '_', ''], $base64);

        $downloadUrl = url('download/app.apk');

        $provisioningJson = [
            "android.app.extra.PROVISIONING_DEVICE_ADMIN_COMPONENT_NAME" => "com.emilocker.dpc/com.emilocker.dpc.receiver.DeviceAdminReceiver",
            "android.app.extra.PROVISIONING_DEVICE_ADMIN_PACKAGE_DOWNLOAD_LOCATION" => $downloadUrl,
            "android.app.extra.PROVISIONING_DEVICE_ADMIN_PACKAGE_CHECKSUM" => $urlSafeBase64,
            "android.app.extra.PROVISIONING_LEAVE_ALL_SYSTEM_APPS_ENABLED" => true
        ];

        return response()->json([
            'status' => 'success',
            'provisioning_data' => $provisioningJson
        ]);
    }
}
