#!/usr/bin/env python3
"""
Lists classes and public methods in lenz-plus/ that have no docblock.

CLAUDE.md requires one on every class and public method; WordPress-Extra does
not check it, so this script does. Exit code 1 when something is missing.

Usage (from the repository root):
    python3 tools/docblocks.py            # whole plugin
    python3 tools/docblocks.py <path>...  # some files or folders
"""

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1] / "lenz-plus"
DECLARATION = re.compile(r"^\s*(?:(?:final|abstract)\s+)?class\s+\w+|^\s*public\s+(?:static\s+)?function\s+\w+")


def missing_in(path: Path):
    lines = path.read_text(encoding="utf-8").splitlines()
    for number, line in enumerate(lines):
        if not DECLARATION.match(line):
            continue
        previous = number - 1
        while previous >= 0 and lines[previous].strip().startswith("#["):
            previous -= 1
        if previous < 0 or not lines[previous].strip().endswith("*/"):
            yield number + 1, line.strip()


def main(argv):
    targets = [Path(arg) for arg in argv] or [ROOT]
    files = []
    for target in targets:
        files.extend(sorted(target.rglob("*.php")) if target.is_dir() else [target])

    found = 0
    for file in files:
        for number, line in missing_in(file):
            print(f"{file}:{number}: {line}")
            found += 1

    print(f"{found} declaration(s) without a docblock")
    return 1 if found else 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
