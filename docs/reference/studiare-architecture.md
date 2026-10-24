# Studiare Extensions: architecture notes for porting

The model plugin is `studiare-extensions` (WordPress plugin for the Studiare LMS theme). A full copy lives in `reference/studiare-extensions/` (git-ignored); its project guide is `reference/studiare-CLAUDE.md` and its build tools are in `reference/studiare-tools/`. The original repository is `/Users/hooman/Documents/Codes/Studiare-Extentions/`.

Use `graphify query "…"` to find code in it (the graph covers `reference/`). This file records what is generic, what is Studiare-specific, and the APIs worth reusing. The rename table and the porting checklist are in the `port-from-studiare` skill.

## Size (source lines, no `.min`)

| Area | PHP | CSS/JS |
| --- | --- | --- |
| Bootstrap (main file, uninstall, Plugin, Autoloader) | 216 | – |
| `includes/Core` | 1,435 (Theme_Bridge 226) | – |
| `includes/Admin` + views | 648 | admin.css 2,313 + admin.js 549 |
| Bottom_Nav | 3,277 (views/admin.php 771) | 3,309 |
| Support_Button | 1,358 | 1,275 |
| Theme_Fixes (Studiare only, not ported) | 369 | 168 |
| Builder root PHP (25 files) | 5,638 | – |
| Builder `Presets/` | 5,863 | – |
| Builder `Elementor/` helpers | 2,321 | – |
| Builder `Elementor/Widgets/` (68 widgets + 4 bases) | 16,350 | – |
| Builder assets | – | CSS 11,919 + JS 3,434 |

## 1. Bootstrap and Core

| File | Lines | Port | Notes |
| --- | --- | --- | --- |
| `studiare-extensions.php` | 49 | copy + rename | header, `*_VERSION/FILE/DIR/URL/MIN_PHP`, PHP check notice, autoloader, `add_action('init', [Plugin::class,'instance'], 1)` |
| `uninstall.php` | 49 | copy + rename | multisite aware; deletes the plugin's post types, meta and every `prefix_%` option |
| `includes/Plugin.php` | 80 | copy + rename | singleton; textdomain; modules from filter `*_modules`; `new Admin($this)` in admin; `modules()`, `module($id)` |
| `includes/Autoloader.php` | 38 | copy + rename | PSR-4: `Ns\Foo\Bar_Baz` → `includes/Foo/Bar_Baz.php` |
| `Core/Module.php` | 127 | copy + rename | abstract `id/title/description/icon/defaults/sanitize/boot/render_admin`; `register`, memoized `settings` (`Arr::merge_defaults` + `normalize`), `is_enabled`, `save`, `set_enabled`, `reset`, `enqueue_admin_assets`, `admin_script_data`; option `prefix<id>`, admin slug `slug-<id>` |
| `Core/Sanitizer.php` | 284 | copy + rename | `apply($schema,$input,$defaults)`, `field()`, `color()`; types bool, int, float, enum, color, text, textarea, html, url, key, css_class, css_selector, font_family, id_list, key_list, svg, group, list |
| `Core/Asset.php` | 49 | copy + rename | `url($path)` / `contents($path)` serve `*.min.*` unless SCRIPT_DEBUG |
| `Core/Arr.php`, `Color.php`, `Site.php` | 51/40/29 | copy + rename | `merge_defaults`, `is_list`; `alpha($hex,$a)`; `is_rtl()`, `direction()` |
| `Core/Persian.php` | 152 | copy + rename | digits, Jalali dates |
| `Core/Icon_Library.php` | 225 | adapt | virtual `fontawesome` pack "Loaded by the Studiare theme" (FA5 Pro weights, lines 20–56, `fa_class` 117–136). The rest is generic: SVG packs, `svg()` fallback chain, `sanitize_svg()`, `decorate()` |
| `Core/Search_Query.php` | 252 | adapt | only `hub_page_ids()` (200–230) reads Studiare header/footer page options; filter `*_live_search_args` |
| `Core/Theme_Bridge.php` | 226 | rewrite | see below |

