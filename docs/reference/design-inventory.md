# Design inventory (Design/*.dc.html)

Nine Claude Design mockups for the photographer/videographer **Amirmahdi Asadi**: `Design/Amirmahdi Asadi - {Home,About,Services,Portfolio,Project,Courses,Course,Blog,Article}.dc.html`. Assets in `Design/assets` (logos, IRANYekanXFANum fonts, `iconly.woff2` = the theme's `lenz-icon` font). `Design/uploads/` holds reference material: the client's bio and workshop copy (`امیر مهدی اسدی.docx`), a full-page capture of the live site, an earlier Course layout with a sticky sidebar buy box (`pasted-1789370743354-0.png`) and an older Lenz zip (git-ignored).

The `design-to-elementor` skill describes how to turn a section into a widget and preset.

## 0. Design runtime (`Design/support.js`)

- `<x-dc>` wraps the page; `<helmet>` injects head content (fonts, Material Symbols link, reset).
- `style-hover="…"` / `style-focus="…"` become `:hover` / `:focus` rules.
- Page props (become global Builder settings):
  - `inkColor` (default `#022D4F`; options `#185E82`, `#0B3A5E`, `#000000`) → `--ink`.
  - `photoTone` (`سیاه‌وسفید` default / `رنگی`) → `--photo-filter: grayscale(1) | none` on every photo box.
  - `showGuides` → `--guides: block | none` (decorative dashed guide lines).
- Interactivity (`data-*`): category filter chips (`data-cat-btn` / `data-cat`, multi-tag on Courses, items replay `fadeUp .35s`); live count + empty state (`data-count` / `data-empty`, Persian digits); exclusive accordion (`data-faq-*`, Home, Services); independent accordion (`data-acc-*`, Course); forms (`data-form` = booking / waitlist / notify / enroll / news; fields hide and a success box shows); reading progress (`data-progress`); copy link (`data-copy`, label «کپی شد» for 1.8s); `data-play="hero"` (no handler: video modal implied).

## A. Tokens

### Colours

| Role | Hex |
| --- | --- |
| ink (headings, primary buttons, active chips, dark bands, footer) | `#022D4F` (via `var(--ink,#022D4F)`) |
| accent (primary hover, 3px bar before H2, icons, notch line, link hover) | `#185E82` |
| body text | `#55636F` |
| secondary text (eyebrows, chips, lists, article body) | `#3E5566` |
| muted (dates, captions, step numbers, «تومان», English sublabels) | `#8C9AA6` |
| on-dark text / muted / chip text | `#C4D3DF` / `#8FA9BE` / `#DCE6EE` |
| on-dark border / hover / surface / card border / badge | `#2A5878` / `#134669` / `#0B3A5E` / `#1A4A6E` / `#1C4F73` |
| dashed border (signature cards) | `#D3DCE3` |
| input / chip border | `#DCE3E9` |
| light border (solid cards, dividers; secondary-button hover bg) | `#E3EBF1` |
| UI border (icon buttons, timeline line) | `#E0E6EB` |
| secondary button border, frame brackets | `#B9C6D0` |
| chip / tile surface | `#E8EEF3` |
| alternate section surface | `#F5F8FA` |
| header border | `#EDF1F4` |
| guides | `#E3E9EE`, `#E5EAEF` |

rgba: header `rgba(255,255,255,.92)` + `backdrop-filter: blur(10px)`; play button `rgba(2,45,79,.85)`; watermark stroke `rgba(2,45,79,.1)` (light) / `rgba(255,255,255,.08)` (dark); image caption text-shadow `0 1px 6px rgba(0,0,0,.5)`. Greys like `#E6E6E6`/`#F2F2F2` are placeholder stripes only.

### Type (IRANYekanXFANum 400/500/700/900; Latin brand names switch to `system-ui`)

| Use | Size |
| --- | --- |
| watermark (outline, `-webkit-text-stroke:1px`, transparent) | `clamp(50px,10vw,145px)` / 900 (Home `clamp(60px,11vw,160px)`) |
| H1 inner / Home / Project / Course / Article | `clamp(26px,4vw,48px)` / `clamp(26px,4.2vw,52px)` / 46 / 44 / `clamp(25px,3.6vw,42px)`, lh 1.35–1.4 |
| statement / quote | `clamp(30px,6vw,74px)` / `clamp(24px,4.4vw,54px)` |
| section H2 / CTA-band H2 / article H2 | `clamp(18px,2.2vw,26px)` / `clamp(20px,2.8vw,34px)` / `clamp(19px,2.2vw,24px)`, 900 |
| stat number / price | `clamp(26px,3.4vw,40px)` / `clamp(22px,2.6vw,30px)` |
| card H3 | 16–18px |
| lead | `clamp(14px,1.6vw,18px)`, lh 2–2.1 |
| body | 15px lh 2.2 (About), 16px lh 2.3 (Article), cards 13–14px lh 2 |
| meta / English sublabels | 12px / 11–12px, letter-spacing .08–.14em |

Weights: 900 headings, numbers, prices; 700 buttons, chips, labels; 500 nav, sublabels; 400 body.

### Shape, spacing, motion

- Radii: 2 (accent bar), 8 (chips, badges), 9 (syllabus number), 10 (buttons, inputs, icon buttons), 12 (images, FAQ items), 14 (hero images, service cards), 16 (cards, CTA bands), 18 (booking frame), 50% (avatars, play, dots).
- **Image notch:** a white 16–18px × 22% block, `border-radius:12px 0 0 0`, at the bottom inline-end corner, with a 2px `#185E82` line above it.
- **No shadows.** Flat, border-driven.
- Container `max-width:1280px; padding-inline:clamp(16px,4vw,40px)`; narrow 1080 (article body), 820 (article header), 900 (FAQ). Measures 46–56ch (leads), 62ch (quotes), 80ch (course intro).
- Section padding-block: Home `clamp(36px,5vw,72px)`, inner `clamp(30px,4.5vw,64px)`, bands `clamp(24px,4vw,48–52px)`, dark Services block `clamp(40px,6vw,88px)`. Card padding 22–26px (blog cards 14px). Gaps 10/12/14 inside, grids `clamp(14px,2vw,22–24px)`, splits `clamp(24px,4vw,52–56px)`. Heading block margin-bottom 26–28px (10px + muted 14px subtitle).
- **No `@media` rules**: `repeat(auto-fit,minmax(N,1fr))` (N 130–300), CSS columns masonry (`column-width:230/250px`), `flex-wrap`, `clamp()`. Need real breakpoints: Home hero 3-column grid, Blog/Article `span 2`, header nav.
- Motion: `@keyframes fadeUp {from{opacity:0;transform:translateY(12px)}}` .35s; chevron `transform .2s`; smooth scroll. Hover: primary ink → `#185E82`; white/secondary bg → `#E3EBF1`; dark outline bg → `#134669`; dashed card → `border-color:#022D4F; border-style:solid`; square icon button fills ink; nav `#55636F` → ink; footer link `#C4D3DF` → `#fff`; input focus border → ink.
- Motifs: dashed guide lines (`--guides`), outline watermark words per page (AMIRMAHDI PHOTO, PHOTOGRAPHY, LIGHT & FRAME, RITUAL & FAITH, BEHIND THE CAMERA, SELECTED WORK, LEARN THE LIGHT, NOTES ON LIGHT, MOBILE PHOTOGRAPHY), ruler ticks + corner brackets on framed bands, 3×3 rule-of-thirds overlay, vertical captions (`writing-mode:vertical-rl`). Ratios 3/4, 4/3, 4/5, 16/10, 1/1, 16/9, 9/16, 16/11, 3/2.

### Icons

1. Material Symbols Rounded (Google Fonts, not allowed: map to bundled SVG packs): `north_west` (RTL forward arrow on every CTA), `calendar_month`, `photo_camera`, `trophy`, `groups`, `mosque`, `stadium`, `video_camera_back`, `school`, `schedule`, `smartphone`, `live_tv`, `rate_review`, `forum`, `workspace_premium`, `request_quote`, `how_to_reg`, `link`, `co_present`.
2. `lenz-icon` (theme font): e000 telegram, e001 quote, e002 location, e003 instagram, e005 phone, e00c whatsapp, e00d website, e00e call, e01d chevron-down, e024 square-tick, e02f play, e034 play-circle, e040 search-2.

## B. Shared chrome

**Header** (sticky, z 60, translucent white + blur, bottom border `#EDF1F4`, padding 14px): logo mark 36px + two-line wordmark («امیرمهدی اسدی» 19/900, "PHOTO & VIDEO" 10px tracked .18em); nav 14px (active 700 ink); ink CTA «رزرو وقت» + calendar icon. Home adds anchors and a 42px phone icon button; Course CTA is «ثبت‌نام دوره» → `#enroll`. No mobile menu designed.

**Footer** (ink; dashed `#2A5878` rule over centred copyright 12px `#8FA9BE`): brand box (white mark + wordmark), optional description, 3 socials (42px bordered squares), 0–2 link columns (6px `#8FA9BE` square bullets; contextual: services / quick links / portfolio categories / blog topics / courses), contact box (`#0B3A5E`: address, phone, website). Grid `auto-fit minmax(210–220px)`.

No newsletter in the footer, no mobile bottom bar, no floating support button. Article has a fixed 3px reading progress bar (z 80).

## C. Pages

### Home
1. Hero: 3 columns — 86px ink film strip (sprocket holes, vertical "PHOTOGRAPHY STUDIO", chamfered `clip-path`); text (eyebrow, H1 «قصه‌ات را با نور می‌نویسم», lead, primary «رزرو وقت» + secondary «نمونه‌کارها», social proof: 3 overlapping 44px avatars + «از سال ۱۳۹۹ تا امروز / کنار شما بوده‌ام»); 4:5 showreel with 74px round play button + notch. Guides + watermark.
2. Services teaser: heading + 4 dashed cards (icon 34px accent, H3, LTR English subtitle, divider, text, 40px arrow button).
3. About teaser: split; heading, 2 paragraphs, underlined «ادامه داستان»; 4 value items (52px dashed icon box + label, 2×2); 4:3 image with 3×3 guide overlay.
4. Stats: 4 tiles (`#E8EEF3`; icon, number, label).
5. Portfolio: heading + 7 filter chips; CSS-columns masonry (230px), 9 items (vertical caption, notch, video badge).
6. CTA band: ink, radius 16, H2 + sub + white button «همین الان مشاوره بگیر» + 16:9 image.
7. Framed statement band «بگو سیـب» + camera icon (dashed rules, ruler ticks, brackets).
8. Courses (`#F5F8FA`): 3 course cards (16:10 cover, meta chip, title, excerpt, dashed divider, price + CTA); third is dark «به‌زودی» with an inline waitlist form.
9. Blog: heading + «مشاهده همه»; 4 dashed post cards (16:11, chip + date, title, excerpt).
10. Testimonials (`#F5F8FA`): 3-card grid (quote icon, text, name + service, 92px 3:4 portrait column).
11. Booking: ink frame (radius 18) around a white form: name, subject select (7), mobile, city, date/time, details textarea, privacy note, «رزرو کن», success state.
12. FAQ: max 900, 5 items, exclusive accordion.
13. Footer.

### Services
1. Split hero (eyebrow, H1 «چهار سرویس، یک استاندارد ثابت», lead, «دیدن تعرفه‌ها» + «نمونه‌کارها», 4:3 image + notch, watermark LIGHT & FRAME).
2. Quick nav: 4 dashed anchor tiles (icon, label, arrow).
3. Service ۰۱ (#photo): heading row (accent bar + «۰۱» + H2), English subtitle, text, «مناسب برای» chips; 2 staggered 3:4 images.
4. Service ۰۲ (#religious), dark full-bleed (watermark RITUAL & FAITH): intro + chips + 2 CTAs (white, dark outline) + 4:3 image; 4 dark cards; 4-image 3:4 captioned gallery; 3-column dashed honours list.
5. Service ۰۳ (#content): reversed; 2 staggered 9:16 images.
6. Service ۰۴ (#event, `#F5F8FA`): text, chips, single 4:3 image.
7. Pricing (#packages): 4 cards, first featured (ink, «پرطرفدار» badge, white CTA): title, price + unit («تومان / هر آیتم», «/ ساعت», «از …», «توافقی»), divider, 4 features, CTA; dashed «خدمات قابل افزودن» box with 4 label/price rows.
8. Process «مسیر هر پروژه» (`#F5F8FA`): 4 step cards (۰۱–۰۴).
9. FAQ (4, exclusive).
10. CTA band (2 buttons + image).

### About
1. Split hero (H1 «مسیری که با یک دوربین کنار پدرم شروع شد», 3 dashed fact chips, 2 buttons, 4:5 portrait, watermark BEHIND THE CAMERA).
2. Stats (as Home).
3. Story (4 paragraphs) + vertical timeline «مسیر کاری» (1px line, 9px dots, 5 entries: year/tag, H3, text).
4. Framed quote band (eyebrow «زندگی از نگاه من», big blockquote).
5. Process «طرز کار من» (as Services).
6. Two icon-tile lists: equipment (6 dashed tiles: icon, title, sublabel) and certificates (5).
7. Awards (`#F5F8FA`): 4 cards (trophy, level tag, H3, festival) + 5 labelled rows (84px label column).
8. Collaborations: 4 organisation cards (name + role); group exhibitions: numbered 2-column list (۰۱–۰۶).
9. CTA band «بیا درباره پروژه‌ات حرف بزنیم».

### Portfolio archive
1. Text hero (H1 «کارهایی که تا امروز ثبت کرده‌ام», watermark SELECTED WORK).
2. Filter bar: 7 chips + live count «۱۲ پروژه».
3. Masonry (250px): 12 items, empty state «در این دسته فعلاً نمونه‌کاری ثبت نشده است».
4. Featured projects (`#F5F8FA`): 3 zigzag cards (cover, chip + year, title, text, 3 facts, «مشاهده پروژه»).
5. CTA band «پروژه بعدی می‌تواند مال تو باشد».

Dynamic: `portfolio` CPT, `portfolio-cat` chips, count, featured flag, project meta. No pagination designed.

### Project single
1. Breadcrumb.
2. Header: 2 category chips + year, H1, lead (52ch), 4 dashed meta boxes (کارفرما، مدت پروژه، خروجی، لوکیشن).
3. 16:9 hero image (radius 16).
4. Two columns: «صورت مسئله» (paragraphs) + «آنچه انجام شد» (6-item square-tick checklist).
5. Gallery 4 × 4:5; two 3:2 images with captions. No lightbox.
6. Client testimonial (single large quote on `#F5F8FA`).
7. Related projects (3 cards: 4:3, title, «category · year») + «همه نمونه‌کارها».
8. Contextual CTA band («درخواست برآورد» + `request_quote`).

### Courses archive
1. Text hero «همه دوره‌ها و کارگاه‌ها، یک‌جا», watermark LEARN THE LIGHT.
2. Chips همه / در حال ثبت‌نام / آنلاین / حضوری / به‌زودی / آرشیو + count «۶ دوره» (multi-tag: status × format).
3. Grid (270px), three card variants: **open** (cover, meta chip duration · format · city, status chip «در حال ثبت‌نام» or «۴ ظرفیت باقی‌مانده», title, excerpt, price, CTA); **soon** (dark, waitlist form); **archive** (dashed, «دوره ۱۴۰۴», «برگزار شد», outline «خبرم کن»). Empty state.
4. «کلاس‌ها چطور برگزار می‌شوند» (`#F5F8FA`): 4 icon cards.
5. Notify CTA band (#waitlist): input + topic select (4) + «خبرم کن», success state.

### Course single
1. Hero (watermark MOBILE PHOTOGRAPHY): breadcrumb; chips (type in ink «ورکشاپ عملی», topic · level, price outline chip), H1 «از دیدن تا ثبت کردن», lead, 5-item meta grid (dashed icon box + label + value: duration, type, tool, date, capacity); 16:10 media with play button.
2. Content (single column): intro box on `#F5F8FA`; «چه چیزهایی یاد می‌گیری؟» 9 tick tiles; «سرفصل‌ها» numbered multi-open accordion (7: number badge, title, description); «برای چه کسانی است؟» 5 tick tiles + info note; outcome box + 4 fact tiles; ink instructor box (150px photo, label, name, bio, button); FAQ (6, multi-open).
3. Summary / buy box: «مشخصات دوره», title, large price, «ثبت‌نام در ورکشاپ» (#enroll), phone note, 9 spec rows (2 columns; date/time, remaining capacity «۵ از ۱۲ نفر»).
4. Enroll form (#enroll): name, mobile, level select, phone model (optional), capacity note, submit, success state.

The earlier layout (`uploads/pasted-1789370743354-0.png`) puts the buy box in a sticky sidebar (cover, price, spec rows, CTA, refund note, dashed phone box).

### Blog archive
1. Text hero «یادداشت‌هایی از پشت دوربین», watermark NOTES ON LIGHT.
2. Featured post: horizontal bordered card (image | content: «مقاله شاخص» badge, date · reading time, H2, excerpt, «خواندن مقاله →»).
3. Chips (topics) + count «۹ مقاله».
4. Grid spanning 2 tracks (240px), 9 borderless cards (4:3, chip, date · N دقیقه, title, excerpt) + sticky aside (top 90): search box, «پرخواننده‌ترین‌ها» numbered list (۰۱–۰۴), courses promo box.
5. Newsletter band (dark): «هر ماه یک یادداشت، بدون تبلیغ», email, «عضویت در خبرنامه», success state.

No pagination designed.

### Article single
0. Fixed 3px reading progress bar.
1. Breadcrumb.
2. Header (max 820): chip, date · reading time, H1, lead; author row (44px avatar, name, role); share (instagram, whatsapp, «کپی لینک»).
3. 16:9 featured image (max 1080).
4. Body (16px lh 2.3 `#3E5566`; H2; blockquote with 3px ink inline-start border on `#F5F8FA`; numbered list ۰۱–۰۵ in muted 900; 3-image 3:4 row + caption; inline dashed «مرتبط با این نوشته» course promo) + sticky aside (TOC «در این مقاله», booking promo).
5. Related posts (`#F5F8FA`): 3 bordered cards (4:3, «category · N دقیقه», title) + «همه مقالات».
6. Newsletter band.

No comments, tags or prev/next designed.

## D. Widget candidates (de-duplicated)

| # | Widget | Used in | Data |
| --- | --- | --- | --- |
| 1 | Site header (logo + wordmark, nav, CTA, phone button, sticky blur, mobile drawer) | all | menu |
| 2 | Site footer parts (brand box, socials, link columns, contact box, copyright) | all | menus/terms |
| 3 | Page hero: filmstrip (Home) / split (Services, About; fact chips) / text-only (archives); eyebrow, H1, lead, 0–2 buttons, watermark, guides, notch | all | static |
| 4 | Section heading (accent bar, optional index, H2, English sub, muted sub, end-side action; light/dark) | all | static |
| 5 | Service cards | Home | static |
| 6 | Anchor nav tiles | Services | static |
| 7 | Service detail split (number, chips, CTAs; media single / staggered / 9:16; reverse; white / soft / dark with cards, captioned gallery, honours) | Services | static |
| 8 | Icon features (inline / tile / card) | Home, About, Courses, Course | static |
| 9 | Stats tiles | Home, About | static |
| 10 | Portfolio masonry + filter + count + empty | Home, Portfolio | portfolio CPT |
| 11 | Featured projects (zigzag) | Portfolio | portfolio CPT |
| 12 | CTA band (ink; image or form) | most pages | static (+form) |
| 13 | Framed statement / quote band | Home, About | static |
| 14 | Course cards grid (open / soon / archive; filter, count, empty) | Home, Courses | WooCommerce products |
| 15 | Post cards grid (dashed 16:11 / borderless 4:3 / compact; filter, count, reading time) | Home, Blog, Article | posts |
| 16 | Featured post | Blog | posts |
| 17 | Sidebar parts (search, most-read, promo box, TOC; sticky) | Blog, Article | mixed |
| 18 | Testimonials (grid with portrait / single large quote) | Home, Project | static / meta |
| 19 | Forms (booking, enroll, waitlist, notify, newsletter; light/dark) | Home, Course, Courses, Blog, Article | handler |
| 20 | FAQ (exclusive / multi-open) | Home, Services, Course | static / meta |
| 21 | Curriculum accordion | Course | meta |
| 22 | Process steps | Services, About | static |
| 23 | Pricing cards + add-ons | Services | static |
| 24 | Vertical timeline | About | static |
| 25 | Awards (cards / labelled rows / dashed list) | About, Services | static |
| 26 | Simple lists (organisation cards, numbered 2-column) | About | static |
| 27 | Checklist grid (tick tiles / plain list + note) | Course, Project | meta / static |
| 28 | Key/value meta boxes | Project, Course | meta |
| 29 | Course buy / summary box (+ sticky sidebar variant) | Course | meta |
| 30 | Instructor box | Course | expert CPT |
| 31 | Breadcrumb | Project, Course, Article | dynamic |
| 32 | Image gallery grid | Project, Services, Article | meta / static |
| 33 | Related items | Project, Article | dynamic |
| 34 | Post header + content (chip, date, reading time, author, share, copy link, progress, typography, inline promo) | Article | post |

Theme widgets that overlap (do not reuse, but useful references): `lenz_filmstrip_text`, `lenz_stroke_text`, `lenz_heading`, `lenz_cta`, `lenz_service_item`, `lenz_plan_cart`, `lenz_testimonial`, `lenz_portfolios`, `lenz_posts_archive`, `lenz_reservation`, `lenz_cf`, `lenz_experts`.

## E. Open points the designs leave to us

Mobile menu, video modal for `data-play`, lightbox, pagination / load more, search behaviour, auto-generated TOC, real social URLs, the Home nav duplicate («مقالات» + «بلاگ»), the Course page's «دوره‌ها» link pointing to `Home#courses`, comments on Article. Default answers: Studiare's drawer menu, native `<dialog>` video modal, no lightbox in v1, WordPress pagination (`paginate_links`), live search via `Core\Search_Query`, TOC from H2/H3 (`Post_Parts`), socials from Support_Button channels, comments through the theme's `comments.php` (off by default).
