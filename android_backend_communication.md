# Android & Backend Communication Guide

This document explains how the **Android DPC Client** (Frontend) and the **Laravel API** (Backend) are connected and how they communicate. Use this guide to keep the connection up to date when changing URLs, adding new API endpoints, or deploying to production.

---

## 1. Architecture Overview

The system uses a client-server architecture where the Android application acts as a Device Policy Controller (DPC) and communicates with the Laravel backend via secure HTTP REST APIs.

```
+--------------------------+                 +--------------------------+
|       Android App        |  HTTP POST/GET  |      Laravel Backend     |
|   (NetworkManager.kt)    | --------------> |    (DeviceController)    |
|                          | <-------------- |                          |
|  - Registration          |  JSON Response  |  - Stores Device Details |
|  - Heartbeat / Polling   |                 |  - Lock / Unlock Logic   |
+--------------------------+                 +--------------------------+
```

---

## 2. Server URL Configuration (Android)

The Android app retrieves the backend server's URL via the [PreferenceHelper](file:///c:/laragon/www/emi-locker/android/app/src/main/java/com/emilocker/dpc/util/PreferenceHelper.kt) class.

### Default URL
The default URL is hardcoded in [PreferenceHelper.kt](file:///c:/laragon/www/emi-locker/android/app/src/main/java/com/emilocker/dpc/util/PreferenceHelper.kt#L23):
```kotlin
private const val DEFAULT_SERVER_URL = "https://muskan.universaleximconnect.com/backend/public/api"
```

### Dynamic URL
The app can also save a custom server URL to its `SharedPreferences` (for example, when scanning a provisioning QR code or setting it manually).
* **Getter/Setter:**
  ```kotlin
  var serverUrl: String
      get() = prefs.getString(KEY_SERVER_URL, DEFAULT_SERVER_URL) ?: DEFAULT_SERVER_URL
      set(value) = prefs.edit().putString(KEY_SERVER_URL, value).apply()
  ```

---

## 3. Route Definitions (Backend)

The Laravel backend defines its API endpoints inside [web.php](file:///c:/laragon/www/emi-locker/backend/routes/web.php#L12-L30) under the `/api` prefix:

```php
Route::prefix('api')->group(function () {
    // Device management
    Route::post('/device/register', [DeviceController::class, 'register']);
    Route::post('/device/heartbeat', [DeviceController::class, 'heartbeat']);
    Route::post('/device/lock-status', [DeviceController::class, 'lockStatus']);

    // Payment processors
    Route::post('/payment/webhook', [PaymentController::class, 'webhook']);
});
```

---

## 4. API Endpoint Specifications

The [NetworkManager](file:///c:/laragon/www/emi-locker/android/app/src/main/java/com/emilocker/dpc/network/NetworkManager.kt) class in Android handles the requests. Below are the key endpoints:

### A. Device Registration
* **Endpoint:** `POST /api/device/register`
* **Android Implementation:** `NetworkManager.registerDevice()`
* **Payload (JSON):**
  ```json
  {
    "imei": "123456789012345",
    "fcm_token": "fcm-token-string",
    "model": "Pixel 6",
    "brand": "Google"
  }
  ```
* **Response (JSON):**
  ```json
  {
    "status": "success",
    "bypass_code": "123456"
  }
  ```

### B. Device Heartbeat
* **Endpoint:** `POST /api/device/heartbeat`
* **Android Implementation:** `NetworkManager.sendHeartbeat()`
* **Payload (JSON):**
  ```json
  {
    "imei": "123456789012345",
    "fcm_token": "fcm-token-string"
  }
  ```
* **Response (JSON):**
  ```json
  {
    "is_locked": false,
    "customer_name": "John Doe",
    "amount_due": "1,500.00",
    "upi_id": "merchant@upi",
    "bypass_code": "123456"
  }
  ```

---

## 5. Development vs. Production Setup

### A. Local Development (Laragon / Localhost)
If you are running Laravel locally via Laragon or `php artisan serve`:
1. Find your computer's local IP address (e.g., run `ipconfig` in Windows Command Prompt).
2. Ensure your Android device/emulator is on the **same Wi-Fi network** as your computer.
3. Update `DEFAULT_SERVER_URL` in [PreferenceHelper.kt](file:///c:/laragon/www/emi-locker/android/app/src/main/java/com/emilocker/dpc/util/PreferenceHelper.kt#L23) to point to your computer's IP address:
   ```kotlin
   private const val DEFAULT_SERVER_URL = "http://<YOUR_COMPUTER_IP>:8000/api"
   ```
4. **Important for Android 9+:** Android blocks non-HTTPS (HTTP) traffic by default. The project has network security configuration enabled to allow local HTTP traffic for development.

### B. Production Deployment
When deploying the backend to a live server (e.g., Oracle Cloud, AWS, Heroku):
1. Secure the backend with an SSL certificate (`https://`).
2. Update the `DEFAULT_SERVER_URL` to your domain name:
   ```kotlin
   private const val DEFAULT_SERVER_URL = "https://yourdomain.com/api"
   ```
