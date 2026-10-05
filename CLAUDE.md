# Lenz Plus — project guide

A WordPress plugin that adds features to the **Lenz** photography theme (v1.6.1.0, by MohammadAmin Noorani, sold on rtl-theme.com). It is the Lenz counterpart of **Studiare Extensions** (the plugin we built for the Studiare theme): the same architecture, admin look and rules, ported module by module.

Modules: **Bottom navigation** (mobile bar), **Support button** (floating contact button), **Builder** (Elementor templates and widgets that rebuild the nine designs in `Design/`, plus their header and footer).

- The user speaks Persian: reply in Persian unless asked otherwise. Code, comments and source strings are English; Persian comes from the `fa_IR` translation.
- The shipping plugin lives in `lenz-plus/`. Everything outside it (docs, tools, configs, `reference/`, `.dev/`, `Design/`) is for development only.
- `Theme.zip` (the Lenz package) and `Studiare-Extentions.zip` are references only. Never edit them, and never ship code copied from the Lenz theme (commercial). Code may be ported from Studiare Extensions (ours).
- Work goes **phase by phase** (`docs/ROADMAP.md`). Use the `lenz-plus-phase` skill to start or resume a phase.

## Start of every session

1. Read `docs/ROADMAP.md`: the current phase, its unticked tasks and the Notes.
2. Answer code questions from the knowledge graph first: `graphify query "<question>"` (covers `lenz-plus/` and `reference/`). Read files after the graph has pointed to them.
3. Background docs: `docs/reference/lenz-theme.md` (theme hooks, variables, selectors), `docs/reference/studiare-architecture.md` (what to port and how), `docs/reference/design-inventory.md` (tokens, sections, widget list). The Studiare guide is `reference/studiare-CLAUDE.md`.
4. Porting a Studiare file: `port-from-studiare` skill. Turning a design section into a widget: `design-to-elementor` skill. UI/UX gaps the designs leave open: `ui-ux-pro-max` skill.

## Non-negotiable rules

1. **Clean code.** Small single-purpose classes and functions, descriptive names, no dead code, no duplicated logic. Follow WordPress Coding Standards (`phpcs.xml.dist` must pass with 0 errors and 0 warnings).
2. **Comments explain *why*.** Every file and class has a docblock that says what it is for. Every public method has a docblock. Inline comments cover non-obvious decisions (theme quirks, browser workarounds), never what the next line obviously does.
3. **Follow the theme by default.** Bottom nav and support button colours and fonts come from Lenz's CSS variables (`--secondary-1`, `--primary-1`, `--text-main`, `--primary-2`, `--main-font`), and admins can override them. An empty colour setting means "use the theme's colour". Builder sections use the design's brand palette (editable under Builder → Brand).
4. **Never break the site.** Degrade gracefully when Lenz, WooCommerce or Elementor is missing. Real `<a href>` links keep working without JS. Mobile-only UI is hidden with CSS above the breakpoint (no `wp_is_mobile()`), so it is safe with page caching.
5. **Security.** Every setting passes through `Core\Sanitizer` (schema based). Admin AJAX checks the nonce and `manage_options`. Escape on output. User SVG goes through `Icon_Library::sanitize_svg()` on save and again on output. Public forms use a honeypot and a per-visitor rate limit (no nonce: pages are cached).
6. **RTL first, i18n always.** Use logical CSS properties (`inset-inline-start`, `margin-inline-end`). Every string is wrapped in `__()` and friends with the text domain `lenz-plus`. JS strings are passed from PHP. PHP regexes on text need the `u` flag: without it `\R` also matches the byte 0x85 inside letters such as «م» and cuts Persian words apart.
7. **Accessibility.** Touch targets of at least 44px, a visible `:focus-visible` ring, `aria-*` on sheets, toggles and accordions, `prefers-reduced-motion` respected, hidden labels kept for screen readers.
8. **Support PHP 7.4 and WordPress 6.0 or newer.** Do not use PHP 8-only syntax (`match`, union types, constructor promotion, nullsafe `?->`, `str_contains`).
9. **No CDN at runtime.** Fonts come from the theme (IRANYekanXFANum) or are bundled; icons are bundled SVG packs (`assets/icons/`) or the theme's `lenz-icon` font. Google Fonts / Material Symbols from the designs are never loaded.

