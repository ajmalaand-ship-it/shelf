# Google Play Data Safety — pre-submission note

The Google Play Data Safety form must be completed from the **final Flutter build** and its actual SDKs, permissions, and data flows. This repository does not declare the final Data Safety answers at the current preparation stage.

Before production submission, review the public privacy policy and complete the form against the built release whenever Flutter introduces or changes:

- analytics;
- crash reporting;
- purchase SDK behavior;
- device or other identifiers;
- storage access or platform permissions; or
- any other data collection, sharing, retention, or deletion behavior.

The submitted form, store disclosures, released application behavior, backend behavior, and public privacy policy must agree.

## P3 audio implementation note

P3 adds publisher-provided audio playback through `just_audio`, `audio_session`, `audio_service`, and `just_audio_background`. These packages are used for media decoding, spoken-audio focus/interruption behavior, background playback, and Android media controls. The app adds `WAKE_LOCK`, `FOREGROUND_SERVICE`, and `FOREGROUND_SERVICE_MEDIA_PLAYBACK`; it does not request microphone, storage/media-library, location, contacts, or camera permissions. The just_audio cache proxy is restricted by Android network security configuration to loopback cleartext only. P3 adds no analytics, advertising, tracking SDK, account identifier, or user recording behavior. Final Data Safety answers still require review against the P6 release artifact.

## P4 share-card implementation note

P4 uses `share_plus` to open the platform share sheet for app-generated PNG files and `gal` to insert explicitly saved PNGs into the device gallery. On current Android versions gallery saving uses scoped media behavior without a broad storage permission. `WRITE_EXTERNAL_STORAGE` is declared only with `maxSdkVersion="28"` for Android 9 and older devices that require legacy write access. P4 does not request read-media, `MANAGE_EXTERNAL_STORAGE`, contacts, camera, microphone, or location access and adds no analytics, advertising, tracking, identifiers, or remote data transfer. Share staging files are app-controlled temporary output; saved gallery files are user-visible output. Final Data Safety answers remain a P6 final-artifact review.
