#!/usr/bin/env python3
"""
Fills Persian translations into lenz-plus/languages/lenz-plus-fa_IR.po.

Usage (from the repository root, after `bash tools/i18n.sh` listed the gaps):
    python3 tools/po-fill.py < translations.json
    python3 tools/po-fill.py <<'JSON'
    { "Booking": "رزرو", "%d result": ["%d نتیجه"] }
    JSON

Keys are the exact English source strings (msgid). A string value fills
`msgstr`; a list fills `msgstr[0..]` of a plural entry (fa_IR has one form).
Only empty entries are filled, so existing translations are never replaced.
The PO must be unwrapped (tools/i18n.sh keeps it that way).
"""

import json
import re
import sys
from pathlib import Path

PO = Path(__file__).resolve().parents[1] / "lenz-plus" / "languages" / "lenz-plus-fa_IR.po"


def quote(text: str) -> str:
    return '"' + text.replace("\\", "\\\\").replace('"', '\\"').replace("\n", "\\n") + '"'


def main() -> int:
    translations = json.load(sys.stdin)
    po = PO.read_text(encoding="utf-8")
    filled, missing = 0, []

    for source, target in translations.items():
        msgid = re.escape("msgid " + quote(source))
        if isinstance(target, list):
            pattern = re.compile(msgid + r'\nmsgid_plural "[^\n]*"\n((?:msgstr\[\d+\] ""\n?)+)')
            match = pattern.search(po)
            if not match:
                missing.append(source)
                continue
            forms = "".join(f"msgstr[{i}] {quote(t)}\n" for i, t in enumerate(target))
            po = po[: match.start(1)] + forms + po[match.end(1):]
        else:
            pattern = re.compile("(" + msgid + r')\nmsgstr ""(?=\n)')
            if not pattern.search(po):
                missing.append(source)
                continue
            po = pattern.sub(lambda m: m.group(1) + "\nmsgstr " + quote(target), po, count=1)
        filled += 1

    PO.write_text(po, encoding="utf-8")
    print(f"filled {filled}")
    for source in missing:
        print(f"not found or already translated: {source}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