## Names

| Thing | Value |
| --- | --- |
| Plugin folder / main file / slug | `lenz-plus/` / `lenz-plus.php` / `lenz-plus` |
| Namespace | `LenzPlus\…` (PSR-4 under `includes/`) |
| Constants | `LENZ_PLUS_VERSION`, `LENZ_PLUS_FILE`, `LENZ_PLUS_DIR`, `LENZ_PLUS_URL`, `LENZ_PLUS_MIN_PHP` |
| Options, filters, actions | `lenz_plus_<module>`, `lenz_plus_*` |
| Text domain | `lenz-plus` |
| CSS classes, handles, data attributes | `lzp-*`, `data-lzp-*` |
| AJAX actions, post types, meta | `lzp_*`, `lzp_template` / `lzp_message` / `lzp_subscriber`, `_lzp_*` |
| JS globals | `window.LZP` (admin shell), `window.lzpAdmin`, `window.lzpBuilder` |
| Admin menu / slugs | «Lenz+» / `lenz-plus`, `lenz-plus-<module>` |
| Elementor category / widget names | `lenz-plus` («Lenz+») / `lzp-<widget>` |

## Layout

The plugin mirrors Studiare Extensions. The tree below is the target; sections are filled in as phases land (see the roadmap).

```
lenz-plus/                           ← the plugin (zip this folder)
├── lenz-plus.php                    bootstrap: constants LENZ_PLUS_*, PHP check, autoloader, boots Plugin on `init`
├── uninstall.php                    deletes every `lenz_plus_*` option, plugin posts and meta (multisite aware)
├── includes/                        PSR-4: LenzPlus\Foo\Bar_Baz → includes/Foo/Bar_Baz.php
│   ├── Plugin.php                   loads textdomain, instantiates modules (filter `lenz_plus_modules`), boots admin
│   ├── Core/                        Module, Sanitizer, Theme_Bridge (Lenz), Icon_Library, Search_Query, Asset, Arr, Color, Site, Persian
│   ├── Admin/                       Admin (menu «Lenz+»), Ajax_Controller, Fields, views/{layout,dashboard}.php
│   └── Modules/
│       ├── Bottom_Nav/              mobile bottom bar (port of Studiare's, adapted to Lenz)
│       ├── Support_Button/          floating contact button
│       └── Builder/                 templates, presets, Elementor widgets, header/footer, page/blog/portfolio/course rendering, forms
├── assets/
│   ├── admin/                       admin.css, admin.js (window.LZP shell), fonts/Vazirmatn (bundled, OFL)
│   ├── icons/                       catalog.json + packs/*.json (generated by tools/build-icons.mjs)
│   └── modules/<module>/            css/js sources + generated *.min.*
└── languages/                       .pot, fa_IR .po/.mo
docs/                                ROADMAP.md (resume point), reference/*.md
tools/                               build-icons.mjs, icon-map.mjs, minify.mjs, build-zip.sh, i18n.sh, dev-site.sh (+ dev/: test mu-plugin, license stub, viewport harness)
phpcs.xml.dist, composer.json        WordPress-Extra + PHPCompatibilityWP (7.4+)
reference/                           (ignored) Lenz theme copy, Lenz demo files, Studiare plugin, its guide and tools
.dev/                                (ignored) local test site (wp/), WP-CLI (bin/wp, wrapper bin/lwp), downloads
Design/                              the nine design mockups (+ assets, uploads)
```

## Adding a new feature (module)

1. Create `includes/Modules/<Name>/Module.php` that extends `Core\Module`. Implement `id()`, `title()`, `description()`, `icon()`, `defaults()`, `sanitize()`, `boot()` and `render_admin()`.
2. Put settings in a `Schema` class (`defaults()` and `fields()` with the same shape) and sanitize with `Sanitizer::apply()`.
3. Register it in `Plugin::register_modules()` (the default list). The admin menu, dashboard card, save/reset/toggle AJAX and the store all work automatically.
4. Build the panel with `Admin\Fields` (`toggle`, `select`, `segmented`, `range`, `text`, `color`) inside `.lzp-card`. Use `data-lzp-show-if="path=a|b;!other"` for conditional fields.
5. Settings live in one option, `lenz_plus_<id>`. `Module::normalize()` upgrades old saved data.

