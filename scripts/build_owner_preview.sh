#!/usr/bin/env bash
# Build a private owner APK in an isolated temporary workspace.
set -euo pipefail
umask 077
export TMPDIR=/home/shelf/tmp TMP=/home/shelf/tmp TEMP=/home/shelf/tmp
repo_root=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)
run_dir=$(mktemp -d "$TMPDIR/shelf-owner-apk.XXXXXXXX")
trap 'rm -rf -- "$run_dir"' EXIT
export TMPDIR="$run_dir" TMP="$run_dir" TEMP="$run_dir"
export JAVA_HOME=/home/shelf/android/jdk-17
export PATH="$JAVA_HOME/bin:$PATH"
export ANDROID_HOME=/home/shelf/android/sdk
export ANDROID_SDK_ROOT="$ANDROID_HOME"
export ANDROID_USER_HOME="$run_dir/android"
export GRADLE_USER_HOME="$run_dir/gradle"
export PUB_CACHE="$run_dir/pub"
export XDG_CACHE_HOME="$run_dir/cache" XDG_CONFIG_HOME="$run_dir/config"
export JAVA_TOOL_OPTIONS="-Djava.io.tmpdir=$run_dir"
mkdir -p "$run_dir/mobile" "$GRADLE_USER_HOME/init.d" "$ANDROID_USER_HOME"
tar -C "$repo_root/mobile" --exclude='./build' --exclude='./.dart_tool' \
  --exclude='./android/.gradle' --exclude='./.flutter-plugins-dependencies' \
  -cf - . | tar -C "$run_dir/mobile" -xf -
php "$repo_root/artisan" poetry:owner-preview-token --days=7 --output="$run_dir/preview.json"
cat > "$GRADLE_USER_HOME/init.d/shelf-signing.gradle" <<'GRADLE'
allprojects {
    afterEvaluate { project ->
        if (project.plugins.hasPlugin('com.android.application')) {
            project.android.signingConfigs.debug.storeFile = new File('/home/shelf/android/keys/shelf-debug.keystore')
        }
    }
}
GRADLE
cd "$run_dir/mobile"
/home/shelf/flutter/bin/flutter build apk --debug --flavor production --dart-define=SHELF_INTERNAL_TEST_PURCHASES=true --dart-define-from-file="$run_dir/preview.json"
destination="$repo_root/storage/app/private/owner-apks"
mkdir -p "$destination"
apk="$destination/shelf-owner-preview-$(date -u +%Y%m%d-%H%M%S).apk"
install -m 0600 build/app/outputs/flutter-apk/app-production-debug.apk "$apk"
sha256sum "$apk"
"$ANDROID_HOME/build-tools/36.0.0/apksigner" verify --print-certs "$apk"
echo "Private owner APK: $apk"
