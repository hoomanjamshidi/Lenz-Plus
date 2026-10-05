#!/usr/bin/env bash
# Translation workflow for lenz-plus (fa_IR).
#
#   bash tools/i18n.sh        1. regenerates languages/lenz-plus.pot from the PHP sources
#                             2. updates lenz-plus-fa_IR.po (creates it on first run), filling new
#                                entries from Studiare Extensions' fa_IR translations when the English
#                                source string is identical (many admin strings are shared)
#                             3. lists the entries that still need a Persian translation
#   bash tools/i18n.sh mo     compiles the .po into the .mo WordPress loads
#
# Needs gettext (msginit, msgmerge, msgattrib) and the WP-CLI wrapper .dev/bin/lwp.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LANG_DIR="$ROOT/lenz-plus/languages"
POT="$LANG_DIR/lenz-plus.pot"
PO="$LANG_DIR/lenz-plus-fa_IR.po"
COMPENDIUM="$ROOT/reference/studiare-extensions/languages/studiare-extensions-fa_IR.po"
LWP="$ROOT/.dev/bin/lwp"

cd "$ROOT"

if [ "${1:-}" = "mo" ]; then
	"$LWP" i18n make-mo "$PO" "$LANG_DIR/" >/dev/null
	echo "compiled $(basename "$PO" .po).mo"
	exit 0
fi

mkdir -p "$LANG_DIR"
"$LWP" i18n make-pot lenz-plus "$POT" --domain=lenz-plus --exclude=assets,vendor >/dev/null

if [ ! -f "$PO" ]; then
	msginit --no-translator --locale=fa_IR --input="$POT" --output-file="$PO" >/dev/null 2>&1
fi

# Exact source matches only: fuzzy guesses would put wrong Persian on screen.
COMP_ARGS=()
[ -f "$COMPENDIUM" ] && COMP_ARGS=(--compendium="$COMPENDIUM")
msgmerge --quiet --update --backup=none --no-wrap --no-fuzzy-matching "${COMP_ARGS[@]}" "$PO" "$POT"
# One line per string, so entries can be found and edited by their exact source text.
msgcat --no-wrap --output-file="$PO" "$PO"

# --no-wrap keeps each source string on one line; the first entry is the PO header.
LIST=$(msgattrib --untranslated --no-obsolete --no-location --no-wrap "$PO" | grep '^msgid "..*"' || true)
echo "untranslated entries: $(printf '%s' "$LIST" | grep -c . || true)"
printf '%s\n' "$LIST"
