#!/usr/bin/env bash

set -Eeuo pipefail

if [[ $# -ne 5 ]]; then
    echo "Usage: $0 SOURCE_PDF EXPECTED_SHA256 BOOK_TITLE PACKAGE_SLUG EXPECTED_PAGES" >&2
    exit 64
fi

source_pdf=$1
expected_sha=$2
book_title=$3
package_slug=$4
expected_pages=$5

repository_root=$(git rev-parse --show-toplevel)
protected_source_root="$repository_root/storage/app/source"
owner_download_root=/home/ajmalaand/backups/poetry/owner-downloads

source_absolute=$(realpath "$source_pdf")
if [[ "$source_absolute" != "$protected_source_root/"* ]]; then
    echo "Refusing a source outside the protected System C source directory." >&2
    exit 65
fi

if [[ ! "$expected_sha" =~ ^[0-9a-f]{64}$ ]]; then
    echo "Expected SHA256 must be 64 lowercase hexadecimal characters." >&2
    exit 65
fi

if [[ ! "$package_slug" =~ ^[a-z0-9]+(-[a-z0-9]+)*$ ]]; then
    echo "Package slug must contain lowercase ASCII letters, numbers, and single hyphens." >&2
    exit 65
fi

if [[ ! "$expected_pages" =~ ^[1-9][0-9]*$ ]]; then
    echo "Expected page count must be a positive integer." >&2
    exit 65
fi

for dependency in gs jq zip unzip file sha256sum; do
    if ! command -v "$dependency" >/dev/null; then
        echo "Required command is unavailable: $dependency" >&2
        exit 69
    fi
done

actual_sha=$(sha256sum "$source_absolute" | awk '{print $1}')
if [[ "$actual_sha" != "$expected_sha" ]]; then
    echo "Source PDF checksum does not match the protected-source record." >&2
    exit 66
fi

page_count=$(gs -q -dNOSAFER -dNODISPLAY -c "($source_absolute) (r) file runpdfbegin pdfpagecount = quit")
if [[ "$page_count" != "$expected_pages" ]]; then
    echo "Source PDF page count is $page_count; expected $expected_pages." >&2
    exit 66
fi

umask 077
mkdir -p "$owner_download_root"
chmod 0700 "$owner_download_root"

stamp=$(date +%Y%m%d-%H%M%S)
archive_name="${package_slug}-transcription-pack-${stamp}.zip"
archive_path="$owner_download_root/$archive_name"
checksum_path="$archive_path.sha256"
staging_root=$(mktemp -d "$owner_download_root/.${package_slug}-transcription.XXXXXX")

cleanup() {
    rm -rf -- "$staging_root"
}
trap cleanup EXIT

mkdir -p "$staging_root/pages" "$staging_root/transcription"

# Ghostscript rasterization only. No OCR/text extraction command is used.
gs -q -dSAFER -dNOPAUSE -dBATCH \
    -sDEVICE=pnggray -r300 -dTextAlphaBits=4 -dGraphicsAlphaBits=4 \
    -sOutputFile="$staging_root/pages/page-%03d.png" \
    "$source_absolute"

rendered_count=$(find "$staging_root/pages" -maxdepth 1 -type f -name 'page-*.png' | wc -l)
if [[ "$rendered_count" -ne "$page_count" ]]; then
    echo "Rendered page count is $rendered_count; expected $page_count." >&2
    exit 70
fi

manifest_rows="$staging_root/.manifest-rows.jsonl"
: > "$manifest_rows"

for ((page_number = 1; page_number <= page_count; page_number++)); do
    page_name=$(printf 'page-%03d.png' "$page_number")
    transcription_name=$(printf 'page-%03d.txt' "$page_number")
    image_path="$staging_root/pages/$page_name"

    if [[ ! -f "$image_path" ]]; then
        echo "Missing sequential rendered page: $page_name" >&2
        exit 70
    fi

    image_sha=$(sha256sum "$image_path" | awk '{print $1}')
    image_description=$(file -b "$image_path")
    if [[ "$image_description" =~ ([0-9]+)[[:space:]]x[[:space:]]([0-9]+) ]]; then
        image_width=${BASH_REMATCH[1]}
        image_height=${BASH_REMATCH[2]}
    else
        echo "Could not determine dimensions for $page_name." >&2
        exit 70
    fi

    : > "$staging_root/transcription/$transcription_name"

    jq -nc \
        --arg source_book_title "$book_title" \
        --arg original_pdf_path "$source_pdf" \
        --arg original_pdf_sha256 "$actual_sha" \
        --argjson source_pdf_page_number "$page_number" \
        --arg rendered_image_filename "pages/$page_name" \
        --arg rendered_image_sha256 "$image_sha" \
        --argjson width "$image_width" \
        --argjson height "$image_height" \
        '{source_book_title: $source_book_title, original_pdf_path: $original_pdf_path, original_pdf_sha256: $original_pdf_sha256, source_pdf_page_number: $source_pdf_page_number, rendered_image_filename: $rendered_image_filename, rendered_image_sha256: $rendered_image_sha256, image_dimensions: {width: $width, height: $height}, transcription_status: "UNTRANSCRIBED"}' \
        >> "$manifest_rows"
done

jq -s '{schema_version: 1, pages: .}' "$manifest_rows" > "$staging_root/page-manifest.json"
rm -- "$manifest_rows"
cp "$repository_root/docs/templates/MANUAL_TRANSCRIPTION_README.md" "$staging_root/README.md"

if find "$staging_root/transcription" -type f -name '*.txt' -size +0c | grep -q .; then
    echo "A transcription template is not empty." >&2
    exit 70
fi

manifest_count=$(jq '.pages | length' "$staging_root/page-manifest.json")
if [[ "$manifest_count" -ne "$page_count" ]]; then
    echo "Manifest contains $manifest_count pages; expected $page_count." >&2
    exit 70
fi

chmod 0600 "$staging_root/README.md" "$staging_root/page-manifest.json"
find "$staging_root/pages" "$staging_root/transcription" -type f -exec chmod 0600 {} +

(
    cd "$staging_root"
    zip -q -X -r "$archive_path" README.md page-manifest.json pages transcription
)
chmod 0600 "$archive_path"

(
    cd "$owner_download_root"
    sha256sum "$archive_name" > "$archive_name.sha256"
)
chmod 0600 "$checksum_path"

unzip -tq "$archive_path" >/dev/null

printf 'Archive: %s\n' "$archive_path"
printf 'Source SHA256: %s\n' "$actual_sha"
printf 'Archive SHA256: %s\n' "$(sha256sum "$archive_path" | awk '{print $1}')"
printf 'Pages: %s\n' "$page_count"
