import Flutter
import UIKit
import PhotosUI
import ImageIO
import AuthenticationServices
import Security
import CryptoKit

@main
@objc class AppDelegate: FlutterAppDelegate, FlutterImplicitEngineDelegate, PHPickerViewControllerDelegate, ASAuthorizationControllerDelegate, ASAuthorizationControllerPresentationContextProviding {
  private var appleRelayRehearsal = false
  private let relayKeychainService = "services.shelf.app/apple-relay-rehearsal"
  private var appleResult: FlutterResult?
  private var appleController: ASAuthorizationController?
  private var appleAnchor: UIWindow?
  private var avatarResult: FlutterResult?
  private var channels: [FlutterMethodChannel] = []

  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }

  func didInitializeImplicitFlutterEngine(_ engineBridge: FlutterImplicitEngineBridge) {
    GeneratedPluginRegistrant.register(with: engineBridge.pluginRegistry)
    let messenger = engineBridge.applicationRegistrar.messenger()
    let avatar = FlutterMethodChannel(name: "services.shelf.app/avatar", binaryMessenger: messenger)
    avatar.setMethodCallHandler { [weak self] call, result in
      guard call.method == "pick" else { result(FlutterMethodNotImplemented); return }
      self?.pickAvatar(result)
    }
    let external = FlutterMethodChannel(name: "services.shelf.app/external", binaryMessenger: messenger)
    external.setMethodCallHandler { call, result in
      guard call.method == "open" else { result(FlutterMethodNotImplemented); return }
      guard let address = call.arguments as? String, let url = URL(string: address),
            ["https", "mailto"].contains(url.scheme?.lowercased() ?? "") else {
        result(false); return
      }
      UIApplication.shared.open(url, options: [:]) { opened in result(opened) }
    }
    let identity = FlutterMethodChannel(name: "services.shelf.app/identity", binaryMessenger: messenger)
    identity.setMethodCallHandler { [weak self] call, result in
      if call.method == "googleClientId" {
        let value = Bundle.main.object(forInfoDictionaryKey: "GIDClientID") as? String
        result(value)
      } else if call.method == "relayRehearsalStatus" {
        guard let self = self else { result(["available": false, "saved": false]); return }
        let allowed = self.relayRehearsalAllowed()
        result(["available": allowed, "saved": allowed && self.savedRelayAddress() != nil])
      } else if ["prepareRelayAddress", "copyRelayAddress", "forgetRelayAddress"].contains(call.method) {
        guard let self = self, self.relayRehearsalAllowed() else {
          result(FlutterError(code: "unavailable", message: "This owner check is unavailable.", details: nil)); return
        }
        guard self.appleResult == nil else {
          result(FlutterError(code: "busy", message: "An Apple request is already open.", details: nil)); return
        }
        if call.method == "prepareRelayAddress" {
          var bytes = [UInt8](repeating: 0, count: 32)
          guard SecRandomCopyBytes(kSecRandomDefault, bytes.count, &bytes) == errSecSuccess else {
            result(FlutterError(code: "unavailable", message: "Please try again.", details: nil)); return
          }
          let nonce = SHA256.hash(data: Data(bytes)).map { String(format: "%02x", $0) }.joined()
          self.signInWithApple(nonce: nonce, relayRehearsal: true, result: result)
        } else if call.method == "copyRelayAddress" {
          guard let address = self.savedRelayAddress() else { result(false); return }
          // Device-local clipboard only; never send the address/token through Flutter.
          UIPasteboard.general.setItems([[UIPasteboard.typeAutomatic: address]], options: [
            .localOnly: true, .expirationDate: Date().addingTimeInterval(60)
          ])
          result(true)
        } else {
          let status = SecItemDelete(self.relayKeychainQuery() as CFDictionary)
          result(status == errSecSuccess || status == errSecItemNotFound)
        }
      } else if call.method == "apple" {
        guard let nonce = call.arguments as? String, nonce.count == 64 else {
          result(FlutterError(code: "invalid_nonce", message: "Please try sign-in again.", details: nil)); return
        }
        self?.signInWithApple(nonce: nonce, result: result)
      } else { result(FlutterMethodNotImplemented) }
    }
    channels = [avatar, external, identity]
  }

  // A temporary distribution constraint, never a backend authentication bypass.
  // Build 3 must be assigned only to the Shelf Owner internal TestFlight group.
  private func relayRehearsalAllowed() -> Bool {
    #if DEBUG
    return false
    #else
    // iOS TestFlight receipts may be absent; do not require a purchase/receipt refresh.
    // Distribution is restricted by verified internal-only export + Shelf Owner group.
    return Bundle.main.bundleIdentifier == "services.shelf.app"
      && (Bundle.main.object(forInfoDictionaryKey: "CFBundleShortVersionString") as? String) == "1.0.0"
      && (Bundle.main.object(forInfoDictionaryKey: "CFBundleVersion") as? String) == "3"
    #endif
  }

  private func relayKeychainQuery() -> [String: Any] {
    return [kSecClass as String: kSecClassGenericPassword,
            kSecAttrService as String: relayKeychainService,
            kSecAttrAccount as String: "relay-address",
            kSecAttrSynchronizable as String: false]
  }

  private func isRelayAddress(_ address: String) -> Bool {
    return address.count <= 254 && address.range(
      of: "^[A-Za-z0-9._%+-]+@privaterelay\\.appleid\\.com$", options: .regularExpression
    ) == (address.startIndex..<address.endIndex)
  }

  private func savedRelayAddress() -> String? {
    var query = relayKeychainQuery()
    query[kSecReturnData as String] = true
    query[kSecMatchLimit as String] = kSecMatchLimitOne
    var value: CFTypeRef?
    guard SecItemCopyMatching(query as CFDictionary, &value) == errSecSuccess,
          let data = value as? Data, let address = String(data: data, encoding: .utf8),
          isRelayAddress(address) else { return nil }
    return address
  }

  private func saveRelayAddress(_ address: String) -> Bool {
    let attributes: [String: Any] = [kSecValueData as String: Data(address.utf8),
      kSecAttrAccessible as String: kSecAttrAccessibleWhenUnlockedThisDeviceOnly]
    let query = relayKeychainQuery()
    let status = SecItemUpdate(query as CFDictionary, attributes as CFDictionary)
    if status == errSecSuccess { return true }
    guard status == errSecItemNotFound else { return false }
    var item = query
    attributes.forEach { item[$0.key] = $0.value }
    return SecItemAdd(item as CFDictionary, nil) == errSecSuccess
  }

  private func signInWithApple(nonce: String, relayRehearsal: Bool = false, result: @escaping FlutterResult) {
    guard appleResult == nil else {
      result(FlutterError(code: "busy", message: "Sign-in is already open.", details: nil)); return
    }
    guard let anchor = UIApplication.shared.connectedScenes.compactMap({ $0 as? UIWindowScene })
      .first(where: { $0.activationState == .foregroundActive })?.windows.first(where: { $0.isKeyWindow }) else {
      result(FlutterError(code: "unavailable", message: "Cannot open sign-in right now.", details: nil)); return
    }
    let request = ASAuthorizationAppleIDProvider().createRequest()
    request.requestedScopes = [.email]
    request.nonce = nonce
    let controller = ASAuthorizationController(authorizationRequests: [request])
    controller.delegate = self
    controller.presentationContextProvider = self
    appleRelayRehearsal = relayRehearsal
    appleResult = result
    appleAnchor = anchor
    appleController = controller
    controller.performRequests()
  }

  func presentationAnchor(for controller: ASAuthorizationController) -> ASPresentationAnchor {
    return appleAnchor!
  }

  func authorizationController(controller: ASAuthorizationController, didCompleteWithAuthorization authorization: ASAuthorization) {
    guard let credential = authorization.credential as? ASAuthorizationAppleIDCredential else {
      finishApple(FlutterError(code: "invalid_identity", message: "Please try again.", details: nil)); return
    }
    if appleRelayRehearsal {
      // Deliberately do not read identityToken, authorizationCode or user identifier.
      guard relayRehearsalAllowed(), let address = credential.email, isRelayAddress(address) else {
        finishApple(FlutterError(code: "relay_missing", message: "Apple did not return a private relay address. Use your existing Shelf relay address if available. Do not revoke a real account.", details: nil)); return
      }
      guard saveRelayAddress(address) else {
        finishApple(FlutterError(code: "unavailable", message: "The address could not be saved privately. Please try again.", details: nil)); return
      }
      finishApple(true)
      return
    }
    guard let tokenData = credential.identityToken, let token = String(data: tokenData, encoding: .utf8),
      let codeData = credential.authorizationCode, let code = String(data: codeData, encoding: .utf8) else {
      finishApple(FlutterError(code: "invalid_identity", message: "Apple sign-in could not be verified.", details: nil)); return
    }
    // Email and identity are accepted only from server-verified token claims.
    finishApple(["id_token": token, "authorization_code": code])
  }

  func authorizationController(controller: ASAuthorizationController, didCompleteWithError error: Error) {
    if (error as? ASAuthorizationError)?.code == .canceled { finishApple(nil); return }
    finishApple(FlutterError(code: "unavailable", message: "Apple sign-in is unavailable. Please try again.", details: nil))
  }

  private func finishApple(_ value: Any?) {
    let result = appleResult
    appleResult = nil
    appleRelayRehearsal = false
    appleController = nil
    appleAnchor = nil
    result?(value)
  }

  private func presenter() -> UIViewController? {
    let scene = UIApplication.shared.connectedScenes
      .compactMap { $0 as? UIWindowScene }.first { $0.activationState == .foregroundActive }
    var controller = scene?.windows.first { $0.isKeyWindow }?.rootViewController
    while let presented = controller?.presentedViewController { controller = presented }
    return controller
  }

  private func pickAvatar(_ result: @escaping FlutterResult) {
    guard avatarResult == nil else {
      result(FlutterError(code: "busy", message: "A photo selection is already open.", details: nil)); return
    }
    guard let controller = presenter() else {
      result(FlutterError(code: "unavailable", message: "Cannot open photos right now.", details: nil)); return
    }
    // PHPicker grants access only to the chosen image; no broad library permission.
    var configuration = PHPickerConfiguration()
    configuration.filter = .images
    configuration.selectionLimit = 1
    let picker = PHPickerViewController(configuration: configuration)
    picker.delegate = self
    avatarResult = result
    controller.present(picker, animated: true)
  }

  func picker(_ picker: PHPickerViewController, didFinishPicking results: [PHPickerResult]) {
    picker.dismiss(animated: true)
    guard let item = results.first else { finishAvatar(nil); return }
    item.itemProvider.loadFileRepresentation(forTypeIdentifier: "public.image") { [weak self] url, error in
      guard error == nil, let url = url,
            let source = CGImageSourceCreateWithURL(url as CFURL, nil),
            let thumbnail = CGImageSourceCreateThumbnailAtIndex(source, 0, [
              kCGImageSourceCreateThumbnailFromImageAlways: true,
              kCGImageSourceCreateThumbnailWithTransform: true,
              kCGImageSourceThumbnailMaxPixelSize: 1024
            ] as CFDictionary),
            let data = UIImage(cgImage: thumbnail).jpegData(compressionQuality: 0.85),
            data.count <= 2 * 1024 * 1024 else {
        DispatchQueue.main.async {
          self?.finishAvatar(FlutterError(code: "invalid_photo", message: "Please choose another photo.", details: nil))
        }
        return
      }
      // Re-encoding strips source metadata. Server validation/access remains authoritative.
      DispatchQueue.main.async { self?.finishAvatar(FlutterStandardTypedData(bytes: data)) }
    }
  }

  private func finishAvatar(_ value: Any?) {
    let result = avatarResult
    avatarResult = nil
    result?(value)
  }
}
