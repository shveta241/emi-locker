package com.emilocker.dpc.ui

import android.app.AlertDialog
import android.app.admin.DevicePolicyManager
import android.content.*
import android.net.Uri
import android.os.Bundle
import android.text.InputType
import android.view.KeyEvent
import android.view.View
import android.view.WindowManager
import android.widget.Button
import android.widget.EditText
import android.widget.ImageView
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.emilocker.dpc.R
import com.emilocker.dpc.receiver.DeviceAdminReceiver
import com.emilocker.dpc.util.PreferenceHelper
import java.net.URLEncoder

class LockActivity : AppCompatActivity() {

    private lateinit var prefs: PreferenceHelper
    private var clickCount = 0

    private val unlockReceiver = object : BroadcastReceiver() {
        override fun onReceive(context: Context?, intent: Intent?) {
            unlockDevice()
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        // Prevent screenshots & recent apps preview
        window.setFlags(
            WindowManager.LayoutParams.FLAG_SECURE,
            WindowManager.LayoutParams.FLAG_SECURE
        )
        // Keep screen turned on and show over keyguard
        window.addFlags(
            WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON or
                    WindowManager.LayoutParams.FLAG_SHOW_WHEN_LOCKED or
                    WindowManager.LayoutParams.FLAG_DISMISS_KEYGUARD or
                    WindowManager.LayoutParams.FLAG_TURN_SCREEN_ON
        )

        setContentView(R.layout.activity_lock)
        
        prefs = PreferenceHelper(this)
        
        if (prefs.imei == null) {
            showSetupScreen()
        } else {
            // Lock task mode (pinning screen)
            enableKioskMode()
            setupUI()
        }
        
        // Register receiver for unlock signal from service
        registerReceiver(unlockReceiver, IntentFilter("com.emilocker.dpc.ACTION_UNLOCK"))
    }

    private fun showSetupScreen() {
        val setupLayout = findViewById<View>(R.id.setupLayout)
        val etImei = findViewById<EditText>(R.id.etImei)
        val etServerUrl = findViewById<EditText>(R.id.etServerUrl)
        val btnSubmitSetup = findViewById<Button>(R.id.btnSubmitSetup)

        setupLayout.visibility = View.VISIBLE
        etServerUrl.setText("https://muskan.universaleximconnect.com/backend/public/api")

        btnSubmitSetup.setOnClickListener {
            val imei = etImei.text.toString().trim()
            val serverUrl = etServerUrl.text.toString().trim()

            if (imei.length != 15) {
                Toast.makeText(this, "Please enter a valid 15-digit IMEI", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            if (!serverUrl.startsWith("http")) {
                Toast.makeText(this, "Please enter a valid HTTP server URL", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            // Temporarily set serverUrl in prefs to let registerDevice connect
            prefs.serverUrl = serverUrl

            Toast.makeText(this, "Connecting to server...", Toast.LENGTH_SHORT).show()

            val network = com.emilocker.dpc.network.NetworkManager(this)
            val mockFcmToken = prefs.fcmToken ?: "token_fcm_device_${java.util.UUID.randomUUID()}"
            
            network.registerDevice(imei, mockFcmToken) { success, error ->
                runOnUiThread {
                    if (success) {
                        prefs.imei = imei
                        prefs.fcmToken = mockFcmToken
                        Toast.makeText(this, "Device provisioned successfully!", Toast.LENGTH_LONG).show()
                        setupLayout.visibility = View.GONE
                        
                        // Check server heartbeat for actual lock state
                        network.sendHeartbeat { hbSuccess, isLocked ->
                            runOnUiThread {
                                if (hbSuccess && isLocked == false) {
                                    unlockDevice()
                                } else {
                                    enableKioskMode()
                                    setupUI()
                                }
                            }
                        }
                    } else {
                        Toast.makeText(this, "Setup Failed: $error. Check URL or Wi-Fi.", Toast.LENGTH_LONG).show()
                    }
                }
            }
        }
    }

    private fun setupUI() {
        val titleText = findViewById<TextView>(R.id.titleText)
        val customerText = findViewById<TextView>(R.id.customerNameText)
        val amountText = findViewById<TextView>(R.id.amountDueText)
        val btnPay = findViewById<Button>(R.id.btnPay)
        val btnEmergency = findViewById<Button>(R.id.btnEmergency)
        val qrImageView = findViewById<ImageView>(R.id.qrImageView)

        // Title secret clicks for manual offline bypass
        titleText.setOnClickListener {
            clickCount++
            if (clickCount >= 5) {
                clickCount = 0
                showBypassDialog()
            }
        }

        customerText.text = "Customer: ${prefs.customerName}"
        amountText.text = "EMI Amount Due: ₹${prefs.amountDue}"

        // Generate dynamic UPI QR URL
        val upiAddress = prefs.upiId.ifEmpty { "merchant@upi" }
        val transactionNote = URLEncoder.encode("EMI Payment for ${prefs.customerName}", "UTF-8")
        val upiUri = "upi://pay?pa=$upiAddress&pn=${URLEncoder.encode(prefs.customerName, "UTF-8")}&tn=$transactionNote&am=${prefs.amountDue}&cu=INR"
        
        // Use free QR code generator API to render the QR code
        val qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${URLEncoder.encode(upiUri, "UTF-8")}"
        
        // In a real app we'd use Glide/Picasso, but for lightweight native code we fetch in background
        // Or we show the UPI details to avoid complex dependencies
        // Let's provide a click to pay option on the device
        btnPay.setOnClickListener {
            try {
                val payIntent = Intent(Intent.ACTION_VIEW, Uri.parse(upiUri))
                val chooser = Intent.createChooser(payIntent, "Pay with UPI app")
                startActivity(chooser)
            } catch (e: Exception) {
                Toast.makeText(this, "No UPI App found. Scan QR instead.", Toast.LENGTH_LONG).show()
            }
        }

        btnEmergency.setOnClickListener {
            val intent = Intent(Intent.ACTION_DIAL).apply {
                data = Uri.parse("tel:112") // Standard Indian Emergency line
            }
            startActivity(intent)
        }
    }

    private fun enableKioskMode() {
        val dpm = getSystemService(Context.DEVICE_POLICY_SERVICE) as DevicePolicyManager
        val adminComponent = ComponentName(this, DeviceAdminReceiver::class.java)

        if (dpm.isDeviceOwnerApp(packageName)) {
            try {
                dpm.setLockTaskPackages(adminComponent, arrayOf(packageName))
                startLockTask()
            } catch (e: SecurityException) {
                e.printStackTrace()
            }
        } else {
            // Fallback pinning for testing
            try {
                startLockTask()
            } catch (e: Exception) {
                e.printStackTrace()
            }
        }
    }

    private fun showBypassDialog() {
        val builder = AlertDialog.Builder(this)
        builder.setTitle("Admin Bypass")
        builder.setMessage("Enter Master Offline Bypass Code:")

        val input = EditText(this)
        input.inputType = InputType.TYPE_CLASS_NUMBER
        builder.setView(input)

        builder.setPositiveButton("Verify") { _, _ ->
            val enteredCode = input.text.toString()
            val serverCode = prefs.bypassCode.ifEmpty { "998877" } // Default fallback code
            
            if (enteredCode == serverCode) {
                unlockDevice()
                Toast.makeText(this, "Device Unlocked Manually", Toast.LENGTH_SHORT).show()
            } else {
                Toast.makeText(this, "Invalid Code", Toast.LENGTH_SHORT).show()
            }
        }
        builder.setNegativeButton("Cancel") { dialog, _ -> dialog.cancel() }
        builder.show()
    }

    private fun unlockDevice() {
        try {
            stopLockTask()
        } catch (e: Exception) {
            e.printStackTrace()
        }
        prefs.isLocked = false
        finish()
    }

    // Block home key, back key, volume keys, etc.
    override fun onBackPressed() {
        // Do nothing to prevent escaping
    }

    override fun onKeyDown(keyCode: Int, event: KeyEvent?): Boolean {
        // Prevent volume key bypasses if necessary
        return if (keyCode == KeyEvent.KEYCODE_HOME || keyCode == KeyEvent.KEYCODE_BACK) {
            true
        } else {
            super.onKeyDown(keyCode, event)
        }
    }

    override fun onDestroy() {
        super.onDestroy()
        unregisterReceiver(unlockReceiver)
    }
}
