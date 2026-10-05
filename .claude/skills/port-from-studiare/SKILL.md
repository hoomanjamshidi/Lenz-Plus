---
name: port-from-studiare
description: Port code from the Studiare Extensions plugin (reference/studiare-extensions/) into the Lenz Plus plugin (lenz-plus/). Use whenever a Lenz Plus task says "port", when creating a Lenz Plus file that has a Studiare counterpart (Core, Admin, Bottom_Nav, Support_Button, Builder classes, widgets, CSS/JS, tools), or when adapting Studiare theme couplings (Theme_Bridge, scdarkcolors, --primary_color, Studiare selectors) to the Lenz theme.
---

# Port from Studiare Extensions to Lenz Plus

Studiare Extensions is our own plugin, so its code may be reused. The goal is the same architecture and admin look with Lenz-specific integration. Background: `docs/reference/studiare-architecture.md` (what each file does, what is generic) and `docs/reference/lenz-theme.md` (Lenz hooks, variables, selectors).

## Workflow per file

1. Find the source: `graphify query "<class or feature>"`, or the path in `docs/reference/studiare-architecture.md`.
2. Copy it with the mechanical renames applied:
   ```bash
   python3 .claude/skills/port-from-studiare/port.py includes/Core/Sanitizer.php
   python3 .claude/skills/port-from-studiare/port.py assets/modules/bottom-nav/css/bottom-nav.css
   python3 .claude/skills/port-from-studiare/port.py studiare-extensions.php   # → lenz-plus/lenz-plus.php
   ```
   It writes into `lenz-plus/` (never overwrites without `--force`) and lists every line that still mentions Studiare things. `--dry-run` only lists them.
3. Fix each listed line with the mapping below, then read the whole file once: drop what Lenz does not need (dead code is not allowed), rewrite comments that explain Studiare quirks, keep every docblock accurate.
4. `php -l` / `node --check` the file. Tick the roadmap task when it works.

Port whole files rather than rewriting them; keep the structure so the two plugins stay comparable. Do not port `Modules/Theme_Fixes` or the Studiare LMS pieces (lessons, teachers, `_studiare_course*`, subscriptions).

## Mechanical renames (done by port.py)

| Studiare | Lenz Plus |
| --- | --- |
| `StudiareExt\…` | `LenzPlus\…` |
| `STUDIARE_EXT_*` | `LENZ_PLUS_*` |
| `studiare_ext_*` (options, filters) | `lenz_plus_*` |
| `studiare-extensions` (text domain, slug, file) | `lenz-plus` |
| `studiare-ext`, `studiare-ext-<id>` (admin slugs) | `lenz-plus`, `lenz-plus-<id>` |
| `Studiare Extensions` / `Studiare+` | `Lenz Plus` / `Lenz+` |
| `studiare-plus` (Elementor category) | `lenz-plus` |
| `stx-*`, `stx_*`, `_stx_*`, `data-stx-*`, `--stx-*` | `lzp-*`, `lzp_*`, `_lzp_*`, `data-lzp-*`, `--lzp-*` |
| `window.STX`, `stxAdmin`, `stxBuilder` | `window.LZP`, `lzpAdmin`, `lzpBuilder` |

## Theme couplings to adapt by hand

| Studiare | Lenz |
| --- | --- |
| `Theme_Bridge::is_active()` (`studiare`) | `get_template() === 'lenz'` |
| `Theme_Bridge::option()` (`codebean_option`) | `\Lenz\Utils\Options::get_options()` (after `init`:0) or `get_option( 'lenz' )` |
| `--primary_color` (brand) | `--primary-1` (ink) |
| `--secondary_color` | `--primary-2` (muted) / `--secondary-2` (hover surface) — pick by role |
| `--font_body-color` | `--text-main` |
| background / surface | `--body`, `--secondary-1` |
| `--font_body-font-family`, `--menu_heading-font-family`, `--fallback-font` | `--main-font` |
| dark mode `.scdarkcolors`, `--dark_*`, `localStorage.darkMode`, `.dark-mode-toggle` | **remove**: Lenz has no dark mode; one colour set |
| Studiare FA5 Pro (`fonawesomeall.min.css`, fal/far/fas/fad) | Lenz FA5 free (`lenz-font-awesome`, far/fas/fab) + `lenz-icon-*` glyphs |
| mini cart `.sc-cart-offcanvas` + `active` | our own cart sheet (Lenz's `.lenz-cart-wrap` opens on hover only) |
| mobile menu `.off-canvas-navigation`, `body.off-canvas-open` | click `#header-mobile-menu-btn`, else remove `.closed` from `#mobile-menu`, add `body.mobile-menu-opened`, show `#overlay` |
| login modal `.register-modal-opener` | link to `header-account-link-guest` / my-account |
| native bottom bar removal (`sc_adding_btm_menu_for_mobile`) | **remove**: Lenz has none |
| Studiare fixed elements to lift (`.sc_studi_btm_addtocart_fixed_btn_holder_container`, `.studi_custom_floating_btn`, `#back-to-top`) | **remove**: Lenz has none; lift only over our own bars |
| cart count fragment `span.studiare-cart-number` | ours only: `span.lzp-bn-cart-count` (Lenz replaces `.lenz-cart-count` with its own markup) |
| header/footer capture (`get_template_part` + `load_studipage_title`, `#footer`) | Elementor Pro style `get_header`/`get_footer` replacement keeping `#container` (see CLAUDE.md → Builder) |
| page meta `_studiare_disable_title` / `_studiare_disable_breadcrumbs` | not needed with `elementor_header_footer`; for theme templates `_show_title` / `_show_breadcrumb` = `"false"` |
| `load_studipage_title` filter | none: our views print only the template between `get_header()` and `get_footer()` |
| teachers (`teacher` CPT, `_studiare_teacher_job_title`) | experts (`expert` CPT, `_position`) |
| course flag `_studiare_course === 'yes'` | `_lzp_course === 'yes'` (our course meta box) |
| blog category icon/colour term meta | none in Lenz (drop or make it plugin term meta) |
| select2 on every `<select>` (Studiare global.js) | not applied by Lenz to our forms (only where it enqueues `lenz-select2`) |
| Persian keyword «استادیار» | «لنز» |
| admin brand colour `--stx-brand:#5b4cf0` | keep the same admin look (the user asked for an identical settings UI); only names and copy change |
