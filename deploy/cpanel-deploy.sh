#!/usr/bin/env bash
set -euo pipefail

project="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
cd "$project"
web="$(dirname "$project")/public_html"

fail() { printf 'Serenovia deployment: %s\n' "$*" >&2; exit 1; }
[[ -d "$web" && ! -L "$web" ]] || fail "Expected a public_html directory next to the repository."
[[ ! -e "$web/wp-config.php" && ! -d "$web/wp-admin" ]] || fail "Move WordPress out of public_html before deploying."
[[ ! -e "$web/index.php" ]] || cmp -s "$web/index.php" deploy/public-html-index.php || fail "public_html/index.php belongs to another site."
[[ -f .env ]] || fail "Create the production .env in the repository with File Manager first."
[[ -s database/database.sqlite ]] || fail "Upload database/database.sqlite outside public_html first."

php_bin=/opt/cpanel/ea-php83/root/usr/bin/php
if [[ ! -x "$php_bin" ]]; then
    php_bin="$(command -v php)" || fail "PHP CLI is unavailable."
fi
"$php_bin" -r 'exit(PHP_VERSION_ID < 80200 || !extension_loaded("pdo_sqlite") || !extension_loaded("gd") ? 1 : 0);' ||
    fail "PHP CLI 8.2+ with PDO SQLite and GD is required."

composer_bin="$(command -v composer || true)"
if [[ -z "$composer_bin" && -f /opt/cpanel/composer/bin/composer ]]; then
    composer_bin=/opt/cpanel/composer/bin/composer
fi
if [[ -z "$composer_bin" ]]; then
    # cPanel runs this task even when interactive Terminal is unavailable.
    tools_dir="$project/storage/app/deploy-tools"
    mkdir -p "$tools_dir"
    composer_bin="$tools_dir/composer.phar"
    if [[ ! -f "$composer_bin" ]]; then
        printf 'Installing project-local Composer from the official verified installer.\n'
        setup_dir="$(mktemp -d "$tools_dir/setup.XXXXXX")"
        trap 'rm -rf -- "$setup_dir"' EXIT
        download() {
            if command -v curl >/dev/null 2>&1; then
                curl --fail --silent --show-error --location --proto '=https' --proto-redir '=https' --connect-timeout 20 --max-time 120 "$1" -o "$2"
            else
                "$php_bin" -r '$context = stream_context_create(["http" => ["timeout" => 120], "ssl" => ["verify_peer" => true, "verify_peer_name" => true]]); exit(copy($argv[1], $argv[2], $context) ? 0 : 1);' "$1" "$2"
            fi
        }
        download https://composer.github.io/installer.sig "$setup_dir/installer.sig" || fail "Cannot download Composer checksum. Ask hosting support to allow outbound HTTPS to composer.github.io."
        download https://getcomposer.org/installer "$setup_dir/composer-setup.php" || fail "Cannot download Composer installer. Ask hosting support to allow outbound HTTPS to getcomposer.org."
        "$php_bin" -r '$expected = trim(file_get_contents($argv[1])); exit(preg_match("/^[a-f0-9]{96}$/i", $expected) && hash_equals(strtolower($expected), hash_file("sha384", $argv[2])) ? 0 : 1);' "$setup_dir/installer.sig" "$setup_dir/composer-setup.php" || fail "Composer installer checksum mismatch; installation stopped."
        "$php_bin" "$setup_dir/composer-setup.php" --2 --install-dir="$tools_dir" --filename=composer.phar --quiet || fail "Composer setup failed. Check PHP extensions and outbound HTTPS in the log above."
        rm -rf -- "$setup_dir"
        trap - EXIT
    fi
fi
"$php_bin" "$composer_bin" --version --no-ansi || fail "Composer cannot run with the selected PHP version."

cp deploy/public-path.php bootstrap/public-path.php
"$php_bin" "$composer_bin" install --no-dev --prefer-dist --optimize-autoloader --no-interaction
"$php_bin" artisan optimize:clear
if grep -Eq '^APP_KEY=[[:space:]]*$' .env; then
    "$php_bin" artisan key:generate --force
fi

# Take a consistent SQLite snapshot before applying any new migrations.
umask 077
mkdir -p storage/app/backups
"$php_bin" -r '$db = new PDO("sqlite:database/database.sqlite"); $db->exec("PRAGMA busy_timeout=5000"); $db->exec("VACUUM INTO ".$db->quote($argv[1]));' "storage/app/backups/pre-deploy-$(date -u +%Y%m%dT%H%M%SZ)-$$.sqlite"
"$php_bin" artisan migrate --force
umask 022

for dir in css images js; do
    cp -R "public/$dir" "$web/"
done
cp public/.htaccess public/favicon.ico public/robots.txt "$web/"
cp deploy/public-html-index.php "$web/index.php"
printf 'Serenovia deployment complete.\n'
