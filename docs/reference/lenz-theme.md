# Lenz theme: integration map (v1.6.1.0)

Read-only notes on the commercial **Lenz** photography theme (rtl-theme.com, by MohammadAmin Noorani), taken from `Theme.zip → Theme package/1-Theme/lenz.zip`. The extracted copy lives in `reference/lenz/` (git-ignored; `inc/Libs`, `assets/libs`, `plugins/` and demo photos left out). Demo import files are in `reference/lenz-demo/`. Never copy or edit theme code; integrate through hooks, CSS variables, selectors and options only.

## 1. Encoding and licensing

- Almost everything is plain PHP.
- `Redux/RTL_License_154a727bbb6d8b10.php`: **ionCube** (`//ICB0 74:0 81:551a 82:a254`). Needs a stub on the local test site (never shipped).
- `Redux/Options.php` (Redux config) and `Redux/Save.php` (compiler that writes the dynamic CSS): RTL-Theme PHP packer (reversed base64 chunks → temp file → `require` → unlink). Runs without ionCube.
- TGM (`inc/TGM/tgm.php`) requires RTL-CareUnit (`plugins/rtlsm.zip`), Redux Framework, Elementor and Elementor Pro (bundled zip). WooCommerce, Newsletter and CF7 are optional.

## 2. Redux options

- **opt_name `lenz`** (inferred: `$GLOBALS['lenz']`, admin page `toplevel_page_lenz`). The wp_options row is `lenz`.
- `\Lenz\Utils\Options::get_options( array $defaults )` (`inc/Utils/utils-options.php`) returns `$GLOBALS['lenz']` with missing keys filled from `$defaults` (empty repeaters are not filled). The class exists only after `init` priority 0 → guard with `class_exists( '\Lenz\Utils\Options' )` and fall back to `get_option( 'lenz' )`.
- Switches are `"1"` or `""`; the theme normalises them with `\Lenz\Utils::to_bool()`.

| File | Keys (defaults) |
| --- | --- |
| Colors.php | `body_color` #fff, `text_main_color` #666, `primary_color_1` #000, `primary_color_2` #8b8b8b, `secondary_color_1/2/3` (#fff/#eee/#c1c1c1), `gray_color_1/2/3`, `text_color_1..4` (#666/#A3A3A3/#bbb/#fff), `input_bg`, `input_secondary_bg`, `btn_primary_{color,color_hover,bg,bg_hover,border}`, `btn_secondary_{…}`, `scrollbar_*`, `disabled_color`, `red_color_1`, `green_color_1` |
| Typography.php | `font_IRANYekanX`, `font_IRANYekanXFANum`, `font_Vazirmatn`, `font_VazirmatnFANum` (switches), `main-typography` (default `IRANYekanXFANum`), `h1-`…`h6-`, `header-menu-`, `forms-btn-`, `footer-*-typography` |
| Header.php | `show-header`, `sticky_header`, `header_bg`, `header_sticky_bg`, `header_border_color`; logo `show-logo`, `logo-type`, `logo-img`, `logo-img-size`, `logo-link`; menu `show-menu`; cart `show-header-cart`, `show-mini-cart`; account `show-header-account-btn`, `header-account-link`, `header-account-link-guest`, `header-account-link-newtab`; reserve button `show-header-reserve-btn`, `header-reserve-text`, `header-reserve-link`; mobile menu `mobile-menu-bg`, `mobile-menu-support-show`, `mobile-menu-support-top-text`, `-top-link`, `-bottom-text`, `-bottom-link` |
| Footer.php | `show-footer`, `footer_bg`, `footer-show-marquee`, `footer-marquee-brands`, `footer-show-menu-1/2`, `footer-menu-1/2-title`, `footer-show-about`, `footer-about`, `footer-addresses` (repeater: title, location, link, phone), `footer-copyright-text` |
| General.php | `header_custom_code`, `footer_custom_code`, `exclude_post_types`, `default_archive_sort` |
| Posts.php | `single-post-*`, `archive-post-*` (title, sort, breadcrumb, sidebar, thumbnail grayscale/filter) |
| Portfolios.php | `single-portfolio-*`, `archive-portfolio-*` (incl. `archive-portfolio-ppp`) |
| Videos.php | `single-video-*`, `archive-video-*` |

