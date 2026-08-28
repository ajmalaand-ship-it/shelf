# P2 Flutter reader status

P2 began through explicit owner authorization on August 28, 2026. The app is located in `mobile/` and uses Android application ID `com.hindara.pitswal`.

## Reader foundation

- Home, Collections, collection detail/front matter, ordered poem list, and poem reader are implemented against `https://poetry.ajmalaand.com/api/`.
- Production code contains no poem fixtures. Populated fixtures exist only under `mobile/test/` because the production catalogue remains intentionally draft/private.
- Public collection and poem state is respected. Locked responses show only the server-provided excerpt and no purchase control.
- Translation attribution is shown separately as original poet and Pashto translator.
- Untitled works use the first non-empty excerpt/body line for reader navigation only; no authored title is created.
- Noto Nastaliq Urdu and Scheherazade New are bundled locally with their SIL Open Font License notices.
- The reader uses explicit RTL, selectable Unicode text, preserved newlines/stanzas, approximately 2.2 line height, constrained readable width, and scrolling for long poems.
- Light, Sepia, and Dark palettes plus Nastaliq/Naskh and font-size controls persist locally without accounts.

## API and cache

The repository stores a complete, validated app-config/collection-list snapshot and versioned collection/poem responses in local preferences. It checks `content_version`, replaces cached content only after successful parsing, removes stale versioned details after a version change, and falls back to a previous valid cache when refresh fails. Covers use a disk-backed network image cache. Private source paths are never consumed or stored.

## Phase boundary and acceptance

P3 audio playback, P4 sharing, P5 purchases, search, accounts, and store submission are not included. An unobtrusive audio-availability label does not initiate playback.

Automated checks prepare the build, but the constitutional P2 exit gate remains open until Ajmal installs the debug APK on a real Android phone and verifies RTL, Nastaliq diacritics/clipping, stanza structure, small-screen margins, long scrolling, all three palettes, and font/size switching.

## Technical validation

- Flutter 3.47.2 stable / Dart 3.13.2, Temurin Java 17, Android SDK 36, Build Tools 36.0.0, and NDK 28.2 are installed under the `ajmalaand` account; no system runtime was changed.
- `flutter analyze` completes with no issues and `flutter test` passes 17 tests.
- The debug APK builds at `mobile/build/app/outputs/flutter-apk/app-debug.apk`. Its manifest reports package `com.hindara.pitswal`, minimum Android API 24, target API 36, and label `پېڅوَل — Pitswal`.
- Google command-line tools 23 report that the legacy `--licenses` operation is no longer needed. Flutter 3.47.2's doctor therefore reports Android license status as unknown even though Gradle verifies/accepts the required installed-package licenses and completes the Android build. Unconfigured Chrome/Linux desktop checks are irrelevant to this Android-only phase.
