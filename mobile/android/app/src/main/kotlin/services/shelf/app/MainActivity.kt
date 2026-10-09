package services.shelf.app

import android.content.ActivityNotFoundException
import android.content.Intent
import android.net.Uri
import com.ryanheise.audioservice.AudioServiceActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

class MainActivity : AudioServiceActivity() {
    private var photoResult: MethodChannel.Result? = null
    private val photoRequest = 7303
    @Deprecated("Legacy activity result bridge for Flutter host")
    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        if (requestCode != photoRequest) return
        val result = photoResult ?: return
        photoResult = null
        val uri = data?.data
        if (resultCode != RESULT_OK || uri == null) { result.success(null); return }
        try {
            val bytes = contentResolver.openInputStream(uri)?.use { stream ->
                val output = java.io.ByteArrayOutputStream()
                val buffer = ByteArray(8192)
                while (true) {
                    val count = stream.read(buffer)
                    if (count < 0) break
                    if (output.size() + count > 2 * 1024 * 1024) throw IllegalArgumentException()
                    output.write(buffer, 0, count)
                }
                output.toByteArray()
            } ?: throw IllegalArgumentException()
            result.success(bytes)
        } catch (_: Exception) { result.error("photo", "Choose a readable photo up to 2 MB.", null) }
    }

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, "services.shelf.app/avatar")
            .setMethodCallHandler { call, result ->
                if (call.method != "pick") result.notImplemented()
                else if (photoResult != null) result.error("busy", "Photo picker is already open.", null)
                else {
                    photoResult = result
                    try { startActivityForResult(Intent(Intent.ACTION_GET_CONTENT).apply { type = "image/*"; addCategory(Intent.CATEGORY_OPENABLE) }, photoRequest) }
                    catch (_: Exception) { photoResult = null; result.error("photo", "Photo picker is unavailable.", null) }
                }
            }

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
