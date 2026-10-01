#!/usr/bin/env bash
# A separate private app, without production credentials or owner-preview access.
set -euo pipefail
umask 077
export TMPDIR=/home/shelf/tmp TMP=/home/shelf/tmp TEMP=/home/shelf/tmp
repo_root=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)
test -f "$repo_root/.shelf-staging"
run_dir=$(mktemp -d "$TMPDIR/shelf-test-apk.XXXXXXXX")
trap 'rm -rf -- "$run_dir"' EXIT
export TMPDIR="$run_dir" TMP="$run_dir" TEMP="$run_dir"
export JAVA_HOME=/home/shelf/android/jdk-17 PATH="/home/shelf/android/jdk-17/bin:$PATH"
export ANDROID_HOME=/home/shelf/android/sdk ANDROID_SDK_ROOT=/home/shelf/android/sdk
export ANDROID_USER_HOME="$run_dir/android" GRADLE_USER_HOME="$run_dir/gradle" PUB_CACHE="$run_dir/pub"
export XDG_CACHE_HOME="$run_dir/cache" XDG_CONFIG_HOME="$run_dir/config" JAVA_TOOL_OPTIONS="-Djava.io.tmpdir=$run_dir"
export SHELF_SIGNING_PROPERTIES=/home/shelf/android/keys/shelf-upload.properties
mkdir -p "$run_dir/mobile"
tar -C "$repo_root/mobile" --exclude='./build' --exclude='./.dart_tool' --exclude='./android/.gradle' --exclude='./.flutter-plugins-dependencies' -cf - . | tar -C "$run_dir/mobile" -xf -
php -r 'require $argv[1]."/vendor/autoload.php";
$env=Dotenv\Dotenv::parse(file_get_contents($argv[1]."/.env"));
if (($env["APP_ENV"]??"")!=="staging" || ($env["DB_DATABASE"]??"")!=="shelf_staging") {exit(1);}
file_put_contents($argv[2],json_encode(["SHELF_TEST_MODE"=>"true","SHELF_API_BASE_URL"=>rtrim($env["APP_URL"],"/")."/api/","SHELF_STAGING_ACCESS_KEY"=>$env["SHELF_STAGING_ACCESS_KEY"]])); chmod($argv[2],0600);' "$repo_root" "$run_dir/defines.json"
cd "$run_dir/mobile"
/home/shelf/flutter/bin/flutter build apk --release --flavor staging --dart-define-from-file="$run_dir/defines.json"
destination="$repo_root/storage/app/private/test-apks"
mkdir -p "$destination"
apk="$destination/shelf-test-$(date -u +%Y%m%d-%H%M%S).apk"
install -m 0600 build/app/outputs/flutter-apk/app-staging-release.apk "$apk"
sha256sum "$apk"
"$ANDROID_HOME/build-tools/36.0.0/apksigner" verify --print-certs "$apk"
"$ANDROID_HOME/build-tools/36.0.0/aapt" dump badging "$apk" | head -4
echo "Private Shelf Test APK: $apk"