**Studiare `Theme_Bridge` (static):** `is_active()`, `option($key,$fallback)` (memoized `codebean_option`), `palette()`, `fonts()`, `user_has_course()`, `is_course()`, `dark_mode_available()`, `category_icon_id()`, `category_color()`, `disable_native_bottom_nav()`. Theme CSS variables are hardcoded in CSS files (`--primary_color`, `--secondary_color`, `--font_body-color`, `--dark_*`, `--font_body-font-family`, `--menu_heading-font-family`, `--fallback-font`; dark class `.scdarkcolors`).

## 2. Admin

| File | Lines | Studiare branding |
| --- | --- | --- |
| `Admin/Admin.php` | 203 | `add_menu_page('Studiare Extensions','Studiare+', …, 'studiare-ext', base64 SVG icon, 59)`, submenus Dashboard + one per module, UI icon pack `phosphor-duotone`, body class `stx-admin-page`, `window.stxAdmin = {ajaxUrl, nonce('stx_admin'), module, settings, moduleData, i18n}`, plugin-row "Settings" link |
| `Admin/Ajax_Controller.php` | 89 | none; `stx_save_settings`, `stx_reset_settings`, `stx_toggle_module`; `authorize()` = nonce + `manage_options` + module id |
| `Admin/Fields.php` | 212 | none |
| `Admin/views/layout.php` | 86 | brand name/tagline (22–25), theme chip (61–69) |
| `Admin/views/dashboard.php` | 58 | hero copy, inactive-theme notice (17–29) |
| `assets/admin/css/admin.css` | 2313 | bundled Vazirmatn; tokens on `.stx-admin` (`--stx-brand:#5b4cf0`, strong `#4a3bdc`, soft `#efedff`, ink `#131729`, bg `#f4f5fb`, radius 18/12); sections Font, Tokens, Shell, Buttons & controls, Cards & fields, Dashboard, Module head & tabs, Style & icon pack pickers, Items, Colour widget, Popover & dialog, Preview (1926–2079), Support button (2080–2170), Toasts, Responsive |
| `assets/admin/js/admin.js` | 549 | generic; `window.STX = {data,i18n,store,clone,escapeHtml,evaluate,getPath,toast,confirm,post}`; Ctrl/⌘+S (481–486); `beforeunload` while dirty; `[data-stx-bind]`, `[data-stx-show-if]` |

**Shell:** `#stx-admin.stx-admin` → `header.stx-topbar` (brand link; on module pages `[data-stx-dirty]`, Reset `[data-stx-reset]` with confirm, Save `[data-stx-save]` with spinner, disabled until dirty) → `.stx-shell` (`aside.stx-sidebar`: Dashboard, "Features", module links with status dot `[data-stx-status]`, theme chip; `main.stx-main`) → `.stx-toasts`.

**Module panel:** `.stx-module[data-stx-module]` (inline style recreating theme vars for previews) → `section.stx-module-head` (icon, h1, description, big `enabled` switch) → `.stx-module-body > .stx-settings > .stx-tabs[role=tablist][data-stx-tabs] > button.stx-tab[data-stx-tab]` → `section.stx-panel[data-stx-panel] > .stx-card > header.stx-card__head(h2,p)` + `.stx-fields.stx-fields--2 > .stx-field`.

**Dashboard:** `.stx-hero`, `.stx-module-grid > .stx-module-card` (icon, switch `[data-stx-module-toggle]`, h2, description, "Customize"), closing "More features are on the way" card.

**Fields** (shared `$args`: `help`, `show_if`; wrapper `.stx-field.stx-field--<type>`; id `stx-f-<path-with-dashes>`): `toggle($path,$label,$args)` (+ `chip` [text, tone]), `select($path,$label,$options,$args)`, `segmented(…)`, `range($path,$label,$min,$max,$step=1,$unit='px',$args)`, `text($path,$label,$args)` (`placeholder`, `type`, `dir`), `color($path,$label,$fallback,$args)` (empty = theme default, reset button).

## 3. Bottom_Nav (3,277 PHP lines)

