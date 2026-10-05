#!/usr/bin/env bash
# Builds the local test site in .dev/ (git-ignored) from scratch:
#   WordPress (fa_IR) on SQLite + WooCommerce + Elementor + Redux + WordPress Importer,
#   a test copy of the Lenz theme (ionCube license file replaced by a stub),
#   the test mu-plugin, the Lenz demo pages, a main menu, and lenz-plus symlinked in.
#
# Usage: bash tools/dev-site.sh            (keeps downloads in .dev/downloads)
# Then:  php -d memory_limit=1024M -S 127.0.0.1:8888 -t .dev/wp   → http://127.0.0.1:8888 (admin / admin)
#        .dev/bin/lwp <wp-cli command>                             → WP-CLI on the test site
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DEV="$ROOT/.dev"
WP_DIR="$DEV/wp"
DL="$DEV/downloads"
LWP="$DEV/bin/lwp"

mkdir -p "$DEV/bin" "$DL"

fetch() { # url → downloads/<file> (cached)
	local file="$DL/$(basename "$1")"
	[ -s "$file" ] || curl -sSL --max-time 900 -o "$file" "$1"
}

echo "• downloads"
[ -s "$DEV/bin/wp" ] || curl -sSL --max-time 300 -o "$DEV/bin/wp" https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x "$DEV/bin/wp"
fetch https://wordpress.org/latest.zip
PLUGINS="sqlite-database-integration woocommerce elementor redux-framework wordpress-importer"
for p in $PLUGINS; do fetch "https://downloads.wordpress.org/plugin/$p.latest-stable.zip"; done

# WP-CLI wrapper: memory for WooCommerce + Elementor; the phar itself is noisy on PHP 8.4.
cat > "$LWP" <<'SH'
#!/bin/sh
# WP-CLI against the local test site (.dev/wp), with enough memory for WooCommerce + Elementor.
# E_DEPRECATED is hidden: the wp-cli phar itself triggers notices on PHP 8.4.
DIR="$(cd "$(dirname "$0")" && pwd)"
SITE="$(cd "$DIR/../wp" && pwd)"
exec php -d memory_limit=1024M -d error_reporting=24575 "$DIR/wp" --path="$SITE" "$@"
SH
chmod +x "$LWP"

echo "• WordPress + plugins"
rm -rf "$WP_DIR" "$DEV/wordpress"
unzip -q "$DL/latest.zip" -d "$DEV" && mv "$DEV/wordpress" "$WP_DIR"
for p in $PLUGINS; do unzip -q -o "$DL/$p.latest-stable.zip" -d "$WP_DIR/wp-content/plugins/"; done
rm -rf "$WP_DIR/wp-content/plugins/akismet" "$WP_DIR/wp-content/plugins/hello.php"
SQLITE="$WP_DIR/wp-content/plugins/sqlite-database-integration"
sed -e "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$SQLITE#" -e "s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" \
	"$SQLITE/db.copy" > "$WP_DIR/wp-content/db.php"
mkdir -p "$WP_DIR/wp-content/uploads" "$WP_DIR/wp-content/mu-plugins"
cp "$ROOT/tools/dev/lzp-dev.php" "$WP_DIR/wp-content/mu-plugins/lzp-dev.php"
# Viewport harness (exact-width iframes) and the design mockups on the same origin.
cp "$ROOT/tools/dev/viewport.html" "$WP_DIR/viewport.html"
ln -sfn "$ROOT/Design" "$WP_DIR/design"

echo "• Lenz test copy"
python3 - "$ROOT" "$WP_DIR" <<'PY'
import io, os, sys, zipfile
root, wp = sys.argv[1], sys.argv[2]
outer = zipfile.ZipFile(os.path.join(root, 'Theme.zip'))
inner = zipfile.ZipFile(io.BytesIO(outer.read('Theme package/1-Theme/lenz.zip')))
for name in inner.namelist():
    if name.endswith('/') or name.startswith('lenz/plugins/'):
        continue
    out = os.path.join(wp, 'wp-content/themes', name)
    os.makedirs(os.path.dirname(out), exist_ok=True)
    with open(out, 'wb') as f:
        f.write(inner.read(name))
PY
for lic in "$WP_DIR"/wp-content/themes/lenz/Redux/RTL_License_*.php; do cp "$ROOT/tools/dev/RTL_License_stub.php" "$lic"; done

echo "• install"
"$LWP" config create --dbname=wp --dbuser=wp --dbpass=wp --dbhost=localhost --skip-check --force --extra-php <<'PHP'
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', false );
define( 'WP_ENVIRONMENT_TYPE', 'local' );
define( 'DISABLE_WP_CRON', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );
PHP
"$LWP" core install --url=http://127.0.0.1:8888 --title="Lenz Plus Test" --admin_user=admin --admin_password=admin \
	--admin_email=admin@example.com --skip-email >/dev/null
"$LWP" language core install fa_IR --activate >/dev/null
"$LWP" language plugin install --all fa_IR >/dev/null || true
"$LWP" rewrite structure '/%postname%/' >/dev/null
"$LWP" plugin activate redux-framework elementor >/dev/null
"$LWP" plugin activate woocommerce >/dev/null
"$LWP" plugin activate wordpress-importer >/dev/null
"$LWP" theme activate lenz >/dev/null

echo "• demo content"
DEMO="$ROOT/reference/lenz-demo/Demo 1 (Black and White)"
if [ -d "$DEMO" ]; then
	"$LWP" import "$DEMO/wordpress-import-file.xml" --authors=create --skip=attachment >/dev/null
	"$LWP" import "$DEMO/lenz-home-page.xml" --authors=create --skip=attachment >/dev/null
fi
MENU=$("$LWP" menu create "Main" --porcelain)
for page in $("$LWP" post list --post_type=page --post_status=publish --orderby=ID --order=ASC --field=ID | head -6); do
	"$LWP" menu item add-post "$MENU" "$page" >/dev/null
done
"$LWP" menu location assign "$MENU" main-menu
"$LWP" menu location assign "$MENU" main-menu-mobile

# Outside requests hang the single-threaded PHP server, so block them once setup is done.
"$LWP" config set WP_HTTP_BLOCK_EXTERNAL true --raw >/dev/null

echo "• lenz-plus"
mkdir -p "$ROOT/lenz-plus"
ln -sfn "$ROOT/lenz-plus" "$WP_DIR/wp-content/plugins/lenz-plus"
if [ -f "$ROOT/lenz-plus/lenz-plus.php" ]; then "$LWP" plugin activate lenz-plus >/dev/null; fi

echo "done: php -d memory_limit=1024M -S 127.0.0.1:8888 -t .dev/wp"
