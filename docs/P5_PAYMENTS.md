# P5 Payments and Locked Content

Ajmal Aand authorized P5 on August 29, 2026. The technical implementation uses one one-time product, `pitswal_unlock_all_v1`, one RevenueCat entitlement, `unlock_all`, and offering `default`. Product price remains an owner decision. P6 and Google Play submission have not started.

**Recorded status: TECHNICAL BUILD COMPLETE — REAL PROVIDER/SANDBOX ACCEPTANCE DEFERRED TO RELEASE PREPARATION.** Ajmal approved this sequencing decision on August 29, 2026. It is not abandonment of P5 and does not mark P5 fully complete. Real product activation/pricing, RevenueCat and Play connection, production keys, and real purchase/restore/paid-audio acceptance will be completed during final Android release preparation.

## Current provider findings

The implementation was checked against current official RevenueCat and Google Play documentation on August 29, 2026. `purchases_flutter` 10.10.x is the current compatible SDK line. It uses Google Billing Client 8+, for which consumed one-time products cannot be restored by anonymous users. Therefore the Play product must be marked **non-consumable in RevenueCat**; RevenueCat otherwise consumes it. RevenueCat-generated anonymous IDs are cached on-device, change after reinstall, and are reconciled through a user-triggered `restorePurchases` under the dashboard's transfer-to-new-App-User-ID behavior.

References:

- <https://www.revenuecat.com/docs/getting-started/installation/flutter>
- <https://www.revenuecat.com/docs/getting-started/restoring-purchases>
- <https://www.revenuecat.com/docs/getting-started/entitlements/android-products>
- <https://www.revenuecat.com/docs/customers/identifying-customers>
- <https://developer.android.com/google/play/billing/one-time-products>

## Mobile architecture

The public Android SDK key is supplied only at build time with `--dart-define=REVENUECAT_PUBLIC_SDK_KEY=...`. Missing configuration leaves the app unentitled and does not reveal paid content. RevenueCat creates the anonymous App User ID; the app sends it only to the Pitswal API as `X-RC-User-Id`. The reader obtains the configured product from offering `default`, requires product ID `pitswal_unlock_all_v1`, displays RevenueCat's localized `priceString`, and grants UI entitlement only when CustomerInfo reports active `unlock_all`.

Purchase cancellation, provider error, completed-but-not-entitled, restore success, and restore-no-purchase are distinct states with Pashto feedback. Purchase and restore trigger a protected-content request with `X-RC-Refresh: 1`; the backend remains authoritative. Debug simulation exists only behind the compile-time release boundary and changes synthetic QA content only.

Public JSON and entitled JSON are stored under distinct keys. Paid keys include a one-way hash of the anonymous customer context. Paid audio cache identity includes that context as well. A legitimately entitled customer can use cached content offline, but unentitled/fresh contexts do not select paid caches. This is not advanced DRM; online CustomerInfo refresh and server cache expiry eventually enforce refund/revocation state.

## Backend authorization

Laravel validates the App User ID (maximum 100 bytes, no slash/control characters or RevenueCat-blocked placeholder IDs) and queries the supported RevenueCat v1 customer endpoint:

`GET https://api.revenuecat.com/v1/subscribers/{app_user_id}`

The server reads only `subscriber.entitlements.unlock_all`. Its secret is never returned to Flutter or public errors. Positive decisions cache for 86,400 seconds; negative/provider-failure decisions cache for 300 seconds. Cache keys use HMAC-SHA-256 and logs use only a truncated SHA-256 reference. Calls have a four-second timeout and no uncontrolled retry. Missing credentials, 401/403, 404, malformed data, timeout, rate/provider failure, or unknown state returns locked/excerpt content.

Free samples remain full without RevenueCat. Unpublished collections/poems remain unavailable even when entitled. List endpoints never return poem bodies. Paid audio metadata requires entitlement and returns a ten-minute signed URL; private storage paths and identifiers are absent from that URL.

## External owner setup still required

**Mandatory release-stage blocker:** Complete Google Play + RevenueCat real configuration and sandbox purchase/restore gate before final closed testing/production submission.

Until the following work is completed, production remains fail-closed for locked content, free/public content remains available, and fake/debug entitlement cannot be used in release. These items are intentionally deferred, not waived:

1. In Google Play Console, create/confirm the app `پېڅوَل — Pitswal` with package `com.hindara.pitswal`; upload only to an internal/closed testing track when owner-approved. Do not submit production.
2. Under Monetize → Products → One-time products, create `pitswal_unlock_all_v1` as a permanent/non-consumed unlock. Ajmal selects localized price and activates the product.
3. In RevenueCat, create the Pitswal project and Android app `com.hindara.pitswal`.
4. In Google Cloud, enable Google Play Android Developer API and Google Play Developer Reporting API. Create a dedicated RevenueCat service account following RevenueCat's current credential guide. Grant only the currently documented Cloud roles and the required Play Console app/account permissions. Do not send the JSON key through chat.
5. Upload that dedicated JSON credential directly to RevenueCat Project Settings → Google Play App Settings → Service account credentials, then securely delete working copies not required by the owner's credential-backup policy. Credentials may take up to 36 hours to validate.
6. Import/create `pitswal_unlock_all_v1` in RevenueCat and explicitly mark it **non-consumable**. Create entitlement `unlock_all` and attach the product. Create offering `default` with one lifetime/custom package containing that product.
7. Obtain the Android **public app-specific SDK key** from RevenueCat Project Settings → API keys. Store it in the secure build environment and pass it as `--dart-define=REVENUECAT_PUBLIC_SDK_KEY=...`; it is a mobile public key, not a server secret.
8. Create a minimum-scope RevenueCat **secret API v1 key** able to read customer information. Place it directly in the System C production `.env` as `REVENUECAT_SECRET_API_KEY`; keep that file mode 0600. Keep `REVENUECAT_ENTITLEMENT=unlock_all`. Never place this key in Flutter, source, chat, Play Console, or APK assets.
9. Set RevenueCat restore behavior to **Transfer to new App User ID**, required for the no-account anonymous reinstall/restore model.
10. Configure license testers and perform the real Play sandbox gate: fresh install samples only; purchase unlock; reinstall and Restore; direct API without entitlement remains locked; paid audio receives a signed URL only after verification.

RevenueCat's current Google Play credential steps are at <https://www.revenuecat.com/docs/service-credentials/creating-play-service-credentials>. Final Play Data Safety answers must disclose purchase history processing by RevenueCat and be completed in P6 against the final artifact. Minimal purchase terms/refund wording and store metadata are also deferred to P6 rather than fabricated here.