| File | Lines | Studiare couplings |
| --- | --- | --- |
| `Module.php` | 247 | enqueues theme FA css (95–98); sends `Theme_Bridge::is_active/palette/fonts/dark_mode_available` to admin JS (126–129) |
| `Frontend.php` | 217 | `disable_native_bottom_nav` on `wp` (43), `is_course()` for hide rules (150), WooCommerce fragments |
| `Item_Resolver.php` | 560 | drops `dark_mode` unless available (101), cart "auto" → Studiare off-canvas, account guest `login-modal`, `build_menu` theme mode (267), FA `<i>` icons (407) |
| `Item_Types.php` | 131 | dark_mode label; cart `requires=woocommerce` |
| `Style_Vars.php` | 147 | font stacks (90–110), `theme_integration_css()` lifting Studiare fixed elements (114–135) |
| `Schema.php` | 387 | `replace_theme_nav`, `lift_fixed_elements`, `hide_theme_back_to_top`, `hide_on_course` |
| `Search_Scope.php` | 53 | single `post_type` rule for Studiare's search filter |
| generic | — | `Renderer.php` 317, `Live_Search.php` 161, `Styles.php` 90, views `nav.php` 38, `sheet.php` 32, `sheet-search.php` 62, `search-results.php` 64 |
| `views/admin.php` | 771 | theme vars (76–90), FA pack (155), dark notice (296), `#sc_notif_trigger` (701) |

Assets: `bottom-nav.css` 1642 (tokens 30–71 fall back to Studiare vars; dark `:where(.scdarkcolors)`; 286–291 dark glyph; 1471–1580 Studiare mini-cart markup), `bottom-nav.js` 690 (`toggleDarkMode` 77–98, `actions.cart` 483–491, `theme-menu` 494–500, `login-modal` 503–510, WooCommerce events 594), `bottom-nav-admin.js` 977 (mirrors `nav.php` + `Item_Resolver`).

Item types: `home`, `link`, `content`, `back_to_top`, `selector`, `search` generic; `menu` (theme mode is theme-specific), `cart` (WooCommerce; "auto" uses theme mini cart), `account` (login modal is theme-specific), `dark_mode` (Studiare only).

## 4. Support_Button (1,358 PHP lines)

