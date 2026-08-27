#!/usr/bin/env bash
set -Eeuo pipefail

app_root=/home/ajmalaand/apps/poetry
document_root=/home/ajmalaand/public_html/poetry
deployment_backup=/home/ajmalaand/backups/poetry/deployments/$(date -u +%Y%m%d-%H%M%S)

if [[ $(id -un) != ajmalaand ]]; then
    echo 'Run as the ajmalaand account.' >&2
    exit 1
fi
if [[ ! -s ${app_root}/.env ]]; then
    echo 'Production .env is missing. Run the root provisioning script first.' >&2
    exit 1
fi

mkdir -p ${deployment_backup}
chmod 700 /home/ajmalaand/backups/poetry /home/ajmalaand/backups/poetry/deployments ${deployment_backup}
cp -a ${document_root}/. ${deployment_backup}/

cd ${app_root}
php artisan poetry:backup
php artisan migrate --force --no-interaction
php artisan filament:assets
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

rsync -a public/ ${document_root}/
install -m 644 deploy/public-index.php ${document_root}/index.php
install -m 644 deploy/public-htaccess ${document_root}/.htaccess

echo "System C deployed. Previous document root backed up at ${deployment_backup}."
