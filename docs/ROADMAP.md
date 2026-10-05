# Lenz Plus roadmap

This file is the **resume point**. Every session starts here (see the `lenz-plus-phase` skill). Tick a box as soon as its task is done and verified, not at the end of the phase, so a session that hits a limit can be continued exactly where it stopped. Write short notes under "Notes" when something is left half done.

Status keys: `[ ]` to do · `[x]` done · `[~]` started (see Notes) · `[-]` dropped (say why)

**Current phase:** 1

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
- [ ] `lenz-plus.php`, `uninstall.php`, `includes/Plugin.php`, `includes/Autoloader.php`
- [ ] Core: `Module`, `Sanitizer`, `Asset`, `Arr`, `Color`, `Site`, `Persian`
- [ ] Core: `Search_Query` (Lenz hub pages)
- [ ] Core: `Icon_Library` (FA5 free pack loaded by Lenz, new `lenz` pack of `lenz-icon-*` glyphs)
- [ ] Core: `Theme_Bridge` for Lenz (`is_active`, `option`, `palette`, `font`, account URLs, reserve link, mobile menu location, `is_course`, experts)
- [ ] Admin: `Admin`, `Ajax_Controller`, `Fields`, `views/layout.php`, `views/dashboard.php`
- [ ] Admin assets: `admin.css` (same look, Lenz+ brand), `admin.js` (`window.LZP`, `window.lzpAdmin`), bundled Vazirmatn
- [ ] Icons: `tools/icon-map.mjs` (+ Material icons of the designs), `node tools/build-icons.mjs` → `assets/icons/`
- [ ] Languages: `.pot`, `lenz-plus-fa_IR.po/.mo`
- [ ] Verify: activates without errors, menu Lenz+, dashboard, theme chip; also without Lenz active

## Phase 2 — Bottom navigation (منوی پایین موبایل)
- [ ] Port `Modules/Bottom_Nav/*` PHP (Module, Schema, Styles, Item_Types, Item_Resolver, Renderer, Style_Vars, Frontend, Search_Scope, Live_Search, views)
- [ ] Drop `dark_mode`, Studiare native-bar replacement, Studiare lifts; one colour set (no light/dark pair)
- [ ] Lenz menu action (`#header-mobile-menu-btn` click, or `#mobile-menu`/`#overlay`/`body.mobile-menu-opened`), WP-menu sheet fallback (`main-menu-mobile`)
- [ ] Cart sheet (always ours), count fragment `lzp-bn-cart-count`
- [ ] Account links from Lenz options, reserve item from `header-reserve-link`
- [ ] CSS tokens fall back to Lenz variables, z-index < 100, hidden while the Lenz menu is open, footer spacing
- [ ] `bottom-nav.js`, `bottom-nav-admin.js` (preview mirrors `nav.php`)
- [ ] Admin panel views/admin.php with Lenz preview variables
- [ ] Verify: 5 styles × 2/4/5/7 items, light and Lenz dark-demo palettes, current page, search sheet + live results, cart, menu, Back button

## Phase 3 — Support button (دکمه پشتیبانی)
- [ ] Port `Modules/Support_Button/*` (channels incl. Bale/Eitaa, native `<details>`, greeting)
- [ ] Tokens from Lenz variables; lifts: bottom nav, Builder mobile buy bar
- [ ] "Import from theme" suggestion from `mobile-menu-support-*`
- [ ] Verify: with and without JS, one channel = plain link, lifting over the bottom nav

## Phase 4 — Builder foundation (زیرساخت صفحه‌ساز)
- [ ] `Builder/Module`, `Schema` (kinds, brand defaults from the design, `photo_tone`, `guides`)
- [ ] `Template_Post_Type` (`lzp_template`), `Library` (+ hash upgrade), `Library_Ajax`, `Presets/Catalog`, `Presets/El`, `Presets/Blocks`
- [ ] `Renderer`, `Assets` (`lzp-tokens`, `lzp-builder`, per-page handles), `Resolver`, `Context`, `Design_Pages`
- [ ] `tokens.css` (brand tokens + design utilities: notch, watermark, guides, chips, buttons, dashed card, ink band, fadeUp; breakpoints 1024/767)
- [ ] Elementor `Integration` (category Lenz+, container `lzp_surface`), `Widgets/Base`, `Page_Base`, `Parts`, `Picture`
- [ ] Widgets: `Heading`, `Text`, `Button`
- [ ] Admin: Library tab (create, duplicate, restore, delete, edit with Elementor), settings tabs, `builder-admin.css/js`
- [ ] `sync_kit_colors`
- [ ] Verify: create/edit a template, widgets render in the editor and on the front end

## Phase 5 — Header and footer (هدر و فوتر)
- [ ] `Header_Footer` for Lenz (Elementor Pro `theme-support.php` approach, keep `#container`, back off when Pro has header/footer conditions)
- [ ] Widgets: `Site_Logo`, `Nav_Menu` (+ drawer), `Header_Action` (CTA + phone icon), `Brand_Box`, `Social_Links`, `Link_Column`, `Contact_Box`, `Copyright`
- [ ] Presets: header (desktop + 64px mobile bar), footer full + footer simple
- [ ] Verify: desktop/mobile slots, sticky + blur, Lenz mobile menu still works, admin bar offset

## Phase 6 — Static widgets, About and Services (درباره و خدمات)
- [ ] `Page_Hero` (split, text-only), `CTA_Band`, `Stats`, `Icon_Features`, `Process_Steps`, `Faq`
- [ ] `Framed_Band`, `Timeline`, `Awards`, `Simple_List`, `Quote`
- [ ] `Service_Detail`, `Anchor_Tiles`, `Pricing_Plans`
- [ ] Presets `About.php`, `Services.php`; "Create page"
- [ ] Verify against `About.dc.html` and `Services.dc.html` at 390 and 1440

## Phase 7 — Forms and requests inbox (فرم‌ها)
- [ ] `Public_Form`, `Contact_Messages` (`lzp_message`), `Contact_Inbox` («درخواست‌ها»), `Newsletter` (`lzp_subscriber` + topic)
- [ ] Widgets `Request_Form`, `Newsletter_Form`
- [ ] Verify: JS and no-JS posts, honeypot, rate limit, CSV export, email copy

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
- Phase 0: the local site runs the real Lenz files except `Redux/RTL_License_*.php` (ionCube), replaced by `tools/dev/RTL_License_stub.php` on the test copy only. Consequences: the Lenz options panel is hidden and `$GLOBALS['lenz']` is never filled, so `Theme_Bridge::option()` must fall back to `get_option( 'lenz' )` (phase 1). See `CLAUDE.md` → Testing.
- Phase 0: the Lenz demo XML has pages only (no portfolio items, posts, products or experts). Seed sample content in the phase that needs it (8 portfolio, 9 blog, 10 courses + experts).
- Phase 0: `resize_window` cannot shrink the maximised Chrome window; use `/viewport.html?a=…&w=390` for phone checks.
- Phase 0: the design mockups have no breakpoints and break at 390px (e.g. the Home hero's 3-column grid): every widget needs its own phone layout.