Almost theme-free: `Module.php` 104, `Schema.php` 239, `Channels.php` 317 (Telegram, WhatsApp, Bale, Eitaa, Instagram, phone, email, link; Iranian number handling), `Frontend.php` 212, `views/button.php` 80. Only `views/admin.php` (406) reads the palette (31, 53–62, 125). `support-button.css` 693 (tokens 31–50 from theme vars; lifting 113–160 for bottom nav, Studiare's bar, add-to-cart bar, buy bar, back-to-top; 232 dark link fix). `support-button.js` 158 generic; `support-button-admin.js` 424.

## 5. Builder (30,813 PHP + 15,353 CSS/JS lines)

`Module.php` (461) always `register()`s Template_Post_Type, Newsletter, Contact_Messages, Assets, Single_Product, Blog_Pages, Header_Footer; with Elementor also Integration and Live_Search; in admin `Library::maybe_upgrade` on `admin_init`, Product_Meta_Box, Library_Ajax, Contact_Inbox. `Schema.php` (197): `TYPES`, `PAGE_TYPES`, `SINGLE_TYPES`, `BLOG_TYPES`, `BRAND_DEFAULTS` (13 colours), settings `enabled`, `course`/`product` (`{default, rules[], preview_id}`), `header{desktop,mobile,sticky_*}`, `footer`, `blog{archive,post,search}`, `breakpoint`, `options{…}`, `brand{…, radius}`.

### Templates, library, presets
- **Template_Post_Type** (191): `stx_template`, meta `_stx_type`, `_stx_preset`; public/queryable for Elementor preview, `guard_frontend` 404 + noindex for non-editors; hidden from menu; `edit.php` → library tab; `post.php?action=edit` → Elementor; removed from sitemaps; post states; `type_of()`, `is_usable()`.
- **Library** (449): option `*_builder_presets` (key → post ID), `*_builder_presets_version`, `_stx_preset_hash = md5(_elementor_data)`. `maybe_upgrade()` on an admin's page load when the version changed: install missing, overwrite unedited (hash matches). `install`, `install_missing`, `create_blank`, `duplicate`, `copy_content`, `restore`, `all`, `catalog_for_js`, `sync_kit_colors` (kit colours `stx*`), `activate_container`, `insert_post` (`elementor_canvas`; page designs get `Design_Pages::page_meta()`), `save_elements` (document API, raw-meta fallback).
- **Presets/El.php** (305): `id()`, `box(array $o, array $children=[])` (options `boxed` int|true, `width[_tablet|_mobile]`, `dir*`, `gap*`, `pad*`, `margin*`, `align*`, `justify*`, `wrap*`, `surface`, `sticky`, `sticky_offset`, `tag`, `min_h`, `grow`, `fill`, `bg`, `radius`, `border` [w,color], `hide` [devices], `set` raw), `w(string $type, array $settings=[], array $o=[])` (`hide`, `width=>'auto'`, `grow`, `fill`), `finalize()` (sets `isInner`), value helpers `px`, `size`, `dims`, `gap`, `start`.
  ```php
  Blocks::band( 'plain', array( Blocks::section_title( __( 'Our numbers', … ), '', '', __( '…' ) ), self::stats( 'tiles', 4 ) ), Blocks::white_band() );
  El::w( 'stx-faq', array( 'items' => array( array( '_id' => El::id(), 'question' => …, 'answer' => '<p>…</p>' ) ) ) );
  ```
- **Presets/Blocks.php** (374): `heading`, `section_title($title,$action,$url,$subtitle)`, `row_title`, `text($html,$tone,$more)`, `button($text,$url,$variant,$more)`, `buttons`, `pill`, `band($surface,$children,$o)`, `white_band`, `last_band`, `perk`, `link`, `arrow`, `blog_url` (+ WooCommerce/course helpers).
- **Presets/Catalog.php** (260): `all()` key → `{type,label,description,build=>[Class,'method']}`; `keys()`, `get()`, `build()` = `El::finalize(call_user_func(build))`. 36 presets.

### Choosing and rendering
- **Resolver** (338): `single_template()` (preview → enabled → per-product override → category rules with ancestors → default), `blog_template()`/`blog_kind()`, `slots('header'|'footer')` → `{desktop,mobile}` (ID / `theme` / `none`; `same` resolved), previews via GET `stx_preview*` + `_stxnonce` (needs `edit_pages`), `preview_url()`.
- **Context** (312, static): `product()`, `run()`, `post()`, `run_post()`, `sample_post_id()`, `is_editor()`, `is_course()`, `sample_id($type)`.
- **Header_Footer** (371, on `wp`): Studiare header captured with `get_template_part` + `ob_start` and ended at `load_studipage_title`; footer `ob_start` on `get_footer` and region replace on `wp_footer`; leaves `#footer.stx-hf__anchor`; non-Studiare themes use `wp_body_open`/`wp_footer` priority 5 + CSS hiding (`*_builder_theme_selectors`). Both device slots printed (`.stx-hf__slot--desktop/--mobile`) and switched by a media query at `breakpoint`; sticky via `data-stx-sticky-*`. **Lenz needs a different capture** (see `docs/reference/lenz-theme.md` §6).
- **Blog_Pages** (94) + `views/blog.php` (47): resolve on `wp`, enqueue through `Renderer::enqueue`, `template_include` 99 → `get_header()`, loop + `Renderer::render()` in `.stx-blog--post` / `.stx-blog--archive`, `get_footer()`.
- **Single_Product** (107) + `views/single-product.php` (60): same pattern with WooCommerce hooks.
- **Design_Pages** (165): `page_meta()` (`elementor_header_footer` + theme title flags), `create($design_id,$title,$status)` copies Elementor data + `_stx_design`, `set_front`, `front_id`, `all`.
- **Renderer** (82): `elementor_ready()`, `enqueue($id)` (frontend styles, post CSS, module handles), `render($id)` → `.stx-tpl.stx-tpl--<type>[data-stx-template]`.
- **Assets** (218): `stx-tokens` (inline `:root` brand vars + compacted tokens.css), `stx-builder` (+ `window.stxBuilder`), `stx-builder-home`, `stx-builder-blog`, `stx-builder-pages`, `stx-slider`; registered on `wp_enqueue_scripts`:1 and Elementor's `after_register_*`.

### Elementor
- **Integration** (237): category `studiare-plus` ("Studiare+", `eicon-apps`), `WIDGETS` list → `new Widgets\<Name>()` on `elementor/widgets/register`; container controls on `elementor/element/container/section_layout_additional_options/after_section_end`: `stx_surface` (`prefix_class 'stx-surface-'`), `stx_sticky`, `stx_sticky_offset`; preview styles; cart fragments; `wp_content_img_tag` → `Picture::clean_img_tag`.
- Helpers: `Parts.php` 948, `Cards.php` 565, `Post_Parts.php` 320, `Picture.php` 170, `Category_Parts.php` 81.
- **Widgets/Base.php** (537) extends `Widget_Base`: categories, keywords, style/script depends `[Assets::HANDLE]`, `has_widget_inner_wrapper()` false, `is_dynamic_content()` true; `product()`, `in_editor()`, `with_product()`, `editor_hint()`; `icon($key,$class)`, `icon_options()`, `digits()`, `digits_html()`, `num()`, `start_icon()`/`end_icon()`; controls `add_align_control`, `add_text_style($prefix,$selector,$args)`, `add_box_style`, `add_button_style`, `add_columns_control` (→ `--stx-cols`), `add_gap_control` (→ `--stx-gap`), `start_style_section`, `start_content_section`, `add_sample_note`; `CHEVRON`.
- Sub-bases: `Home_Base` (24), `Blog_Base` (59; `with_post()`, `add_post_note()`), `Page_Base` (30).
- **Example: Widgets/Faq.php (162)** — `final class Faq extends Home_Base`; `get_name()` `stx-faq`; `is_dynamic_content()` false; repeater `items` (`question` TEXT, `answer` WYSIWYG), switchers `open_first`, `one_open`, `schema`; style: gap, item box, question/answer text; render `<div class="stx-faq">` + `<details class="stx-faq__item" [name] [open]><summary class="stx-faq__q">…</summary><div class="stx-faq__a">…</div></details>`, answer through `wp_kses_post($this->parse_text_editor())`, FAQPage JSON-LD when `schema` and not editing. CSS in `home.css` "FAQ" section; no JS.

### tokens.css (50)
Brand vars printed as `:root` (`--stx-accent`, `-accent-strong`, `-accent-soft`, `-on-accent`, `-ink`, `-text`, `-muted`, `-bg`, `-card`, `-line`, `-dark`, `-success`, `-danger`, `--stx-radius`, `--stx-bp`) + derived (`--stx-radius-sm/-lg`, `--stx-shadow`, `--stx-shadow-pop`, `--stx-ease`, `--stx-control-h:46px`, `--stx-field-bg/-line`, `--stx-header-offset`, `--stx-admin-bar`, `--stx-accent-ink`). Dark mode via `:where(.scdarkcolors)` (not needed for Lenz).

### Studiare/WooCommerce-specific pieces (do not port as-is)
Course LMS (`Course_Curriculum`, `Course_Teacher`, `People`, lesson meta in `Parts`, `Presets/Course.php`, course card style), Studiare hooks (`Header_Footer` capture, `load_studipage_title`, `_studiare_*` meta, `Site_Logo` option, `Account` login modal, `Dark_Toggle`, category icon/colour meta, `Thumbs::draw_home_studiare`, `.variation-radios`, select2 handling). Generic and reusable: Heading, Text, Button, Icon_List, Nav_Menu, Search, Copyright, Slides, Slider, Promo_Card, Post_Grid, Features, Countdown, Testimonials, Faq, Stats, Media, Newsletter_Form, Logo_Strip, Pricing_Plans, the blog widgets, Contact_Form, Contact_Details, Map, Timeline, plus Newsletter, Contact_Messages/Inbox and Public_Form.

## 6. Tools and lint

- `tools/minify.mjs` (46): hardcoded asset ROOT and list; `npx esbuild@0.25 --target=esnext` → `*.min.*`.
- `tools/build-zip.sh` (20): reads Version, runs minify, zips to `dist/<slug>-<ver>.zip` (excludes `.DS_Store`, `node_modules`, `.git*`).
- `tools/build-icons.mjs` (108) + `tools/icon-map.mjs` (160): packs (lucide, tabler, phosphor, phosphor-duotone, heroicons, bootstrap) fetched from jsDelivr at build time → `assets/icons/catalog.json` + `packs/*.json`. The plugin never loads icons from a CDN.
- `phpcs.xml.dist`: WordPress-Extra minus `WordPress.Files.FileName`, text domain, prefixes, PHPCompatibilityWP `7.4-`, WP 6.0.
- `languages/`: about 2,000 entries in fa_IR.
