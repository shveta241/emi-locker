package com.emilocker.dpc.receiver

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.util.Log
import com.emilocker.dpc.network.NetworkManager
import com.emilocker.dpc.ui.LockActivity
import com.emilocker.dpc.util.PreferenceHelper

class OfflineSyncReceiver : BroadcastReceiver() {

    override fun onReceive(context: Context, intent: Intent) {
        val action = intent.action
        Log.d("OfflineSyncReceiver", "Received action: $action")

        val prefs = PreferenceHelper(context)
        
        // 1. Self-healing Check: If locked, ensure LockActivity is displayed
        if (prefs.isLocked) {
            ensureLocked(context)
        }

        // 2. Offline Watchdog Check: Lock device if offline for more than 24 hours
        val timeSinceLastSync = System.currentTimeMillis() - prefs.lastSyncTime
        val maxOfflineLimitMs = 24 * 60 * 60 * 1000 // 24 hours
        
        if (timeSinceLastSync > maxOfflineLimitMs && !prefs.isLocked) {
            Log.w("OfflineSyncReceiver", "Device offline too long. Triggering security lock.")
            prefs.isLocked = true
            ensureLocked(context)
        }

        // 3. Network Heartbeat Sync Check
        val network = NetworkManager(context)
        network.sendHeartbeat { success, isLocked ->
            if (success && isLocked != null) {
                if (isLocked) {
                    ensureLocked(context)
                } else if (!isLocked && prefs.isLocked) {
                    // Unlock the device if server state updated to Unlocked
                    val unlockIntent = Intent("com.emilocker.dpc.ACTION_UNLOCK")
                    context.sendBroadcast(unlockIntent)
                }
            }
        }
    }

    private fun ensureLocked(context: Context) {
        val lockIntent = Intent(context, LockActivity::class.java).apply {
            addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
            addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP)
        }
        context.startActivity(lockIntent)
    }
}
