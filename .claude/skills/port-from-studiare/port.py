#!/usr/bin/env python3
"""
Copy a Studiare Extensions file into the Lenz Plus plugin with the mechanical
renames applied, then list the lines that still need a human decision
(Studiare theme hooks, variables, selectors, Persian brand names).

Usage (from the repository root):
    python3 .claude/skills/port-from-studiare/port.py <src> [<dest>] [--force] [--dry-run]

    <src>   path inside reference/studiare-extensions/ (e.g. includes/Core/Sanitizer.php)
    <dest>  path inside lenz-plus/ (default: same path, file name renamed)

The script never overwrites an existing file unless --force is given, so a
ported file that was already adapted by hand is not lost.
"""

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[3]
SRC_ROOT = ROOT / "reference" / "studiare-extensions"
DEST_ROOT = ROOT / "lenz-plus"

# Order matters: longer, more specific patterns first.
RENAMES = [
    (r"studiare-extensions", "lenz-plus"),
    (r"Studiare Extensions", "Lenz Plus"),
    (r"Studiare\+", "Lenz+"),
    (r"studiare-plus", "lenz-plus"),
    (r"StudiareExt", "LenzPlus"),
    (r"STUDIARE_EXT_", "LENZ_PLUS_"),
    (r"studiare_ext_", "lenz_plus_"),
    (r"studiare_ext\b", "lenz_plus"),
    (r"studiare-ext-", "lenz-plus-"),
    (r"'studiare-ext'", "'lenz-plus'"),
    (r"\bSTX\b", "LZP"),
    (r"\bstx(?=[A-Z_\-.\w])", "lzp"),
    (r"\bstx\b", "lzp"),
    (r"_stx_", "_lzp_"),
]

# Lines matching these still talk about Studiare and must be adapted by hand
# (see the mapping table in SKILL.md).
NEEDS_REVIEW = re.compile(
    r"studiare|Studiare|codebean|scdarkcolors|darkMode|--primary_color|--secondary_color|"
    r"--font_body|--dark_|--menu_heading|--fallback-font|\bsc_|\bstudi_|\.sc-|sc-cart|"
    r"off-canvas|register-modal|mobile-btm|fonawesome|fontawesome|استادیار|Theme_Bridge|is_course|teacher|"
    r"\bdark\b|'dark'|dark_mode|_course\b|replace_theme_nav|lift_fixed_elements|back_to_top|guest_action|login"
)


def dest_name(rel: str) -> str:
    return rel.replace("studiare-extensions", "lenz-plus")


def port(text: str) -> str:
    for pattern, replacement in RENAMES:
        text = re.sub(pattern, replacement, text)
    return text


def main(argv):
    args = [a for a in argv if not a.startswith("--")]
    force = "--force" in argv
    dry = "--dry-run" in argv
    if not args:
        print(__doc__)
        return 2

    src = SRC_ROOT / args[0]
    if not src.is_file():
        print(f"missing source: {src}")
        return 1
    dest = DEST_ROOT / (args[1] if len(args) > 1 else dest_name(args[0]))

    ported = port(src.read_text(encoding="utf-8"))

    if dest.exists() and not force and not dry:
        print(f"exists, not overwritten (use --force): {dest.relative_to(ROOT)}")
    elif not dry:
        dest.parent.mkdir(parents=True, exist_ok=True)
        dest.write_text(ported, encoding="utf-8")
        print(f"wrote {dest.relative_to(ROOT)} ({ported.count(chr(10))} lines)")

    review = [
        (n, line.strip())
        for n, line in enumerate(ported.splitlines(), 1)
        if NEEDS_REVIEW.search(line)
    ]
    if review:
        print(f"{len(review)} line(s) to adapt:")
        for n, line in review:
            print(f"  L{n}: {line[:160]}")
    else:
        print("nothing Studiare-specific left")
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
