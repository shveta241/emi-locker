<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Device;
use App\Models\Customer;
use App\Models\EmiInstallment;
use Illuminate\Support\Facades\Log;

class DeviceApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test device automatic registration
     */
    public function test_device_auto_registration(): void
    {
        $response = $this->postJson('/api/device/register', [
            'imei' => '123456789012345',
            'fcm_token' => 'mock_fcm_token_xyz',
            'brand' => 'Samsung',
            'model' => 'Galaxy S23',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'bypass_code',
                     'device_id',
                 ])
                 ->assertJson([
                     'status' => 'success'
                 ]);

        $this->assertDatabaseHas('devices', [
            'imei_1' => '123456789012345',
            'fcm_token' => 'mock_fcm_token_xyz',
            'brand' => 'Samsung',
            'model' => 'Galaxy S23',
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'John Doe (Test)',
        ]);
    }

    /**
     * Test heartbeat check-in
     */
    public function test_device_heartbeat_sync(): void
    {
        // 1. Register device first
        $this->postJson('/api/device/register', [
            'imei' => '888888888888888',
            'fcm_token' => 'token_1',
        ]);

        // 2. Query heartbeat (should return unlocked initially)
        $response = $this->postJson('/api/device/heartbeat', [
            'imei' => '888888888888888',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'is_locked' => false,
                     'customer_name' => 'John Doe (Test)',
                 ]);

        // 3. Force overdue EMI and verify lock response
        $device = Device::where('imei_1', '888888888888888')->first();
        $installment = $device->emiInstallments()->first();
        $installment->update(['due_date' => now()->subDays(5)->toDateString()]);

        $response2 = $this->postJson('/api/device/heartbeat', [
            'imei' => '888888888888888',
        ]);

        $response2->assertStatus(200)
                  ->assertJson([
                      'status' => 'success',
                      'is_locked' => true,
                  ]);
    }

    /**
     * Test mock payment trigger and automatic unlock
     */
    public function test_payment_clearance_and_unlock(): void
    {
        // 1. Setup overdue device
        $this->postJson('/api/device/register', [
            'imei' => '999999999999999',
            'fcm_token' => 'token_lock_test',
        ]);

        $device = Device::where('imei_1', '999999999999999')->first();
        $installment = $device->emiInstallments()->first();
        $installment->update([
            'due_date' => now()->subDays(5)->toDateString(),
            'status' => 'PENDING',
        ]);

        // Sync to verify locked state
        $this->postJson('/api/device/heartbeat', [
            'imei' => '999999999999999',
        ]);
        $device->refresh();
        $this->assertEquals('LOCKED', $device->status);

        // 2. Trigger Mock Payment clearance
        $response = $this->postJson('/api/payment/mock-trigger', [
            'imei' => '999999999999999',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'unlocked' => true,
                     'remaining_overdue' => false,
                 ]);

        $device->refresh();
        $this->assertEquals('UNLOCKED', $device->status);
        
        $installment->refresh();
        $this->assertEquals('PAID', $installment->status);
    }
}