## Lenz integration points (verified against Lenz 1.6.1.0)

Details and sources: `docs/reference/lenz-theme.md`.

| Need | Theme hook / selector |
| --- | --- |
| Active theme | `get_template() === 'lenz'` |
| Options | Redux row `lenz`; `\Lenz\Utils\Options::get_options( $defaults )` after `init`:0, else `get_option( 'lenz' )`; switches are `"1"`/`""` |
| Colour variables | `--body`, `--primary-1` (ink), `--primary-2` (muted/borders), `--secondary-1` (surface), `--secondary-2` (hover surface), `--text-main`, `--text-1..4`, `--btn_primary_*`, `--btn_secondary_*`, `--red-1`, `--green-1` (file `uploads/lenz.css`, handle `lenz-custom`) |
| Font | `--main-font` (default IRANYekanXFANum) |
| Dark mode | none (the dark demo only changes Redux colours) |
| Header markup | `body.sticky-header|static-header` → `div#container` → `header#header-container > #header.page-width > #header_inner` (`#header-logo`, `#header-menu-wrap`, `#header-actions-wrap`, `button#header-mobile-menu-btn`) |
| Footer markup | `footer#site-footer` (`#main-footer`, `#bottom-footer`) → `wp_footer()` → `</div>` (closes `#container`) |
| Mobile menu | `#overlay` + `div#mobile-menu.closed` printed on `wp_footer`:1 (location `main-menu-mobile`); open = remove `.closed`, add `body.mobile-menu-opened`, fade in `#overlay`; close = `#overlay` / `.mobile-menu-close` click |
| Mini cart | `.lenz-cart-wrap` hover only; fragments replace `.lenz-mini-cart-content` and `.lenz-cart-count` |
| Account | `.lenz-account-btn`; links `header-account-link` / `header-account-link-guest` (else my-account) |
| Reserve button | `show-header-reserve-btn`, `header-reserve-text`, `header-reserve-link` |
| Mobile support block | `mobile-menu-support-show`, `-top-text`, `-top-link`, `-bottom-text`, `-bottom-link` |
| Post types | `portfolio` (`portfolio-cat`, `portfolio-tag`; meta `_gallery`, `_external_links`), `video` (`video-cat`, `video-tag`), `expert` (`_position`, `_socials`, `_careers`); posts `_views` |
| Templates | `get_header()` → `main#page-body` (`.page-width` = 1440px boxed) → content → `get_footer()`; page meta `_show_title`, `_show_breadcrumb` (`"false"` hides) |
| Scripts / styles | styles `lenz`, `lenz-icons`, `lenz-custom`, `lenz-font-awesome` (Font Awesome **6.6 Free**, v5 names kept as aliases), all on every front-end page; script `lenz` (jQuery, `lenzVars`); icon font classes `lenz-icon-*` (styled through `[class^=lenz-icon-]`, `[class*=" lenz-icon-"]`) |
| Layers | `#overlay` 100, sticky header 99, `#mobile-menu` 10000 |
| Breakpoints | body classes `desktop` (>1200) / `tablet` (769–1200) / `mobile` (≤768); `.hide-desktop-1200`, `.hide-mobile` (≤767) |

## Module contracts

Each module section records the contracts that are not obvious from the code (markup shared by PHP and JS, theme quirks, cache rules). Write them when the module lands; keep them current.

