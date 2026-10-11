# Shelf Review 3

## Shelf Review 3 — isolated implementation, 9 October 2026

**Owner-approved scope:** continue latest isolated Review 2 branch, including
pagination c211167, pinned reading preview 6c9ef45 and build-17 records 29dd095.
Use the supplied original bilingual logo/action assets and written requirements;
Figma board 15:8 was accessible and inspected as a visual reference. No placeholder
prices, book text/covers or logo were copied. ZIP integrity evidence was reused.

**Implemented:** proportion-preserving light/dark Shelf / شیلف Store header;
supplied action/inactive-navigation icons, directional RTL/LTR page controls and
local-script font glyph with localized label; persisted interface choices
پښتو، دری، English, with Afghan Dari bookstore/reader/settings/sharing translations.
Source text and book direction stay independent of interface language. Existing
owner-approved English/LTR account and purchase flows remain English/LTR.
Font names use only the interface language, including نوی شهرزاد; minimum reading
size is 16, preserving valid saved sizes; Pashto Settings is سیټینګ. Contents has
a subtly distinct reader-footer background without the repeated Book details
caption; real Book details remain reachable. Duplicate empty-search instruction
removed. Both Review 2 pagination/anchors and pinned reading preview retained.
Sharing preview remains above scrolling controls, updates font/size/colors/lines
immediately, offers expanded viewing and keeps attribution and existing limits.
Small-screen/enlarged-text export buttons use full width.

**Private account avatar:** authenticated own-account upload/replacement/removal,
private storage and no public URL/profile. Server validates JPEG/PNG/WebP up to
2 MB/4096 pixels, re-encodes bounded JPEG without source metadata, removes replaced
files and erases avatar files after confirmed account deletion. Resident photo
clears on sign-out; late requests cannot repopulate another account. Reversible
avatar-path migration is prepared in git, NOT applied live. Android picker uses
the existing native channel pattern without broad media permissions. Proposed
second/cover photo is PENDING PURPOSE CLARIFICATION, not implemented.

**Evidence:** focused scripts/run_tests.sh --mobile, review-3 scope and temporary
ReaderAvatarTest database: 97 Flutter checks plus 1 staging-identity check;
4 avatar API tests / 35 assertions. Covers all three UI languages, dark/inactive
assets, RTL/LTR arrows, 320-width text scales 1/2/3, short landscape, pinned live
preview updates, original-font reflow/pagination/positions/access regressions,
private cross-account denial, invalid uploads, replacement/removal and confirmed
deletion. Flutter analysis: no errors; 58 existing style/warning notices remain
(nonfatal policy). PHP syntax and git diff --check passed. Log:
/home/shelf/tmp/shelf-review3-checks.log; synthetic local renders:
/home/shelf/tmp/shelf-review3-renders/. These are not real-phone acceptance.

**Remaining/boundaries:** Android native picker/device and all new UI phone review
pending; iOS native avatar picker/build/device readiness NOT VERIFIED. Existing
installed Shelf Test 1.0.8 (17) does not contain Review 3. No production deployment,
live migration, release build, store upload, real transaction or main merge/push.
Checkout OFF, pricing sync DEFERRED/disabled, second-phone restore owner-deferred
and Android/iOS joint launch preserved. Newer records/unrelated edits retained.
Next phone-review step requires separately authorized private staging of the
avatar backend and a fresh separate Shelf Test APK; then review languages/logo,
font names/16-size, both pinned previews, pagination/Contents and photo lifecycle.

