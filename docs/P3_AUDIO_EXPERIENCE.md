# P3 Audio Experience

P3 was explicitly authorized by Ajmal Aand on August 29, 2026. The technical build was completed in commit `d06d7f579ebae80c69e392a08e21032b43cf9c42`, and Ajmal passed the owner real-device audio gate on August 29, 2026. Testing on real Android hardware confirmed basic playback and play/pause, background and lock-screen playback, sufficient system playback behavior, and offline cached replay after prior playback.

## Content and phase boundary

Ajmal's original voice recordings are ongoing owner content added through Filament before and after publication. Production may contain zero or only some recordings. The former expectation of 10 recordings before launch is superseded by owner decision, and an incomplete planned recording batch is not a launch blocker. No recording is fabricated, no AI voice is used, and a poem without audio remains a clean, complete reading experience. P4 share cards, P5 payments/entitlements, P6 launch work, and store submission have not started.

## Accepted technical baseline

The accepted P3 baseline is a `just_audio`-based single player with play/pause/seek/duration, streaming, bounded local caching, offline replay, interruption/audio-focus handling, and background/system media controls. Poems without audio remain clean, locked audio remains protected, and authoritative recording replacement/removal invalidates stale cache. The application requests no microphone, storage, location, or camera permission. AI voice remains excluded, and the synthetic acceptance recording remains debug-only and excluded from release builds.

## Backend contract and owner workflow

- Audio remains on the private `audio` disk; public filesystem paths are never returned.
- Accessible free audio metadata is returned from `GET /api/poems/{id}/audio` with a short-lived signed stream URL, duration, safe format, and a hashed stable cache identity.
- Locked audio continues to return locked metadata without a stream URL. P5 entitlement behavior is not implemented early.
- Filament accepts the existing M4A, MP3, and WAV allowlist up to 50 MB, rejects hostile MIME types, shows audio presence, supports deliberate replacement/removal, and detects duration with `ffprobe` rather than requiring manual entry.
- Replaced and removed database references increment `content_version`, so normal app refresh discovers additions/removals. Previous private files are not silently destroyed and remain in the established backup scope.
- The System C backup includes the database audio metadata and `storage/app/private/audio`. This does not claim that an off-server archive of future recordings exists before Ajmal supplies and verifies it.

## Flutter architecture

- `just_audio` 0.10.6 provides one process-wide player and `LockCachingAudioSource` streaming/cache.
- `just_audio_background` 0.0.1-beta.17 and `audio_service` 0.18.19 provide the notification, lock-screen, headset, and system play/pause controls for the single-player use case.
- `audio_session` 0.2.4 configures spoken-audio focus. just_audio handles platform interruptions; an explicit becoming-noisy listener pauses on headphone/Bluetooth route loss.
- `path_provider` selects the application cache directory; `crypto` hashes stable server identities into safe filenames.
- The reader displays a restrained inline player only for accessible audio: play/pause, seek, elapsed/duration, buffer/loading, cache progress, and recoverable error/retry.
- System metadata uses the authored poem title when present. Untitled works use the existing first-line UI fallback without creating an authored title. Translation attribution remains visible, and notification metadata identifies the Pashto translator without implying original authorship.

## Cache behavior

The first play uses one signed stream through `LockCachingAudioSource` while writing the same bytes to a stable local cache file. A completed cached file is used directly on later playback, including after restart/offline. Cache identity is derived from the backend's hashed recording path, not the expiring signed URL. A replacement gets a new identity and removes the prior poem cache; removal makes the refreshed poem non-playable. Temporary metadata/network failure does not delete a completed cache. Partial files are cleared before retry and cannot masquerade as complete. Retention is bounded to 20 recordings and 128 MiB with least-recently-used pruning.

## Debug acceptance audio and release isolation

The debug catalogue contains one clearly labelled synthetic, generated, quiet non-voice tone served from an in-process loopback HTTP endpoint. It is not Ajmal's recording and does not depend on copyrighted or external content. It exercises the actual streaming/cache/player path. Compile-time product-mode selection still takes precedence over every debug request, and built release binaries are checked for QA fixture/audio markers.

## Android and privacy

Android declares Internet plus wake-lock and media-playback foreground-service permissions required for background playback. It declares the audio service and media-button receiver. No microphone, storage/media-library, camera, contacts, or location permission is requested. Local cleartext is limited to `127.0.0.1` for just_audio's cache proxy. No analytics, tracking, or device identifier collection was added. Final Play Data Safety submission remains P6 work.
