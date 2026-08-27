#!/usr/bin/env bash
set -Eeuo pipefail

if [[ ${EUID} -ne 0 ]]; then
    echo 'Run this script as root.' >&2
    exit 1
fi

app_root=/home/ajmalaand/apps/poetry
env_file=${app_root}/.env
database=ajmalaand_poetry
database_user=ajmalaand_poetry
temporary_env=''
request_file=''
runuser_bin=$(command -v runuser)

if [[ -z ${runuser_bin} ]]; then
    echo 'The runuser executable is required.' >&2
    exit 1
fi

cleanup() {
    unset database_password
    [[ -z ${temporary_env} ]] || /usr/bin/rm -f -- "${temporary_env}"
    [[ -z ${request_file} ]] || /usr/bin/rm -f -- "${request_file}"
}
trap cleanup EXIT

check_whm_result() {
    /opt/cpanel/ea-php83/root/usr/bin/php -r '
        $response = json_decode(stream_get_contents(STDIN), true);
        $top = $response["metadata"]["result"] ?? 0;
        $uapi = $response["data"]["uapi"] ?? [];
        $nested = $uapi["result"]["status"] ?? $uapi["status"] ?? 0;
        exit($top === 1 && $nested === 1 ? 0 : 1);
    '
}

run_whm_uapi() {
    local function=$1
    local response
    response=$(/usr/local/cpanel/bin/whmapi1 --input=json --output=json uapi_cpanel < "${request_file}")
    if ! check_whm_result <<< "${response}"; then
        echo "WHM API failed while running the System C Mysql::${function} operation." >&2
        exit 1
    fi
}

if [[ -s ${env_file} ]]; then
    if ! /opt/cpanel/ea-php83/root/usr/bin/php -r '
        $env = parse_ini_file($argv[1], false, INI_SCANNER_RAW);
        $valid = ($env["DB_DATABASE"] ?? "") === "ajmalaand_poetry"
            && ($env["DB_USERNAME"] ?? "") === "ajmalaand_poetry"
            && ($env["DB_PASSWORD"] ?? "") !== ""
            && ($env["APP_URL"] ?? "") === "https://poetry.ajmalaand.com"
            && ($env["SESSION_COOKIE"] ?? "") === "poetry_session";
        exit($valid ? 0 : 1);
    ' "${env_file}"; then
        echo 'Existing environment does not match the isolated System C configuration.' >&2
        exit 1
    fi

    /usr/bin/chown ajmalaand:ajmalaand "${env_file}"
    /usr/bin/chmod 600 "${env_file}"
    if ! /usr/bin/grep -Eq '^APP_KEY=.+$' "${env_file}"; then
        "${runuser_bin}" -u ajmalaand -- /usr/bin/env APP_ENV=production \
            /opt/cpanel/ea-php83/root/usr/bin/php "${app_root}/artisan" key:generate --force --no-interaction
    fi
    echo 'Existing System C provisioning completed without recreating database objects.'
    exit 0
fi

database_password=$(/usr/bin/openssl rand -hex 32)
request_file=$(/usr/bin/mktemp)
/usr/bin/chmod 600 "${request_file}"

/usr/bin/printf '{"cpanel.user":"ajmalaand","cpanel.module":"Mysql","cpanel.function":"create_database","name":"%s"}' \
    "${database}" > "${request_file}"
run_whm_uapi create_database

/usr/bin/printf '{"cpanel.user":"ajmalaand","cpanel.module":"Mysql","cpanel.function":"create_user","name":"%s","password":"%s"}' \
    "${database_user}" "${database_password}" > "${request_file}"
run_whm_uapi create_user

/usr/bin/printf '{"cpanel.user":"ajmalaand","cpanel.module":"Mysql","cpanel.function":"set_privileges_on_database","user":"%s","database":"%s","privileges":"ALL PRIVILEGES"}' \
    "${database_user}" "${database}" > "${request_file}"
run_whm_uapi set_privileges_on_database

temporary_env=$(/usr/bin/mktemp)
/usr/bin/sed \
    -e "s|^DB_DATABASE=.*|DB_DATABASE=${database}|" \
    -e "s|^DB_USERNAME=.*|DB_USERNAME=${database_user}|" \
    -e "s|^DB_PASSWORD=.*|DB_PASSWORD=${database_password}|" \
    "${app_root}/.env.example" > "${temporary_env}"
/usr/bin/install -o ajmalaand -g ajmalaand -m 600 "${temporary_env}" "${env_file}"
/usr/bin/rm -f "${temporary_env}"
"${runuser_bin}" -u ajmalaand -- /usr/bin/env APP_ENV=production \
    /opt/cpanel/ea-php83/root/usr/bin/php "${app_root}/artisan" key:generate --force --no-interaction

echo 'System C database, user, and protected production environment provisioned.'
