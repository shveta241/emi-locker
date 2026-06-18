package com.emilocker.dpc.receiver

import android.app.admin.DeviceAdminReceiver
import android.app.admin.DevicePolicyManager
import android.content.ComponentName
import android.content.Context
import android.content.Intent
import android.os.UserManager
import android.widget.Toast

class DeviceAdminReceiver : DeviceAdminReceiver() {

    override fun onEnabled(context: Context, intent: Intent) {
        super.onEnabled(context, intent)
        Toast.makeText(context, "EMI Locker DPC: Admin Enabled", Toast.LENGTH_SHORT).show()
        
        // Apply system-level policies if we are the Device Owner
        val dpm = context.getSystemService(Context.DEVICE_POLICY_SERVICE) as DevicePolicyManager
        val adminComponent = ComponentName(context, DeviceAdminReceiver::class.java)

        if (dpm.isDeviceOwnerApp(context.packageName)) {
            try {
                // Prevent Factory Reset
                dpm.addUserRestriction(adminComponent, UserManager.DISALLOW_FACTORY_RESET)
                
                // Prevent Safe Boot
                dpm.addUserRestriction(adminComponent, UserManager.DISALLOW_SAFE_BOOT)
                
                // Prevent USB Debugging / Developer Options
                dpm.addUserRestriction(adminComponent, UserManager.DISALLOW_DEBUGGING_FEATURES)
                
                // Prevent mounting external storage
                dpm.addUserRestriction(adminComponent, UserManager.DISALLOW_MOUNT_PHYSICAL_MEDIA)
                
                // Keep the app pinned/persistent
                dpm.setUninstallBlocked(adminComponent, context.packageName, true)
                
                Toast.makeText(context, "Strict Security Policies Enforced", Toast.LENGTH_LONG).show()
            } catch (e: SecurityException) {
                e.printStackTrace()
            }
        }
    }

    override fun onDisabled(context: Context, intent: Intent) {
        super.onDisabled(context, intent)
        Toast.makeText(context, "EMI Locker DPC: Admin Disabled", Toast.LENGTH_SHORT).show()
    }

    override fun onProfileProvisioningComplete(context: Context, intent: Intent) {
        super.onProfileProvisioningComplete(context, intent)
        
        // This is called when setup via QR code (Device Owner provisioning) is complete.
        val dpm = context.getSystemService(Context.DEVICE_POLICY_SERVICE) as DevicePolicyManager
        val adminComponent = ComponentName(context, DeviceAdminReceiver::class.java)
        
        if (dpm.isDeviceOwnerApp(context.packageName)) {
            // Ensure the app is automatically locked if it tries to uninstall or reset
            dpm.setUninstallBlocked(adminComponent, context.packageName, true)
        }
    }
}