Not present anywhere: dark mode, mobile bottom bar, back-to-top, an Elementor header/footer selector. Sticky header is only `sticky_header`.

## 3. CSS variables

The compiler writes `wp-content/uploads/lenz.css` (handle `lenz-custom`, version option `lenz_custom_style_version`). Defaults live in `assets/css/style.min.css` `:root`:

```
--body (=var(--secondary-1))  --text-main  --primary-1  --primary-1-50  --primary-2
--secondary-1  --secondary-2  --secondary-2-50  --secondary-3  --gray-1  --gray-2  --gray-3
--text-1  --text-2  --text-2-50  --text-3  --text-4  --input-bg  --input-secondary-bg
--btn_primary_bg  --btn_primary_bg_hover  --btn_primary_color  --btn_primary_color_hover  --btn_primary_border
--btn_secondary_bg  --btn_secondary_bg_hover  --btn_secondary_color  --btn_secondary_color_hover  --btn_secondary_border
--red-1  --yellow-1  --green-1  --disabled  --main-font
```

Redux key → variable is one-to-one (`primary_color_1` → `--primary-1`, `btn_*` → `--btn_*`); verify against a live `uploads/lenz.css`.

| Variable | Meaning |
| --- | --- |
| `--body` | page background |
| `--primary-1` | main ink: headings, icons, buttons, footer background |
| `--primary-2` | muted grey: borders, menu text, scrollbar |
| `--secondary-1` | surface: header background, mobile menu |
| `--secondary-2` | hover surfaces, submenus |
| `--text-main` | body text |
| `--text-4` | text on dark surfaces |
| `--primary-1-50` | overlay |
| `--main-font` | active font family |

Also: `--header-border-color`, `--sticky-header-border-color` (from lenz.css), `--header-height` (set by JS on `body.sticky-header`).

## 4. Dark mode

