#!/usr/bin/env bash
# Packages the plugin folder into dist/lenz-plus-<version>.zip,
# ready to upload via Plugins → Add New → Upload Plugin.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN="lenz-plus"
VERSION="$(grep -m1 -E '^\s*\*\s*Version:' "$ROOT/$PLUGIN/$PLUGIN.php" | awk '{print $NF}')"
OUT="$ROOT/dist/$PLUGIN-$VERSION.zip"

# Minified copies must match the sources that ship.
node "$ROOT/tools/minify.mjs" > /dev/null

mkdir -p "$ROOT/dist"
rm -f "$OUT"

cd "$ROOT"
zip -rq "$OUT" "$PLUGIN" -x '*.DS_Store' -x '*/node_modules/*' -x '*/.git*'

echo "$OUT"
