#!/usr/bin/env bash
set -euo pipefail
umask 077
export TMPDIR=/home/shelf/tmp TMP=/home/shelf/tmp TEMP=/home/shelf/tmp
repo_root=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)
run_dir=$(mktemp -d "$TMPDIR/shelf-release.XXXXXXXX")
trap 'rm -rf -- "$run_dir"' EXIT
export TMPDIR="$run_dir" TMP="$run_dir" TEMP="$run_dir"
export JAVA_HOME=/home/shelf/android/jdk-17 PATH="/home/shelf/android/jdk-17/bin:$PATH"
export ANDROID_HOME=/home/shelf/android/sdk ANDROID_SDK_ROOT=/home/shelf/android/sdk
export ANDROID_USER_HOME="$run_dir/android" GRADLE_USER_HOME="$run_dir/gradle" PUB_CACHE="$run_dir/pub"
export XDG_CACHE_HOME="$run_dir/cache" XDG_CONFIG_HOME="$run_dir/config" JAVA_TOOL_OPTIONS="-Djava.io.tmpdir=$run_dir"
export SHELF_SIGNING_PROPERTIES=/home/shelf/android/keys/shelf-upload.properties
test -r "$SHELF_SIGNING_PROPERTIES"
mkdir -p "$run_dir/mobile"
tar -C "$repo_root/mobile" --exclude='./build' --exclude='./.dart_tool' --exclude='./android/.gradle' --exclude='./.flutter-plugins-dependencies' -cf - . | tar -C "$run_dir/mobile" -xf -
cd "$run_dir/mobile"
/home/shelf/flutter/bin/flutter build appbundle --release
destination="$repo_root/storage/app/private/owner-aabs"
mkdir -p "$destination"
aab="$destination/shelf-internal-test-$(date -u +%Y%m%d-%H%M%S).aab"
install -m 0600 build/app/outputs/bundle/release/app-release.aab "$aab"
"$JAVA_HOME/bin/jarsigner" -verify "$aab"
sha256sum "$aab"
echo "Private test AAB: $aab"
