# Lenz Plus roadmap

This file is the **resume point**. Every session starts here (see the `lenz-plus-phase` skill). Tick a box as soon as its task is done and verified, not at the end of the phase, so a session that hits a limit can be continued exactly where it stopped. Write short notes under "Notes" when something is left half done.

Status keys: `[ ]` to do · `[x]` done · `[~]` started (see Notes) · `[-]` dropped (say why)

**Current phase:** 8

Every phase ends with the **Definition of done** in `CLAUDE.md` (lint, minify, browser check at 390 and 1440, translations, CLAUDE.md contracts, graphify update, commit `Phase N: …`).

---

## Phase 0 — Setup, project knowledge, tools (راه‌اندازی)
- [x] `git init`, `.gitignore`, `.graphifyignore`
- [x] `reference/`: Lenz theme (no Libs, libs, demo photos), Lenz demo import files, Studiare plugin + guide + tools
- [x] graphify graph of the code (AST only), community labels
- [x] `docs/reference/lenz-theme.md`, `studiare-architecture.md`, `design-inventory.md`
- [x] `docs/ROADMAP.md` (this file)
- [x] `CLAUDE.md` + graphify section
- [x] Project skills: `lenz-plus-phase`, `port-from-studiare`, `design-to-elementor`
- [x] Tools: `phpcs.xml.dist`, `composer.json`, `tools/{minify.mjs,build-zip.sh,build-icons.mjs,icon-map.mjs}`
- [x] Composer dev dependencies (phpcs + WPCS + PHPCompatibilityWP)
- [x] Local test site in `.dev/` (WordPress fa_IR + SQLite + WooCommerce + Elementor + Redux + Lenz test copy), rebuilt by `tools/dev-site.sh`; WP-CLI wrapper `.dev/bin/lwp`; viewport harness `/viewport.html`
- [x] graphify post-commit hook, commit "Phase 0"

## Phase 1 — Plugin skeleton, Core, Admin (اسکلت افزونه)
- [x] `lenz-plus.php`, `uninstall.php`, `includes/Plugin.php`, `includes/Autoloader.php`
- [x] Core: `Module`, `Sanitizer`, `Asset`, `Arr`, `Color`, `Site`, `Persian`
- [x] Core: `Search_Query` (Lenz hub pages)
- [x] Core: `Icon_Library` (Font Awesome 6 Free loaded by Lenz, new `lenz` font pack of `lenz-icon-*` glyphs)
- [x] Core: `Theme_Bridge` for Lenz (`is_active`, `option` with `get_option` fallback, `flag`, `palette` keyed by CSS variable, `font`)
- [x] Admin: `Admin`, `Ajax_Controller`, `Fields`, `views/layout.php`, `views/dashboard.php`
- [x] Admin assets: `admin.css` (same look, Lenz+ brand, previews on Lenz variables), `admin.js` (`window.LZP`, `window.lzpAdmin`), bundled Vazirmatn
- [x] Icons: `tools/icon-map.mjs` (`lenz` glyphs, `material` mapping, 21 new keys for the designs), `node tools/build-icons.mjs` → `assets/icons/` (97 icons, 6 packs)
- [x] Languages: `.pot`, `lenz-plus-fa_IR.po/.mo` via `tools/i18n.sh` (Studiare compendium)
- [x] Verify: activates without errors, menu Lenz+, dashboard, theme chip; also without Lenz active

