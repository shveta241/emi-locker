package com.emilocker.dpc.service

import android.content.Intent
import android.util.Log
import com.emilocker.dpc.network.NetworkManager
import com.emilocker.dpc.ui.LockActivity
import com.emilocker.dpc.util.PreferenceHelper
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage

class FcmService : FirebaseMessagingService() {

    override fun onNewToken(token: String) {
        super.onNewToken(token)
        Log.d("FcmService", "New Firebase token: $token")
        
        val prefs = PreferenceHelper(applicationContext)
        prefs.fcmToken = token

        // If device is registered, update token on server
        if (prefs.imei != null) {
            val network = NetworkManager(applicationContext)
            network.registerDevice(prefs.imei!!, token) { success, _ ->
                if (success) {
                    Log.d("FcmService", "FCM token updated on server successfully.")
                }
            }
        }
    }

    override fun onMessageReceived(message: RemoteMessage) {
        super.onMessageReceived(message)
        Log.d("FcmService", "FCM message received: ${message.data}")

        val prefs = PreferenceHelper(applicationContext)
        val data = message.data

        if (data.containsKey("action")) {
            val action = data["action"]
            
            // Sync details if provided in FCM payload
            if (data.containsKey("customer_name")) {
                prefs.customerName = data["customer_name"] ?: prefs.customerName
            }
            if (data.containsKey("amount_due")) {
                prefs.amountDue = data["amount_due"] ?: prefs.amountDue
            }
            if (data.containsKey("upi_id")) {
                prefs.upiId = data["upi_id"] ?: prefs.upiId
            }
            if (data.containsKey("bypass_code")) {
                prefs.bypassCode = data["bypass_code"] ?: prefs.bypassCode
            }

            when (action) {
                "LOCK" -> {
                    prefs.isLocked = true
                    launchLockScreen()
                }
                "UNLOCK" -> {
                    prefs.isLocked = false
                    dismissLockScreen()
                }
            }
        }
    }

    private fun launchLockScreen() {
        val intent = Intent(applicationContext, LockActivity::class.java).apply {
            addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
            addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP)
            putExtra("action", "LOCK")
        }
        startActivity(intent)
    }

    private fun dismissLockScreen() {
        val intent = Intent("com.emilocker.dpc.ACTION_UNLOCK")
        sendBroadcast(intent)
    }
}