### Core and admin (phase 1)
- **Theme options.** `Theme_Bridge::option()` reads `$GLOBALS['lenz']` when Redux has filled it, else the `lenz` option row. Never call Lenz's `Options::get_options()`: when the global is empty it returns only the defaults passed to it. Switches go through `Theme_Bridge::flag()` (`"1"` / `""`, missing = the field's default).
- **Palette.** `Theme_Bridge::palette()` returns the theme colours keyed by the CSS variable that carries them (`--primary-1` => `#000000`). Admin previews print these on their wrapper, because Lenz's stylesheets are not loaded in wp-admin; front-end CSS uses the variables directly with the same defaults as fallbacks.
- **Icons.** SVG packs come from `assets/icons/` (built from `tools/icon-map.mjs`). Two font packs need Lenz (`Icon_Library::is_available()`): `fontawesome` (FA6 Free, weights `far`/`fas`, `fa_class()`) and `lenz` (`lenz_class()` → `lenz-icon-<glyph>`, empty when the font has no glyph). `Icon_Library::svg( 'lenz', $key )` falls back lenz → lucide → tabler, so a font pack always has an SVG fallback. Put the `lenz-icon-*` class first in the attribute (Lenz's selector is `[class^=…]`).
- **Admin look.** Identical to Studiare Extensions (indigo `--lzp-brand`, Vazirmatn bundled); only names and copy change. Ported files keep their structure so fixes can flow between the two plugins.

### Bottom navigation (phase 2)
- Decisions taken: no `dark_mode` item (Lenz has no dark mode); one colour set falling back to Lenz variables; the cart always opens our own sheet (Lenz's mini cart is hover only); the "theme menu" action opens Lenz's `#mobile-menu`; z-index below 100 and hidden while `body.mobile-menu-opened`.
- Studiare's contracts that carry over unchanged: markup contract between `views/nav.php` and `renderNav()` in the admin JS, live search sends only button id + term (no nonce), sheets push a history entry for the Back button. See `reference/studiare-CLAUDE.md` → "Bottom navigation: key contracts".

### Support button (phase 3)
- Carries over Studiare's contracts (markup contract with the admin JS, works without JS through `<details>`, channels keyed by id, never call them «کانال» in Persian). Lifting: `--lzp-sb-lift` over the bottom nav and the Builder mobile buy bar only.

### Builder (phases 4–11)
- Brand palette defaults come from the designs: ink `#022D4F`, accent `#185E82`, text `#55636F`, sub `#3E5566`, muted `#8C9AA6`, line `#E3EBF1`, dashed `#D3DCE3`, chip `#E8EEF3`, soft `#F5F8FA`, dark surface `#0B3A5E`. The design props become global settings: `photo_tone` (grayscale/colour photos, `--lzp-photo-filter`) and `guides` (decorative guide lines, `--lzp-guides`).
- The designs have no media queries: every widget adds real breakpoints (1024 and 767) where `auto-fit` / `clamp()` is not enough.
- Header/footer replacement follows Elementor Pro's `theme-support.php` approach (`get_header` / `get_footer`), keeps `#container` and `wp_footer()`, and backs off when Elementor Pro has its own header/footer conditions.
- Courses are WooCommerce products flagged by the plugin's course meta box; other products keep Lenz's own product page.

## Workflows

- **Lint PHP:** `vendor/bin/phpcs` (reads `phpcs.xml.dist`). Must report 0/0. `vendor/bin/phpcbf` fixes formatting. Install once with `composer install`.
- **Syntax check:** `find lenz-plus -name '*.php' -exec php -l {} \;` and `node --check` on every JS source.
- **Minify:** after editing front-end CSS/JS run `node tools/minify.mjs` (esbuild via npx). The plugin serves the `.min` copies unless SCRIPT_DEBUG; build-zip.sh runs it too. Edit the sources, never the `.min` files.
- **Icons:** edit `tools/icon-map.mjs` (semantic key → name per pack, Persian label, keywords, Font Awesome class, Lenz glyph), then run `node tools/build-icons.mjs`. Packs are downloaded from jsDelivr at build time and committed.
- **Translations:** `bash tools/i18n.sh` regenerates `lenz-plus.pot`, updates `lenz-plus-fa_IR.po` (new entries are filled from Studiare Extensions' fa_IR translations when the English source is identical) and lists the entries still untranslated. Translate those in the `.po` (Persian punctuation «», ZWNJ نیم‌فاصله; one line per entry), then `bash tools/i18n.sh mo` compiles the `.mo`. Needs gettext (`brew install gettext`) and `.dev/bin/lwp`.
- **Knowledge graph:** the post-commit hook rebuilds code nodes after every commit; run `graphify update .` by hand after large changes. `.graphifyignore` keeps the graph code-only (no LLM cost) and re-includes `reference/`.
- **Package:** `bash tools/build-zip.sh` creates `dist/lenz-plus-<version>.zip`.
- **Release:** bump `Version` and `LENZ_PLUS_VERSION` in `lenz-plus.php`, update `Stable tag` and the changelog in `readme.txt`. Bump the version to ship preset changes.

## Definition of done (end of every phase)

1. `php -l` on every PHP file and `node --check` on every JS source.
2. `vendor/bin/phpcs` reports 0 errors and 0 warnings.
3. `node tools/minify.mjs`.
4. Browser check on the local site (Claude in Chrome, viewport harness `/viewport.html`) at 390×844 and 1440×900, RTL: no console errors, nothing new in `.dev/wp/wp-content/debug.log`. Widgets are compared side by side with their `.dc.html` design (`&b=/design/<Page>`).
5. Translations: `bash tools/i18n.sh` reports 0 untranslated, then `bash tools/i18n.sh mo`.
6. Module contracts in this file updated; tasks ticked in `docs/ROADMAP.md`.
7. Commit `Phase N: <summary>` (the hook updates the graph).

## Testing

Local test site: `.dev/wp` (git-ignored), built from scratch in about 20 seconds by `bash tools/dev-site.sh` (downloads are cached in `.dev/downloads`). WordPress (fa_IR) on SQLite (`sqlite-database-integration` drop-in), WooCommerce, Elementor, Redux Framework, WordPress Importer, the Lenz demo pages and a «Main» menu on `main-menu` / `main-menu-mobile`. Elementor Pro and RTL-CareUnit are not installed (the plugin must not need them).

- **Lenz test copy:** the theme's files run unchanged except `Redux/RTL_License_*.php` (ionCube), replaced by `tools/dev/RTL_License_stub.php` (never shipped). Side effects: the Lenz options panel is hidden and Redux never fills `$GLOBALS['lenz']`, so `Theme_Bridge::option()` must fall back to `get_option( 'lenz' )` (it does on real sites too when the global is empty). Set test options with `.dev/bin/lwp option update lenz --format=json < file.json`.
- **Palettes:** the theme's compiled `uploads/lenz.css` is never written here; the test mu-plugin (`tools/dev/lzp-dev.php`) prints the same `:root` variables from the `lenz` option. Dark palette: `.dev/bin/lwp option update lenz --format=json < "reference/lenz-demo/Demo 2 (Dark)/redux_options.json"`; light: `.dev/bin/lwp option delete lenz`.
- **Demo content:** the Lenz demo XML holds only pages. Each phase seeds what it needs (portfolio items, posts, course products) with WP-CLI and records the commands in its roadmap notes.
- Start: `php -d memory_limit=1024M -S 127.0.0.1:8888 -t .dev/wp` (run in the background). Admin: `http://127.0.0.1:8888/wp-admin/` (user `admin`, password `admin`).
- WP-CLI: `.dev/bin/lwp …` (wrapper with memory and the path set).
- The single-threaded server hangs on outside requests, so `WP_HTTP_BLOCK_EXTERNAL` is on.
- `debug.log`: the test mu-plugin hides PHP 8.4 deprecations from WooCommerce/Elementor (PHPCompatibility guards our code). Check it with `grep -n 'lenz-plus\|Fatal' .dev/wp/wp-content/debug.log`.
- The plugin folder is symlinked into `wp-content/plugins/lenz-plus`.

Check with Claude in Chrome through the viewport harness (the Chrome window is maximised, so `resize_window` does not change the viewport): `http://127.0.0.1:8888/viewport.html?a=/<path>/&w=390` (or `&w=1440`) renders the page in an exact-width iframe (same origin: inspect it with `frames[0].document`), and `&b=/design/<Page>` shows the matching design beside it.
- bottom nav: all 5 styles with 2, 4, 5 and 7 items, light and Lenz dark-demo palettes, RTL; current page (home, shop, search, account, cart) with pretty and plain permalinks; search sheet (focus, Esc, Back, live results), cart sheet + count fragment, Lenz mobile menu, content sheet, back to top
- support button: with and without JS, one channel, greeting, lifting over the bottom nav and the buy bar
- builder: every preset opens in the Elementor editor; "Create page" and "Use as home page"; header/footer swap (desktop and mobile, sticky, Lenz mobile menu still works); portfolio archive/single, blog archive (page 2, category, search) and article (TOC, progress, copy link), courses archive (filters, card states) and course single (add to cart → checkout, request form, waitlist), home; compared with `Design/*.dc.html` at both widths; photo tone and guides settings
- forms: JS and plain posts, invalid fields, honeypot, rate limit, CSV export, inbox
- admin: every tab, save with Ctrl/⌘+S, reset, dashboard toggles, reload persistence
- no console errors, nothing new in `debug.log`

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
