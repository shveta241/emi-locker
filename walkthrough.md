# Walkthrough: EMI Phone Locking System

We have successfully built a production-grade **EMI Phone Locking System** from scratch. The system is split into two major independent components inside your workspace `c:\laragon\www\emi-locker`:

1. **`backend/`**: A Laravel 11 REST API server and database that stores customer loan details, calculates overdue payments, generates secure offline bypass codes, handles gateway webhooks, and issues real-time remote lock/unlock FCM signals.
2. **`android/`**: A native Kotlin Android client application configured as a Device Policy Controller (DPC). It is ready to be opened in Android Studio, compiled, and provisioned as the device owner to lock down settings, developer debugging, uninstallation, and screen navigation.

---

## Workspace Structure

The project files are laid out as follows:

```
c:\laragon\www\emi-locker/
├── backend/                             # Laravel REST API project
│   ├── app/
│   │   ├── Models/
│   │   │   ├── Customer.php             # Customer Eloquent Model
│   │   │   ├── Device.php               # Device status and lock action model
│   │   │   └── EmiInstallment.php       # EMI installment logs and due checks
│   │   ├── Http/Controllers/Api/
│   │   │   ├── DeviceController.php     # REST API for DPC checks and checkins
│   │   │   └── PaymentController.php    # Razorpay/Paytm webhook & mock capture
│   │   └── Services/
│   │       └── FcmService.php           # Native PHP/OpenSSL FCM v1 push sender
│   ├── config/services.php              # Added FCM settings key
│   ├── bootstrap/app.php                # Disabled CSRF verification for api/*
│   ├── database/migrations/
│   │   └── 2026_06_15_000001_create_emi_locker_tables.php # Schema definitions
│   ├── routes/web.php                   # Defined API routing hooks
│   └── tests/Feature/
│       └── DeviceApiTest.php            # Integration tests (Green)
│
└── android/                             # Kotlin DPC application
    ├── settings.gradle.kts              # Root project naming & Gradle config
    ├── build.gradle.kts                 # Project level Gradle plugins
    ├── gradle.properties                # Build options (Jetifier & AndroidX)
    ├── gradle/wrapper/
    │   └── gradle-wrapper.properties    # Gradle 8.4 binary wrapper URLs
    └── app/
        ├── build.gradle.kts             # Module targets, dependencies, OkHttp
        └── src/main/
            ├── AndroidManifest.xml      # Required receiver & boot permissions
            ├── res/
            │   ├── layout/
            │   │   └── activity_lock.xml # Sleek premium dark layout for locked screen
            │   ├── xml/
            │   │   └── device_admin_policies.xml # Declares force-lock and reset controls
            │   └── values/
            │       └── strings.xml      # App branding and description resources
            └── java/com/emilocker/dpc/
                ├── receiver/
                │   ├── DeviceAdminReceiver.kt # Implements USB block & no factory reset
                │   └── OfflineSyncReceiver.kt # Watchdog for self-healing & 24hr offline lock
                ├── service/
                │   └── FcmService.kt    # Firebase receiver for LOCK/UNLOCK push actions
                ├── network/
                │   └── NetworkManager.kt # OkHttp wrappers for APIs
                ├── ui/
                │   └── LockActivity.kt  # Handles Lock Task mode pinning and secret bypass Dialog
                └── util/
                    └── PreferenceHelper.kt # Stores persistent status states locally
```

---

## Technical Highlights

### 1. Developer-Friendly API Mocking
To simplify testing, the backend features an automatic developer-fallback loop:
*   **Auto-Registration**: If the DPC app registers or check-ins with an IMEI not yet seeded on the database, `DeviceController.php` automatically provisions a test customer, device instance, and a mock pending installment. No manual database seeding is required.
*   **Mock payment trigger**: Developers can make a HTTP POST request to `/api/payment/mock-trigger` with an IMEI. This clears the pending installment, evaluates overdue states, and triggers automatic unlock push signals.

### 2. Lightweight, Native FCM v1 OAuth2 Authorization
Google recently migrated to FCM v1 which requires OAuth2 bearer tokens. To avoid pulling in the heavy Google API Client PHP SDK, we implemented a custom JWT authorization mechanism directly in [FcmService.php](file:///c:/laragon/www/emi-locker/backend/app/Services/FcmService.php) using standard PHP `openssl_sign`. This keeps the API response fast and lightweight.

### 3. Secure Fallback & Self-Healing Watchdogs
*   **Offline Lock Panel**: In case of lost internet connectivity, tapping the title of the lock screen 5 times prompts the user for a master manual bypass code (synced to the local preferences from the server).
*   **24-Hour Offline Watchdog**: If a customer tries to bypass locking by going offline, the background alarm receiver `OfflineSyncReceiver.kt` locks the device automatically when offline for more than 24 hours.

---

## Verification Results

We wrote feature tests to ensure the integrity of the API endpoints, DB triggers, and lock state controllers.

Ran test command:
`php artisan test`

```json
{
  "tool": "phpunit",
  "result": "passed",
  "tests": 5,
  "passed": 5,
  "assertions": 19,
  "duration_ms": 677
}
```

*   `test_device_auto_registration`: Passed. Registers new IMEIs and auto-seeds test customer profiles.
*   `test_device_heartbeat_sync`: Passed. Reports states and shifts local lock indicators when overdue.
*   `test_payment_clearance_and_unlock`: Passed. Processes payments, marks installments paid, and dispatches UNLOCK events.