## Phase 2 — Bottom navigation (منوی پایین موبایل)
- [x] Port `Modules/Bottom_Nav/*` PHP (Module, Schema, Styles, Item_Types, Item_Resolver, Renderer, Style_Vars, Frontend, Search_Scope, Live_Search, views)
- [x] Drop `dark_mode`, Studiare native-bar replacement, Studiare lifts, `guest_action` (login popup), `cart_action=auto`; one colour set (`--lzp-bn-o-*`)
- [x] `Theme_Bridge`: `account_url()`, `reserve_link()`
- [x] Lenz menu action (`#header-mobile-menu-btn` click, else `#mobile-menu`/`#overlay`/`body.mobile-menu-opened`), WP-menu sheet fallback (`main-menu-mobile`)
- [x] Cart sheet (always ours) with Lenz's mini-cart template and quantity AJAX; count fragment `lzp-bn-cart-count`
- [x] Account links from Lenz options; new `reserve` (booking) and `archive` (post type list) button types; Lenz-flavoured default set (home, portfolio, booking, search, menu)
- [x] CSS tokens on Lenz variables, z-index 990, hidden while the Lenz menu is open; `lenz` icon pack as default
- [x] `bottom-nav.js`, `bottom-nav-admin.js` (preview mirrors `nav.php`, Lenz glyphs, archive/reserve rules)
- [x] Admin panel `views/admin.php` with Lenz preview variables, font and icon fonts
- [x] Live search honours Lenz `exclude_post_types`
- [x] Fix in `tools/build-icons.mjs`: keep `<rect>` width/height (was stripped from every element; Studiare's Lucide pack has 10 broken icons), fetch retries
- [x] Verify (390/1440 via harness): default set, current page (home, portfolio archive), Lenz menu open/close, search sheet + live results + Esc + Back, notch style + featured button, cart sheet + badge + quantity AJAX fragments, Lenz dark demo palette, admin preview + save, hidden at 1440, no console errors, empty debug.log
- [ ] Deferred to phase 12 (full matrix): every style × 2/4/5/7 items on the front end, plain permalinks, content sheet, back to top, selector, guest account view

## Phase 3 — Support button (دکمه پشتیبانی)
- [x] Port `Modules/Support_Button/*` (channels incl. Bale/Eitaa, native `<details>`, greeting)
- [x] Tokens from Lenz variables (primary button, surface, ink, `--main-font`); Studiare lifts, dark rules and `--lzp-sb-btt` removed; z-index 985; hidden while the Lenz menu is open
- [x] Lifts: bottom nav, Builder mobile buy bar
- [x] "Import from theme": `Theme_Bridge::support_phone()` + «استفاده کن» under the Phone channel
- [x] Shared preview helpers: `Theme_Bridge::preview_vars()`, `Admin::enqueue_theme_preview_assets()` (used by both modules)
- [x] Verify: suggestion fill + save, single channel = plain `tel:` link without script (Persian digits converted), lifted 24px above the bottom nav, two channels = `<details>` menu with brand colours, greeting, hidden while the Lenz menu is open, 1440 corner + label, no console errors, empty debug.log

## Phase 4 — Builder foundation (زیرساخت صفحه‌ساز)
- [x] `Builder/Module`, `Schema` (kinds, brand defaults from the design, `photo_tone`, `guides`)
- [x] `Template_Post_Type` (`lzp_template`), `Library` (+ hash upgrade), `Library_Ajax`, `Presets/Catalog`, `Presets/El` (`Presets/Blocks` moves to phase 5, its first user)
- [x] `Assets` (`lzp-tokens` inline, `lzp-builder`), `Resolver`, `Context`, `Design_Pages`, `Thumbs` (`Renderer` moves to phase 5 with the header/footer)
- [x] `tokens.css` (brand-derived tokens, radius scale, spacing, type) + design utilities in `builder.css` (surfaces, notch, watermark, guides, chips, buttons, fadeUp)
- [x] Elementor `Integration` (category Lenz+, container `lzp_surface`, `lzp_sticky`), `Widgets/Base` (`Page_Base`, `Parts`, `Picture` move to the phases that first use them: 6, 8, 9)
- [x] Widgets: `Heading`, `Text`, `Button` (shared `Heading::markup()` / `Button::markup()`)
- [x] Admin: Pages, Templates and Colours & options tabs, `builder-admin.css/js`
- [x] `sync_kit_colors` («افزودن به رنگ‌های سراسری المنتور»)
- [x] `uninstall.php`: delete `lzp_template` posts (their meta goes with them) and `_lzp_design`
- [x] Verify: create a template, widgets in the editor and on the front end (light and ink surfaces, 390/1440), "Create page", "Make it the home page", brand colour save → `--lzp-ink`

## Phase 5 — Header and footer (هدر و فوتر)
- [x] Port `Renderer` (moved from phase 4); `header` / `footer` kinds in `Schema::TYPES`, device slots, sticky modes and breakpoint in the schema, «هدر و فوتر» admin tab with pickers (`data-lzp-picker`) and previews (`Presets/Blocks` moves to phase 6, its first user)
- [x] `Header_Footer` for Lenz: buffers header.php on `get_header` (require_once) and footer.php from `get_footer` to `wp_footer`, swaps only `#header-container` / `#site-footer`, keeps `#container`, backs off when Elementor Pro has its own header/footer
- [x] Widgets: `Site_Logo`, `Nav_Menu` (+ drawer, or Lenz's mobile menu), `Header_Action` (CTA + phone icon), `Brand_Box`, `Social_Links`, `Link_Column`, `Contact_Box`, `Copyright`
- [x] Presets: header (desktop row + 64px phone bar), footer full + footer compact
- [x] Shared `lenz-menu.js` (`window.lzpLenzMenu.open()`), used by the bottom nav and the header menu button
- [x] Verify: desktop/mobile slots (same, mixed with Lenz's header), sticky + blur, Lenz mobile menu from both buttons, drawer (focus, Esc, sub-menus), dropdown, admin bar offset, editor, 390/1440

## Phase 6 — Static widgets, About and Services (درباره و خدمات)
- [x] `Presets/Blocks` (moved from phase 5): written for Lenz (bands, headings, two-column rows, links); Studiare's version is course/shop specific
- [x] `Section_Base` + `sections.css`, `Elementor/Picture` (ratio frames, striped placeholders)
- [x] `Page_Hero` (split, text-only), `CTA_Band`, `Stats`, `Icon_Features`, `Process_Steps`, `Faq`
- [x] `Framed_Band`, `Timeline`, `Simple_List`, `Quote`; `Awards` became the generic `Card_Grid` (awards, collaborations, areas of work)
- [x] `Service_Detail`, `Pricing_Plans`, `Photo_Grid`; `Anchor_Tiles` is `Icon_Features` with links and `size=lg`
- [x] Presets `About.php`, `Services.php`; "Create page"
- [x] Verify against `About.dc.html` and `Services.dc.html` at 390 and 1440 (headless Chrome side by side), editor loads, no console errors

## Phase 7 — Forms and requests inbox (فرم‌ها)
- [x] `Public_Form`, `Contact_Messages` (`lzp_message`, configurable fields), `Contact_Inbox` («درخواست‌ها»), `Newsletter` (`lzp_subscriber` + list + topic)
- [x] Widgets `Request_Form` (ink frame / card / none), `Newsletter_Form` (sign-up band: newsletter or waitlist), `forms.css`, `forms.js`; admin «فرم‌ها» tab with counts and CSV
- [x] `uninstall.php`: delete `lzp_message` and `lzp_subscriber` posts
- [x] Verify: JS and no-JS posts, invalid fields, honeypot, wrong form id, rate limit, duplicates, Persian digits, CSV export, inbox; the email copy path runs (`wp_mail` failures are caught, the request stays saved)

## Phase 8 — Portfolio archive and project (نمونه‌کارها)
- [ ] Project details meta box on `portfolio`
- [ ] `Portfolio_Pages` (`template_include` for single and archives/terms)
- [ ] Widgets `Portfolio_Grid`, `Featured_Projects`, `Project_Header`, `Project_Gallery`, `Checklist`, `Related_Items`, `Breadcrumb`
- [ ] Presets portfolio archive + project single
- [ ] Verify against `Portfolio.dc.html` and `Project.dc.html`

## Phase 9 — Blog archive and article (بلاگ)
- [ ] `Blog_Pages` + `views/blog.php`
- [ ] Port and restyle blog widgets (`Post_Grid`, `Post_Title`, `Post_Meta`, `Post_Content`, `Post_Toc`, `Post_Share`, `Reading_Progress`, `Post_Image`, `Post_Author`, `Post_Comments`, `Post_Categories`)
- [ ] New: `Featured_Post`, `Popular_Posts` (`_views`), `Promo_Box`, sidebar search
- [ ] Presets blog archive + article
- [ ] Verify against `Blog.dc.html` and `Article.dc.html` (page 2, category, search, TOC, progress, copy link)

## Phase 10 — Courses (دوره‌ها)
- [ ] Course flag and `expert` helpers (Builder `Context`, not `Theme_Bridge`: they are plugin data)
- [ ] Course details meta box on `product` (status, format, schedule, capacity, level, lists, curriculum, FAQ, instructor = `expert`, registration mode)
- [ ] `Single_Course` (`template_include` for course products only)
- [ ] Widgets `Course_Grid`, `Course_Hero`, `Checklist_Grid`, `Curriculum`, `Course_Outcome`, `Instructor_Box`, `Course_Buy_Box`, `Mobile_Buy_Bar`
- [ ] Presets course single (+ sticky sidebar variant) and the Courses page design
- [ ] Verify against `Courses.dc.html` and `Course.dc.html`; add-to-cart → checkout; request form; waitlist

## Phase 11 — Home page (صفحه اصلی)
- [ ] `Page_Hero` filmstrip variant + video `<dialog>`
- [ ] `Service_Cards`, `Testimonials`
- [ ] `Presets/Home.php` (all sections, booking form in the ink frame)
- [ ] Verify against `Home.dc.html`; "Use as the site's home page"

## Phase 12 — QA, translations, release (انتشار)
- [ ] Full test matrix (CLAUDE.md "Testing")
- [ ] Accessibility review, `/code-review` of the plugin
- [ ] Complete fa_IR translation
- [ ] `readme.txt` + changelog, `README.md`, `bash tools/build-zip.sh` → `dist/lenz-plus-1.0.0.zip`
- [ ] Final CLAUDE.md and graph

---

## Notes
- Phase 6: `docs/reference/*.md` (Lenz integration map, Studiare architecture, design inventory) were never committed: `.gitignore`'s `reference/` also matched `docs/reference/`. Fixed to `/reference/`; the facts that matter live in CLAUDE.md. A fresh clone needs `reference/` re-extracted from `Theme.zip` and `Studiare-Extentions.zip` (lenz/, lenz-demo/, studiare-extensions/, studiare-CLAUDE.md).
- Phase 7: test page «Forms test» (`/forms-test/`, option `lzp_test_forms_page`) holds both forms in every variant; rate-limit transients: `.dev/bin/lwp transient delete --all`.
- Phase 6: headless checks use Google Chrome through `puppeteer-core` (scratch script: full-page screenshots after scrolling, so Elementor's lazy backgrounds load; side-by-side slices with the design). Dev helper: re-install every preset and refresh pages made from them with `Library::install( $key, $id )` + `Library::copy_content()` + Elementor `files_manager->clear_cache()`.
- Phase 0: the local site runs the real Lenz files except `Redux/RTL_License_*.php` (ionCube), replaced by `tools/dev/RTL_License_stub.php` on the test copy only. Consequences: the Lenz options panel is hidden and `$GLOBALS['lenz']` is never filled, so `Theme_Bridge::option()` falls back to `get_option( 'lenz' )`. See `CLAUDE.md` → Testing.
- Phase 0: the Lenz demo XML has pages only (no portfolio items, posts, products or experts). Seed sample content in the phase that needs it (8 portfolio, 9 blog, 10 courses + experts).
- Phase 0: `resize_window` cannot shrink the maximised Chrome window; use `/viewport.html?a=…&w=390` for phone checks.
- Phase 1: Lenz loads Font Awesome 6.6 Free (not FA5); the plugin's `fontawesome` pack uses `far`/`fas`. The `lenz` font pack covers 29 semantic keys; the rest fall back to SVG.
- Phase 1: `debug.log` on the test site also gets Lenz's own booking-table queries failing on SQLite; filter with `grep -n 'lenz-plus\|Fatal'`.
- Phase 0: the design mockups have no breakpoints and break at 390px (e.g. the Home hero's 3-column grid): every widget needs its own phone layout.
- Phase 4: the Claude in Chrome extension may be disconnected. Fallback used for phase 4: headless Microsoft Edge (installed) driven by `puppeteer-core` from a scratch folder (`npm i puppeteer-core`, `executablePath: '/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge'`), logging in with "remember me" so `lzp_template` previews (404 for visitors) work.
- Phase 4: Lenz's Elementor "posts archive" widget uses Elementor Pro classes without checking for Pro (fatal in the editor and in Elementor's global-classes scan). The test mu-plugin stands in for them (`tools/dev/lzp-dev/elementor-pro.php`); real Lenz sites have Pro. Not our bug: never work around it in the plugin.
- Phase 4: Builder kinds grow per phase (`Schema::TYPES`, `Module::type_labels()`, `page_kinds()`, `Thumbs`): add a kind only with its first preset. Font Awesome is not offered as a builder icon pack (the designs use Material-like line icons and Lenz glyphs).
- Phase 5: test data. Footer menus «خدمات» (`footer-menu1`) and «دسترسی سریع» (`footer-menu2`) with custom links; a sub-item under «Sample Page» in «Main»; support button channels Instagram/Telegram/WhatsApp switched on (for the footer socials); a Lenz footer branch (`.dev/bin/lwp option update lenz --format=json` with `{"footer-addresses":{"footer-address-title":["دفتر مشهد"],"footer-address-location":["مشهد، خراسان رضوی — با هماهنگی قبلی"],"footer-address-link":[""],"footer-address-phone":["0912 000 0000"]}}`). Builder enabled with the «Header» and «Footer» presets on both devices.
- Phase 5: `Library::install()` from WP-CLI (no user) takes the raw-meta path; it now also clears `_elementor_element_cache`, else Elementor serves the old markup whose element IDs no longer match the new CSS (the header rows fell back to columns).
- Phase 5: Lenz's `overflow-x: hidden` on body and `#container` breaks `position: sticky`; builder.css switches them to `overflow-x: clip` while our header is on. On phones the admin bar is anchored to Lenz's relatively positioned body; builder.css moves it back into the html margin.
