# P2 Flutter reader status

P2 began through explicit owner authorization on August 28, 2026. The app is located in `mobile/` and uses Android application ID `com.hindara.pitswal`.

**Gate status: Complete — owner real-device acceptance recorded August 29, 2026.**

## Reader foundation

- Home, Collections, collection detail/front matter, ordered poem list, and poem reader are implemented against `https://poetry.ajmalaand.com/api/`.
- Release builds contain no active poem fixtures. The production catalogue remains intentionally draft/private; synthetic acceptance fixtures are selected only in compile-time debug mode.
- Public collection and poem state is respected. Locked responses show only the server-provided excerpt and no purchase control.
- Translation attribution is shown separately as original poet and Pashto translator.
- Untitled works use the first non-empty excerpt/body line for reader navigation only; no authored title is created.
- Noto Nastaliq Urdu and Scheherazade New are bundled locally with their SIL Open Font License notices.
- The reader uses explicit RTL, selectable Unicode text, preserved newlines/stanzas, approximately 2.2 line height, constrained readable width, and scrolling for long poems.
- The full poem title appears only in the reader content hierarchy. The pinned toolbar is title-free, opaque in every reader palette, and separated from the hard-clipped, bottom-safe scroll viewport so Nastaliq text cannot paint behind it.
- Forward collection-entry cues use direction-aware icons, while Flutter's automatic back controls retain platform behavior and mirror correctly in RTL.
- Light, Sepia, and Dark palettes plus Nastaliq/Naskh and font-size controls persist locally without accounts.

## API and cache

The repository stores a complete, validated app-config/collection-list snapshot and versioned collection/poem responses in local preferences. It checks `content_version`, replaces cached content only after successful parsing, removes stale versioned details after a version change, and falls back to a previous valid cache when refresh fails. Covers use a disk-backed network image cache. Private source paths are never consumed or stored.

## Phase boundary and acceptance

P3 audio playback, P4 sharing, P5 purchases, search, accounts, and store submission are not included. An unobtrusive audio-availability label does not initiate playback.

The real-device acceptance build provides two clearly labelled local test collections covering cover/no-cover, titled/untitled, short/long, multi-stanza/free-verse, translation, free, and locked reader states. Product build mode takes precedence over every requested fixture mode, so release builds continue to use only the production API/cache repository. The fixtures are not published and make no backend or production-data changes.

Ajmal tested the final P2 QA APK on real Android hardware and accepted the core reader operational gate with the assessment: “The basics of the operations are working.” Owner evidence confirmed launch, corrected Home hierarchy, RTL navigation, the complete collection-to-reader route, long scrolling, unobscured poem text, single-title hierarchy, unclipped Nastaliq rendering, reader controls, font-size adjustment, Nastaliq/Naskh selection, Light/Sepia/Dark availability, and locked/translation representations.

The accepted technical baseline is the `com.hindara.pitswal` Flutter reader with RTL Pashto, bundled Noto Nastaliq Urdu and Scheherazade New, Light/Sepia/Dark palettes, font-size control, Nastaliq/Naskh toggle, and the versioned public API/cache architecture. Synthetic QA wording remains test-only; fixtures remain debug-only. Real production content remains governed by API publication state and was not published for acceptance.

P2 acceptance closed the core operational reader gate, not final real-content product acceptance. Under Roadmap Revision 3, real-content visibility and product review belong to PC; final Play Store screenshots/assets remain P6 work after PC passes. P3 subsequently passed its owner real-device audio gate, and P4 share cards were subsequently authorized on August 29, 2026.

## Technical validation

- Flutter 3.47.2 stable / Dart 3.13.2, Temurin Java 17, Android SDK 36, Build Tools 36.0.0, and NDK 28.2 are installed under the `ajmalaand` account; no system runtime was changed.
- `flutter analyze` completes with no issues and `flutter test` passes 22 tests.
- The debug APK builds at `mobile/build/app/outputs/flutter-apk/app-debug.apk`. Its manifest reports package `com.hindara.pitswal`, minimum Android API 24, target API 36, and label `پېڅوَل — Pitswal`.
- Google command-line tools 23 report that the legacy `--licenses` operation is no longer needed. Flutter 3.47.2's doctor therefore reports Android license status as unknown even though Gradle verifies/accepts the required installed-package licenses and completes the Android build. Unconfigured Chrome/Linux desktop checks are irrelevant to this Android-only phase.
