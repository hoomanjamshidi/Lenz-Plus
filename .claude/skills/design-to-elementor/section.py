#!/usr/bin/env python3
"""
Navigate a Claude Design mockup (Design/*.dc.html) without reading the whole file.

Usage (from the repository root):
    python3 .claude/skills/design-to-elementor/section.py <page>              # list top-level sections
    python3 .claude/skills/design-to-elementor/section.py <page> <n>          # print section n (indented HTML)
    python3 .claude/skills/design-to-elementor/section.py <page> <n> --text   # visible text of section n only
    python3 .claude/skills/design-to-elementor/section.py <page> --colors     # colour usage across the page

<page> is a design name (Home, About, Services, Portfolio, Project, Courses,
Course, Blog, Article) or a path. Sections are the children of the page's
root `<div dir="rtl">` (header, sections, footer), and the scripts are skipped.
"""

import re
import sys
from collections import Counter
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[3]
VOID = {"area", "base", "br", "col", "embed", "hr", "img", "input", "link", "meta", "source", "track", "wbr"}


class Node:
    def __init__(self, tag, attrs, parent):
        self.tag, self.attrs, self.parent, self.children = tag, dict(attrs), parent, []


class TreeBuilder(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.root = Node("#root", [], None)
        self.cur = self.root

    def handle_starttag(self, tag, attrs):
        node = Node(tag, attrs, self.cur)
        self.cur.children.append(node)
        if tag not in VOID:
            self.cur = node

    def handle_startendtag(self, tag, attrs):
        self.cur.children.append(Node(tag, attrs, self.cur))

    def handle_endtag(self, tag):
        node = self.cur
        while node is not self.root and node.tag != tag:
            node = node.parent
        if node is not self.root:
            self.cur = node.parent

    def handle_data(self, data):
        if data.strip():
            self.cur.children.append(data.strip())


def resolve(page):
    path = Path(page)
    if not path.is_file():
        path = ROOT / "Design" / f"Amirmahdi Asadi - {page}.dc.html"
    if not path.is_file():
        sys.exit(f"design not found: {page}")
    return path


def find(node, pred):
    for child in node.children:
        if isinstance(child, Node):
            if pred(child):
                return child
            hit = find(child, pred)
            if hit:
                return hit
    return None


def text_of(node):
    out = []
    for child in node.children:
        if isinstance(child, str):
            out.append(child)
        elif child.tag not in ("script", "style"):
            out.append(text_of(child))
    return " ".join(t for t in out if t)


def sections(tree):
    page = find(tree.root, lambda n: n.tag == "div" and n.attrs.get("dir") == "rtl")
    if not page:
        sys.exit("no <div dir=rtl> root found")
    return [c for c in page.children if isinstance(c, Node) and c.tag not in ("script", "style")]


def label(node):
    heading = find(node, lambda n: n.tag in ("h1", "h2", "h3"))
    title = text_of(heading) if heading else text_of(node)[:60]
    return f"<{node.tag}{' #' + node.attrs['id'] if 'id' in node.attrs else ''}> {title[:80]}"


ICON_FONT = re.compile(r"font-family:\s*'?(Material Symbols Rounded|lenz-icon)'?;.*?font-feature-settings:'liga';|font-family:\s*'?lenz-icon'?;?")


def icon_tag(node):
    """Collapse an icon-font span into one short line (the font boilerplate is noise)."""
    style = node.attrs.get("style") or ""
    if "Material Symbols" not in style and "lenz-icon" not in style:
        return None
    name = text_of(node).strip()
    pack = "material" if "Material Symbols" in style else "lenz"
    if pack == "lenz":
        name = " ".join(f"U+{ord(ch):04X}" for ch in name) or name
    rest = ICON_FONT.sub("", style).strip("; ")
    return f'<icon {pack}="{name}" style="{rest}" />'


def dump(node, depth=0, out=None):
    out = [] if out is None else out
    pad = "  " * depth
    if isinstance(node, str):
        out.append(pad + node)
        return out
    compact = icon_tag(node)
    if compact:
        out.append(pad + compact)
        return out
    attrs = "".join(f' {k}="{v}"' if v is not None else f" {k}" for k, v in node.attrs.items())
    if node.tag in VOID:
        out.append(f"{pad}<{node.tag}{attrs} />")
        return out
    out.append(f"{pad}<{node.tag}{attrs}>")
    for child in node.children:
        dump(child, depth + 1, out)
    out.append(f"{pad}</{node.tag}>")
    return out


def main(argv):
    if not argv:
        print(__doc__)
        return 2
    path = resolve(argv[0])
    tree = TreeBuilder()
    tree.feed(path.read_text(encoding="utf-8"))
    items = sections(tree)

    if "--colors" in argv:
        html = path.read_text(encoding="utf-8")
        for colour, count in Counter(c.upper() for c in re.findall(r"#[0-9a-fA-F]{6}\b", html)).most_common():
            print(f"{count:4}  {colour}")
        return 0

    numbers = [a for a in argv[1:] if a.isdigit()]
    if not numbers:
        for i, node in enumerate(items):
            print(f"{i:2}  {label(node)}  ({len(chr(10).join(dump(node)))} chars)")
        return 0

    node = items[int(numbers[0])]
    print(text_of(node) if "--text" in argv else "\n".join(dump(node)))
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
