---
name: design-to-elementor
description: Turn a section of the Lenz Plus design mockups (Design/*.dc.html — Home, About, Services, Portfolio, Project, Courses, Course, Blog, Article) into a Lenz Plus Elementor widget, its CSS and an El::box preset. Use whenever building or restyling a Builder widget or preset from the designs, mapping design colours/type/icons to tokens, or comparing a rendered widget with its design.
---

# Design section → Elementor widget + preset

The designs are static RTL mockups made with Claude Design. They are the visual spec; the plugin rebuilds them as **reusable, configurable widgets** (not one-off HTML) placed by **presets** written in PHP with `Presets/El.php`. Inventory of every page, section and widget candidate: `docs/reference/design-inventory.md`.

## 1. Read the section (don't open the whole file)

```bash
python3 .claude/skills/design-to-elementor/section.py Home            # numbered list of sections
python3 .claude/skills/design-to-elementor/section.py Home 4          # indented HTML of section 4 (icons compacted)
python3 .claude/skills/design-to-elementor/section.py Home 4 --text   # visible text only (preset copy)
python3 .claude/skills/design-to-elementor/section.py Home --colors   # colour usage of the page
```

`style-hover="…"` / `style-focus="…"` attributes are the `:hover` / `:focus` states. `<icon material="…">` is a Material Symbols ligature; `<icon lenz="U+E0xx">` is a glyph of the theme's `lenz-icon` font.

## 2. Decide the widget

- Check `docs/reference/design-inventory.md` §D: the same pattern usually appears on several pages (e.g. stats on Home and About, process steps on Services and About). Build **one** widget with variants (a `style`/`layout` control), not one widget per page.
- Check whether a ported Studiare widget already covers it (`graphify query "<widget>"`): Faq, Stats, Timeline, Pricing_Plans, Testimonials, Post_*… Port and restyle rather than writing from scratch.
- Static content → repeater/text controls with the design's copy as defaults. Dynamic content (portfolio, posts, course products, experts) → query controls + `Context` helpers; never hardcode copy that comes from data.

## 3. Widget conventions

- File `lenz-plus/includes/Modules/Builder/Elementor/Widgets/<Name>.php`, `final class <Name> extends <Base>` (the base picks the stylesheet handle, like Studiare's `Home_Base` / `Blog_Base` / `Page_Base`). `get_name()` = `lzp-<name>`, category `lenz-plus`, keywords include `lenz`, `لنز`, `lzp`.
- Register it in `Elementor/Integration.php` (`WIDGETS`).
- Markup: BEM with the `lzp-` prefix (`.lzp-stats`, `.lzp-stats__item`, `.lzp-stats--tiles`). Semantic tags (`h2`/`h3` per context, `ul` for lists, `<details>` for accordions, `<figure>` for images, `<a>` for anything that navigates). Works without JS.
- Controls: content first (`start_content_section`), then style (`start_style_section`) using the Base helpers (`add_text_style`, `add_box_style`, `add_button_style`, `add_columns_control`, `add_gap_control`, `add_align_control`). Style controls write CSS variables or selectors; render reads only what it needs (`get_settings_for_display()`; controls read in render need `'render_type' => 'template'`).
- `is_dynamic_content()` false for static widgets (Elementor may cache them); never read widget settings inside it.
- Images: `Elementor\Picture` (aspect-ratio from the design, `photo_tone` filter via `var(--lzp-photo-filter)`, lazy loading). Placeholder: Elementor's placeholder image in presets.
- Persian digits in numbers through `Base::digits()`; prices through WooCommerce.

## 4. Map the design to tokens

Never paste raw hex values into widget CSS: use the tokens printed by `Assets` from Builder → Brand (defaults = the design):

| Design | Token |
| --- | --- |
| `#022D4F` / `var(--ink)` | `--lzp-ink` |
| `#185E82` | `--lzp-accent` |
| `#55636F` | `--lzp-text` |
| `#3E5566` | `--lzp-sub` |
| `#8C9AA6` | `--lzp-muted` |
| `#E3EBF1` | `--lzp-line` |
| `#D3DCE3` | `--lzp-dashed` |
| `#DCE3E9` | `--lzp-field-line` |
| `#E8EEF3` | `--lzp-chip` |
| `#F5F8FA` | `--lzp-soft` |
| `#0B3A5E` | `--lzp-dark-surface` |
| on-dark `#C4D3DF` / `#8FA9BE` / `#2A5878` / `#134669` | `--lzp-on-dark-text` / `-muted` / `-line` / `-hover` |
| `var(--photo-filter)` | `var(--lzp-photo-filter)` |
| `var(--guides)` | `var(--lzp-guides)` |
| font family | `var(--main-font, inherit)` (Lenz) |

Shared pieces live in `tokens.css` / `builder.css` utilities: buttons (`.lzp-btn--primary|secondary|on-dark|outline-dark`), chips (`.lzp-chip`), dashed card hover, the image notch (`.lzp-notch`), outline watermark (`.lzp-watermark`), guide lines (`.lzp-guides`), ink band, `fadeUp`. Reuse them; add a new utility only when a second widget needs it.

Radii, sizes and spacing: copy the design's `clamp()` values; radii 8/10/12/14/16/18 as in the inventory.

## 5. Responsive (the designs have no media queries)

Keep the design's `auto-fit` / `minmax()` / `clamp()` behaviour, then add explicit breakpoints where it breaks: **1024px** (tablet) and **767px** (phone). Check every widget at 390px: no horizontal scroll, touch targets ≥ 44px, multi-column heroes stack (image after text unless the design says otherwise), `span 2` grids fall back to one column, long Persian words wrap. Elementor phone containers measure widgets at zero width: give picture/grid widgets a definite width (the `:is(...)` width rule at the top of the stylesheet).

## 6. Icons

No Material Symbols font and no CDN. Every ligature used in the designs is already mapped to a semantic key of `Core\Icon_Library`: the `material` field in `tools/icon-map.mjs` (e.g. `north_west` → `arrow-forward`, `calendar_month` → `calendar`, `photo_camera` → `camera`, `request_quote` → `receipt`). A new ligature gets a new key there, then `node tools/build-icons.mjs`. Theme glyphs (`<icon lenz="U+E0xx">`: telegram e000, quote e001, location e002, instagram e003, phone e005, whatsapp e00c, website e00d, call e00e, chevron-down e01d, square-tick e024, play e02f, play-circle e034, search e040) map to keys with a `lenz` field; widgets draw them with `Icon_Library::lenz_class()` when Lenz is active and fall back to the SVG pack otherwise. `arrow-forward` points up-left (RTL forward): mirror it with `transform: scaleX(-1)` under `[dir=ltr]`.

## 7. Preset

Add or extend `Presets/<Page>.php` and register it in `Presets/Catalog.php`. Build with `El::box()` / `El::w()` and `Blocks` helpers; `'boxed' => true` for the 1280 container; explicit `width` on every column of a row; `'fill' => true` for text beside actions. Copy the design's text as default content (Persian is allowed in presets because it is demo content, wrapped in `__()` with English source strings when it is UI copy). Bump `LENZ_PLUS_VERSION` when a shipped preset changes.

## 8. Verify side by side

Start the local site and open `http://127.0.0.1:8888/viewport.html?a=/<page-path>/&b=/design/<Page>&w=390` (then `&w=1440`) with Claude in Chrome: the page built from the preset and its design mockup render side by side at the same exact width. Compare: spacing, type scale, colours, borders, hover/focus states, RTL order. Also check `photo_tone` (colour/greyscale) and `guides` on/off, keyboard focus, and reduced motion. Fix differences in the widget CSS, not in the preset.