None: no toggle, class, attribute, `prefers-color-scheme` or localStorage. The "dark demo" (`reference/lenz-demo/Demo 2 (Dark)/redux_options.json`) just sets different Redux values (`body_color` #232323, `primary_color_1` #E2E2E2, `secondary_color_1` #303030, `footer_bg` #000 …). Anything that uses only the variables follows either palette automatically.

## 5. Fonts

Bundled: IRANYekanX, **IRANYekanXFANum** (default), Vazirmatn, VazirmatnFANum (`assets/fonts/*`, CSS in `assets/css/fonts/*.min.css`). Each active font is enqueued as `lenz-font-{Name}`; `html{font-family:var(--main-font)}`. Plugin CSS uses `font-family: var(--main-font, inherit)`.

## 6. Header and footer

`header.php`:
```
<body class="… sticky-header|static-header"> wp_body_open()
<div id="container">
  <header id="header-container"><div id="header" class="page-width"><div id="header_inner">
    #header-logo · nav#header-menu-wrap.lenz-menu-wrap.show-only-desktop-1200
    #header-actions-wrap.show-only-desktop-1200 · button#header-mobile-menu-btn.hide-desktop-1200
```
`footer.php`: `footer#site-footer.site-footer` (marquee, `#main-footer.page-width`, `#bottom-footer`) → `wp_footer()` → `</div>` (closes `#container`) → `</body></html>`. No theme hooks besides `wp_body_open` / `wp_footer`.

- The theme registers no Elementor Pro locations. When a Pro header/footer template has conditions, Pro's `theme-builder/classes/theme-support.php` hooks `get_header` / `get_footer`, prints its own document head + `wp_body_open` + its location, and swallows the theme's `header.php` in a discarded buffer (then there is no `#container`, `#header-container`, `sticky-header` or `--header-height`).
- Demo header JSON (`lenz-elementor-header-light.json`): Pro sticky container (1440 boxed), `theme-site-logo`, `lenz_menu`, `lenz_mini_cart`, `lenz_account_button`, `lenz_button` "رزرو وقت", a hamburger opening a Pro `off-canvas`.

## 7. Mobile menu, mini cart, search, account

- **Mobile menu** — always printed by `lenz_mobile_menu_html()` on `wp_footer` priority 1: `<div id="overlay">` + `div#mobile-menu.closed.hide-desktop-1200` (`#mobile-menu-head` with `.mobile-menu-close`, `nav#mobile-menu-nav` from location `main-menu-mobile`, support block `#mobile-menu-footer`). Open (private closure in `assets/js/script.js`, handle `lenz`): add `body.mobile-menu-opened` (overflow hidden), fade in `#overlay`, remove `.closed` from `#mobile-menu`. Close handlers are bound to `#overlay` and `.mobile-menu-close`. A plugin can click `#header-mobile-menu-btn`, or replicate the three DOM operations. Theme bug: the support link's href uses `mobile-menu-support-top-text`.
- **Body state** — `desktop`/`tablet`/`mobile` classes + `data-device` (>1200, 769–1200, ≤768); `sticky-header-active` after 10px scroll; `rtl`.
- **Mini cart** — `.lenz-cart-wrap(.header-cart-wrap) > a.button.lenz-cart-btn`, `span.lenz-cart-count.circle`, `.lenz_mini_cart > .lenz-mini-cart-content`. Opens on **CSS hover only**. Fragments (`woocommerce/WC.php` → `lenz_wc_add_to_cart_fragments`) replace `.lenz-mini-cart-content` and `.lenz-cart-count`. Qty AJAX action `lenz_update_mini_cart`. Use our own count class and fragment.
- **Search** — no overlay/modal. `searchform.php`: `form.searchform` with `input.search-field`, `button.search-field-icon`, hidden `post_type`.
- **Account** — `.lenz-account-wrap > a.button.lenz-account-btn(.user-logged-in)` + hover `.account-menu` (location `account-menu`). Links: `header-account-link` / `header-account-link-guest`, else WooCommerce my-account.

## 8. Fixed layers

No back-to-top, cookie bar or floating button. Layers: `#overlay` z 100; fixed sticky `#header-container` z 99 (98 at rest); `#mobile-menu` z 10000; `.video-container.fullscreen` z 1000; demo Pro header z 97/99. A bottom bar should sit below 100 and hide on `body.mobile-menu-opened`; `#site-footer` has `margin-top:110px`.

## 9. Post types, taxonomies, meta

| Post type | Settings | Taxonomies |
| --- | --- | --- |
| `portfolio` | public, archive `portfolios`, rewrite `portfolio`; title, editor, thumbnail, comments, revisions | `portfolio-cat` (hierarchical), `portfolio-tag` |
| `video` | archive `videos`, rewrite `video` | `video-cat`, `video-tag` |
| `expert` | public=false, no archive; title, editor, thumbnail | — |

Meta (via `Utils::get_post_options`, key = `_` + option name):
- portfolio: `_gallery` (attachment IDs, images or videos), `_external_links` (`{type, url, position}`). No client/date/video fields.
- video: `_video_type` (`file`|`link`), `_link`, `_file`, `_date`, `_package`.
- expert: `_socials`, `_position`, `_careers`.
- post: `_views` (popularity sort).
- menu items: `_lenz_menu_item_icon`, `_lenz_menu_item_display` (`all`|`users`|`guests`), rendered through `nav_menu_item_title` (icons for free in menu-driven lists).
- No courses anywhere. WooCommerce extras: wishlist, colour-swatch attributes, booking product `lenz_booking_product_id`.

## 10. Elementor widgets (category `lenz`, "Lenz")

`lenz_heading`, `lenz_button`, `lenz_menu`, `lenz_filmstrip_text`, `lenz_group_images`, `lenz_image_cart`, `lenz_service_item`, `lenz_about_item`, `lenz_portfolios` (isotope tabs), `lenz_video_portfolios`, `lenz_stroke_text`, `lenz_cta`, `lenz_experts`, `lenz_plan_cart`, `lenz_cf` (CF7 + map), `lenz_video`, `lenz_posts_slider`, `lenz_posts_archive`, `lenz_products_slider`, `lenz_testimonial`, `lenz_marquee`, `lenz_mini_cart`, `lenz_account_button`, `lenz_reservation`. No per-widget asset dependencies (theme enqueues all). Shared helper `\Lenz\ElementorControls` exists only from `elementor/widgets/register` onward. The theme adds `IRANYekanXFANum` to Elementor's font list (group `lenz`).

## 11. Templates

- `page.php`: `get_header()` → `main#page-body(.page-width unless _fullwidth)` → `#page-main` → page head (breadcrumb + `header#page-header`) → `#primary.content-area.row` → `.entry-container.col-12`. `_is_blog` renders `archive.php`.
- Page meta (`inc/Metaboxes/Page/metabox-settings.php`): `_show_breadcrumb`, `_show_title`, `_show_sidebar`, `_sidebar`, `_fullwidth`, `_use_content_style`, `_is_blog`, … Values `"1"`/`"false"`, missing = true.
- `archive.php`, `archive-portfolio.php`, `archive-video.php`: `get_header()`, then `elementor_theme_do_location('archive')` or `div#page-body.page-width > main#page-main` + archive head + `#primary` / `#posts.list-posts`.
- `single.php`, `single-portfolio.php`, `single-video.php`: `get_header()` → `div#page-body.page-width > main#page-main` → `lenz_breadcrumb()`, `header#page-header`, `#primary > #post-content.row > .entry-container > #page-content > article`.
- Plugin templates: `template_include` → `get_header()` → `<main id="page-body">` → `Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $id )` → `get_footer()`. `#page-body` gets `padding-top:92px` (71px ≤480) under `body.sticky-header-active`.
- Helpers: `lenz_breadcrumb()`, `lenz_archive_title()`, `lenz_get_post_title()`, `lenz_post_thumbnail()`, `lenz_single_share()`, `\Lenz\Components\Button::view()`, `\Lenz\Components\Heading::view()`, `\Lenz\Utils::load_svg()`.

## 12. Scripts and icons

- `lenz_enqueue()` on `wp_enqueue_scripts` 10. Styles `lenz` (`style.min.css`), `lenz-icons`, `lenz-sidebar`, `lenz-custom`. Scripts `lenz` (`script.min.js`, jquery, `lenzVars` {ajaxUrl, isIranTimezone}), `lenz-utils`, `lenz-elementor`, `lenz-video-player`. Libraries `lenz-bootstrap` (grid), `lenz-isotope`, `lenz-slick`, `lenz-select2`, `lenz-lightgallery*`, `lenz-font-awesome` (FA5 free: `far`/`fas`/`fab`), `lenz-wc`.
- Enqueue plugin CSS at priority ≥ 11 with deps `['lenz','lenz-custom']` when the theme is active.
- Icon font `lenz-icon` (`assets/fonts/lenz-icon/iconly.*`, classes `lenz-icon-*`, 79 glyphs: house, cart, user, search-normal, search-2, menu, call, phone, whatsapp, telegram, instagram, bale-border, eitaa-border, message-square-chat, heart, close, top, quote, location, website, chevron-down, square-tick, play, play-circle …). Two-tone SVGs in `assets/images/icons/` (`lenz-svg-{name}`). `assets/icons.json` packs filterable through `lenz/icon-picker/packs`.
- Buttons: `.button` + `.button-primary` / `.button-secondary`, `.button-small`, `.button-no-text`.

## 13. Layout

- `.page-width{max-width:1440px;padding-inline:72px}`; ≤1024px `padding-inline:20px` and `html{font-size:14px}`. `.page-width-mobile` ≤767.
- `scroll-padding-top:120px`; `#header_inner` min-height 92px; `#page-header{margin-top:80px}`.
- Visibility helpers: `.hide-desktop-1200`, `.show-only-desktop-1200`, `.hide-desktop` (≥1025), `.show-only-desktop`, `.hide-mobile` (≤767).
- `theme.json`: only `appearanceTools: true`.
