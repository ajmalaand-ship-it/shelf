package services.shelf.app

import android.content.ActivityNotFoundException
import android.content.Intent
import android.net.Uri
import com.ryanheise.audioservice.AudioServiceActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

class MainActivity : AudioServiceActivity() {
    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, "services.shelf.app/external")
            .setMethodCallHandler { call, result ->
                if (call.method != "open") {
                    result.notImplemented()
                } else {
                    val uri = Uri.parse(call.arguments as? String ?: "")
                    val intent = when {
                        uri.scheme == "mailto" && uri.schemeSpecificPart == "ajmalaand@gmail.com" ->
                            Intent(Intent.ACTION_SENDTO, uri)
                        uri.scheme == "https" && !uri.host.isNullOrBlank() && uri.path == "/privacy" ->
                            Intent(Intent.ACTION_VIEW, uri)
                        else -> null
                    }
                    if (intent == null) {
                        result.success(false)
                    } else {
                        try {
                            startActivity(intent)
                            result.success(true)
                        } catch (_: ActivityNotFoundException) {
                            result.success(false)
                        } catch (_: SecurityException) {
                            result.success(false)
                        }
                    }
                }
            }
    }
}
