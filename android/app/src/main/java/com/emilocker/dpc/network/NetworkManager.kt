package com.emilocker.dpc.network

import android.content.Context
import android.os.Build
import android.util.Log
import com.emilocker.dpc.util.PreferenceHelper
import com.google.gson.JsonObject
import com.google.gson.JsonParser
import okhttp3.*
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.IOException

class NetworkManager(private val context: Context) {

    private val client = OkHttpClient()
    private val prefs = PreferenceHelper(context)
    private val JSON = "application/json; charset=utf-8".toMediaType()

    fun registerDevice(imei: String, fcmToken: String, callback: (Boolean, String?) -> Unit) {
        val json = JsonObject().apply {
            addProperty("imei", imei)
            addProperty("fcm_token", fcmToken)
            addProperty("model", Build.MODEL)
            addProperty("brand", Build.BRAND)
        }

        val request = Request.Builder()
            .url("${prefs.serverUrl}/device/register")
            .post(json.toString().toRequestBody(JSON))
            .build()

        client.newCall(request).enqueue(object : Callback {
            override fun onFailure(call: Call, e: IOException) {
                Log.e("NetworkManager", "Registration failed: ${e.message}")
                callback(false, e.message)
            }

            override fun onResponse(call: Call, response: Response) {
                val responseBody = response.body?.string()
                if (response.isSuccessful && responseBody != null) {
                    try {
                        val obj = JsonParser.parseString(responseBody).asJsonObject
                        if (obj.has("bypass_code")) {
                            prefs.bypassCode = obj.get("bypass_code").asString
                        }
                        prefs.lastSyncTime = System.currentTimeMillis()
                        callback(true, null)
                    } catch (e: Exception) {
                        callback(false, e.message)
                    }
                } else {
                    callback(false, "Server error: ${response.code}")
                }
            }
        })
    }

    fun sendHeartbeat(callback: (Boolean, Boolean?) -> Unit) {
        val imei = prefs.imei ?: "unknown"
        val fcmToken = prefs.fcmToken ?: ""
        val json = JsonObject().apply {
            addProperty("imei", imei)
            addProperty("fcm_token", fcmToken)
        }

        val request = Request.Builder()
            .url("${prefs.serverUrl}/device/heartbeat")
            .post(json.toString().toRequestBody(JSON))
            .build()

        client.newCall(request).enqueue(object : Callback {
            override fun onFailure(call: Call, e: IOException) {
                Log.e("NetworkManager", "Heartbeat failed: ${e.message}")
                callback(false, null)
            }

            override fun onResponse(call: Call, response: Response) {
                val responseBody = response.body?.string()
                if (response.isSuccessful && responseBody != null) {
                    try {
                        val obj = JsonParser.parseString(responseBody).asJsonObject
                        val isLocked = obj.get("is_locked").asBoolean
                        
                        // Update cache
                        prefs.isLocked = isLocked
                        prefs.lastSyncTime = System.currentTimeMillis()
                        
                        if (obj.has("customer_name")) {
                            prefs.customerName = obj.get("customer_name").asString
                        }
                        if (obj.has("amount_due")) {
                            prefs.amountDue = obj.get("amount_due").asString
                        }
                        if (obj.has("upi_id")) {
                            prefs.upiId = obj.get("upi_id").asString
                        }
                        if (obj.has("bypass_code")) {
                            prefs.bypassCode = obj.get("bypass_code").asString
                        }

                        callback(true, isLocked)
                    } catch (e: Exception) {
                        callback(false, null)
                    }
                } else {
                    callback(false, null)
                }
            }
        })
    }
}
