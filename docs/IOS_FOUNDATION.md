# Shelf iOS foundation

See the authoritative 10 October iOS entry in MASTER_RECORD.md for source,
owner-confirmed setup, evidence, boundaries and unfinished work.

Connect **https://github.com/ajmalaand-ship-it/shelf**, branch **ios/foundation**,
to Codemagic; project directory **mobile**, YAML at repository root. Select the
branch and check for configuration. Do not start a build or enable billing,
automatic triggering/publishing, signing or store distribution in this task.
Workflow **ios-foundation** deliberately compiles unsigned and does not export
an installable IPA. No Apple credential is needed merely to connect GitHub.
GitHub repository-access authorization is private and limited to Shelf.

For a later authorized compilation, set **SHELF_API_BASE_URL** privately in
Codemagic to the approved HTTPS API address. Existing default checkout flags are
false; neither this variable nor a native build enables real purchases. Signing
certificates/profiles/App Store API keys must stay in the provider's protected
credential storage, never committed. Do not add Android signing files or server
.env to the cloud configuration. No native plugin or signing success is claimed
until the actual macOS build passes. CocoaPods lockfile cannot be finalized on
this Linux host; capture it after the first approved native resolution, review
versions/privacy manifests and commit before reproducible signed distribution.

Source verification: **python3 -B scripts/check_ios_foundation.py**. Shared checks
use **scripts/run_tests.sh --mobile** on this server. macOS compilation and iPhone
behavior must be recorded separately; unsigned .app artifacts prove neither.

Official references inspected 10 October:
- [Codemagic Flutter YAML and repository connection](https://docs.codemagic.io/yaml-quick-start/building-a-flutter-app/)
- [Flutter iOS setup](https://docs.flutter.dev/platform-integration/ios/setup)
- [Flutter native channels and implicit engine lifecycle](https://docs.flutter.dev/platform-integration/platform-channels)
- [Secure storage Keychain setup](https://pub.dev/packages/flutter_secure_storage)
- [Gallery save plugin setup](https://github.com/natsuk4ze/gal)
- [Native share requirements](https://pub.dev/packages/share_plus)

## Prepared GitHub Actions fallback (10 October)

`.github/workflows/ios-foundation.yml` uses a deliberate **draft PR creation**
from this repository's `ios/foundation` to `main` as the build request. It runs
only on `opened`, checks out the exact head SHA and never builds a merged main
tree. Pushes do not launch it. No default-branch change/merge is needed.

Next execution action: [compare main with ios/foundation](https://github.com/ajmalaand-ship-it/shelf/compare/main...ios/foundation?expand=1)
→ **Create draft pull request**, title **Run unsigned Shelf iOS foundation**.
Do not merge. No build has been launched here. Static checks passed, native
compilation remains unverified. Existing Codemagic queue/configuration unchanged.

GitHub SSH repository access passed. Authenticated API access is unavailable:
Actions permissions and account billing usage GETs returned **401**. Enabled
Actions policy and remaining usage/spending controls are unknown. Repository
metadata currently reports **public**, contrary to prior private-repository notes.
No account/settings/visibility changes made. See Master Record for exact evidence.

[GitHub trigger requirements](https://docs.github.com/en/actions/reference/workflows-and-actions/events-that-trigger-workflows)
explain the PR-head checkout and conflict caveat;
[manual dispatch requirements](https://docs.github.com/en/actions/how-tos/manage-workflow-runs/manually-run-a-workflow)
require default-branch presence for workflow_dispatch.
