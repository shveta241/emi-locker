package com.emilocker.dpc.util

import android.content.Context
import android.content.SharedPreferences

class PreferenceHelper(context: Context) {

    private val prefs: SharedPreferences = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)

    companion object {
        private const val PREFS_NAME = "emi_locker_prefs"
        private const val KEY_SERVER_URL = "server_url"
        private const val KEY_IS_LOCKED = "is_locked"
        private const val KEY_FCM_TOKEN = "fcm_token"
        private const val KEY_CUSTOMER_NAME = "customer_name"
        private const val KEY_AMOUNT_DUE = "amount_due"
        private const val KEY_UPI_ID = "upi_id"
        private const val KEY_BYPASS_CODE = "bypass_code"
        private const val KEY_LAST_SYNC_TIME = "last_sync_time"
        private const val KEY_IMEI = "imei"

        // Default local dev server (e.g. Laragon or Localhost via emulator bridge)
        private const val DEFAULT_SERVER_URL = "https://muskan.universaleximconnect.com/backend/public/api"
    }

    var serverUrl: String
        get() = prefs.getString(KEY_SERVER_URL, DEFAULT_SERVER_URL) ?: DEFAULT_SERVER_URL
        set(value) = prefs.edit().putString(KEY_SERVER_URL, value).apply()

    var isLocked: Boolean
        get() = prefs.getBoolean(KEY_IS_LOCKED, false)
        set(value) = prefs.edit().putBoolean(KEY_IS_LOCKED, value).apply()

    var fcmToken: String?
        get() = prefs.getString(KEY_FCM_TOKEN, null)
        set(value) = prefs.edit().putString(KEY_FCM_TOKEN, value).apply()

    var customerName: String
        get() = prefs.getString(KEY_CUSTOMER_NAME, "Valued Customer") ?: "Valued Customer"
        set(value) = prefs.edit().putString(KEY_CUSTOMER_NAME, value).apply()

    var amountDue: String
        get() = prefs.getString(KEY_AMOUNT_DUE, "0.00") ?: "0.00"
        set(value) = prefs.edit().putString(KEY_AMOUNT_DUE, value).apply()

    var upiId: String
        get() = prefs.getString(KEY_UPI_ID, "") ?: ""
        set(value) = prefs.edit().putString(KEY_UPI_ID, value).apply()

    var bypassCode: String
        get() = prefs.getString(KEY_BYPASS_CODE, "") ?: ""
        set(value) = prefs.edit().putString(KEY_BYPASS_CODE, value).apply()

    var lastSyncTime: Long
        get() = prefs.getLong(KEY_LAST_SYNC_TIME, System.currentTimeMillis())
        set(value) = prefs.edit().putLong(KEY_LAST_SYNC_TIME, value).apply()

    var imei: String?
        get() = prefs.getString(KEY_IMEI, null)
        set(value) = prefs.edit().putString(KEY_IMEI, value).apply()
}
