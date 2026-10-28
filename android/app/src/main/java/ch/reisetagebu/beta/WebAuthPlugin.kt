package ch.reisetagebu.beta

import android.content.Intent
import android.net.Uri
import androidx.browser.customtabs.CustomTabsIntent
import com.getcapacitor.JSObject
import com.getcapacitor.Plugin
import com.getcapacitor.PluginCall
import com.getcapacitor.PluginMethod
import com.getcapacitor.annotation.CapacitorPlugin

/**
 * Runs the OAuth login in a Custom Tab (in-app browser sharing the default
 * browser's cookies) and resolves with the callback URL, mirroring the iOS
 * WebAuthPlugin in MainViewController.swift.
 *
 * The callback deep link reaches MainActivity (launchMode singleTask), which
 * also closes the Custom Tab sitting on top of it in the task.
 */
@CapacitorPlugin(name = "WebAuth")
class WebAuthPlugin : Plugin() {
    private var pendingCall: PluginCall? = null
    private var callbackScheme: String? = null

    @PluginMethod
    fun authenticate(call: PluginCall) {
        val url = call.getString("url")
        val scheme = call.getString("callbackScheme")
        if (url == null || scheme == null) {
            call.reject("url and callbackScheme are required")
            return
        }

        pendingCall?.reject("Superseded by a new login", "CANCELLED")
        pendingCall = call
        callbackScheme = scheme
        CustomTabsIntent.Builder().build().launchUrl(activity, Uri.parse(url))
    }

    override fun handleOnNewIntent(intent: Intent) {
        super.handleOnNewIntent(intent)
        val data = intent.data ?: return
        if (data.scheme != callbackScheme) return

        pendingCall?.resolve(JSObject().put("url", data.toString()))
        pendingCall = null
    }

    override fun handleOnResume() {
        super.handleOnResume()
        // Android delivers the callback (onNewIntent) before onResume, so a call
        // still pending here means the user closed the Custom Tab.
        pendingCall?.reject("Login cancelled", "CANCELLED")
        pendingCall = null
    }
}
