=== Lenz Plus ===
Tags: lenz, photography, bottom navigation, elementor, rtl
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.10.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Extra features for the Lenz photography theme: a customizable mobile bottom navigation, a floating support button, and Elementor page templates for the home, about, services, portfolio, courses and blog pages with their header and footer.

== Description ==

= Mobile bottom navigation =

* Five styles: Classic, Floating glass, Center button (notch), Bubble and Expanding pill, each with a live preview.
* Button types: home, custom link, portfolio or video archive, booking (Lenz's Reserve button), search sheet with live results, cart (live WooCommerce count), account, menu (Lenz's own mobile menu or a sheet), content sheet, back to top and "click an element".
* Lenz's icon font, Font Awesome from the theme and six bundled SVG icon packs (Lucide, Tabler, Phosphor, Phosphor Duotone, Heroicons, Bootstrap Icons), or a custom image or SVG per button.
* Colours and font follow the Lenz theme (including its dark demo palette); every colour and size can be overridden.
* Cache friendly (no server-side device detection), accessible (keyboard, screen readers, reduced motion) and RTL-first.

= Floating support button =

* One corner button for Telegram, WhatsApp, Bale, Eitaa, Instagram, phone, email and a support page. Persian digits and 09… mobiles work; the admin shows the exact link each channel opens and offers the number saved in Lenz's mobile menu.
* Works without JavaScript (a native disclosure), shows an optional greeting once per visit and rises above the bottom navigation and the course buy bar.

= Page templates (Elementor) =

* 26 ready-made designs installed as normal Elementor templates: header, three footers (one with trust badges), home, about, services, three contact pages, four project booking pages, portfolio list, three project pages (photo, video, photo and video), blog list, four article pages, courses page, and two course pages.
* Header and footer for desktop and for phones separately (a template, Lenz's own, or nothing), switched with CSS so page caching keeps working; sticky header modes; Lenz's mobile menu keeps working.
* Pages: create a real WordPress page from the home, about, services, contact, booking or courses design in one click; the home design can become the site's home page.
* Site pages: templates for the portfolio archive and categories, single projects (with a separate design for video projects and for photo and video projects), the blog (categories, tags, authors, dates, searches), single posts and course products, each filled from the site's content.
* Courses are WooCommerce products marked as courses: status (open, coming soon, archive), online or in-person, seats, curriculum, outcomes, instructor (Lenz's experts), intro video, FAQ, and registration through the checkout or a request form, with a waitlist for upcoming courses and a buy bar on phones.
* Project bookings: a booking form in four looks (numbered steps on a card, steps beside an ink panel with a live summary, one question at a time, or an ordinary one-page form) asks for the kind of project, its details and the contact details, shows a review before sending and gives a tracking code. Bookings are kept in Lenz+ → Bookings with a status (new, confirmed, done, cancelled), emailed to you, confirmed to the customer by email, and downloadable as CSV.
* Request forms, newsletter and waitlist sign-ups are kept in Lenz+ → Requests (new-count badge, CSV download, WordPress privacy tools) and can be emailed. They work without JavaScript and on cached pages (honeypot and rate limit).
* 63 Elementor widgets in a "Lenz+" category, built from the designs: page hero (with the film strip and showreel layout), service cards, key numbers, steps, cards, lists, timeline, framed band, quotes, testimonials, photo frames and rows, pricing plans, FAQ (with structured data), call to action, portfolio grid with category chips, featured projects, project header and gallery, post grid, featured post, article header, table of contents, reading progress, comments, popular posts, course grid, course hero, curriculum, buy box, request and sign-up forms, a project booking form in steps, contact cards, a map that loads on click, brand logos (grid, tiles or a scrolling strip), trust badges (eNamad, Samandehi, Zarinpal), author box, previous and next post, and the header and footer pieces.
* Brand colours in one place, black-and-white or colour photos, and the designs' guide lines on or off.

= Studio panel =

* A simple management panel inside WordPress, built for phones: articles, projects with their gallery, courses, products, orders, bookings, requests, comments and media, in the site's brand colours and font.
* Opened from WordPress itself: «Studio panel» is the first item of the wp-admin menu, with a button at the top of the dashboard and in the admin bar. Staff log in with their WordPress account; a «Studio manager» role manages content and the shop without the site's settings.
* A simple text editor for phones (headings, bold, lists, quotes, links, photos), or WordPress's classic editor, a plain box or WordPress's own editor, chosen per content type. Texts built with Elementor keep opening in Elementor.
* Moves between pages like an app (no reloads, smooth transitions) and can be added to the phone's home screen to open full screen.
* Photos upload in the background with a preview and can be reordered; prices and seat counts accept Persian digits. Optionally, staff are kept out of wp-admin and land in the panel after logging in.

== Installation ==

1. Upload the `lenz-plus` folder to `/wp-content/plugins/`, or upload the zip from Plugins → Add New → Upload Plugin.
2. Activate the plugin.
3. Open **Lenz+** in the admin menu. Page templates need Elementor (free) 3.16 or newer with Flexbox Containers; courses and the cart need WooCommerce.

== Frequently Asked Questions ==

= Does it need the Lenz theme? =

It is built for Lenz and reads its colours, logo, menus and options. Without Lenz the plugin keeps working with its own defaults; features that only make sense with the theme (such as the Reserve button and Lenz's mobile menu) are skipped.

= Will my edits to a template be overwritten by an update? =

No. Updates refresh only the ready-made templates you have not edited, and pages created from a design never change by themselves.

== Changelog ==

= 1.10.1 =
* Checklist grid: new looks (soft cards, the new default; cards with a coloured edge; rows with dividing lines; chips), an icon from Elementor's library as the mark, an own icon for each typed item and marker colours.
* Curriculum: new looks (soft rows, rows with dividing lines, timeline); any session can show its own icon while the others keep their numbers.
* Session icons per course: give any session its own icon in the course details box or the studio panel's course form; sessions without one keep the widget's number or icon.
* Logos are served at the size they are shown, so phones no longer download the full upload.

= 1.10.0 =
* New Speed section: Lenz's sliders, galleries, select boxes, date picker, widget scripts and Font Awesome load only on pages that use them (about 20 files less on a typical page), text shows while fonts load, the site font is preloaded, WordPress extras (emojis, block styles, dashicons, Heartbeat…) and WooCommerce's cart request for empty carts are trimmed. Works next to WP Rocket: its «Remove unused CSS» keeps the styles Lenz switches on with JavaScript, LazyLoad skips the first large photo, and the page cache is cleared after Lenz+ settings or header/footer templates change.
* Lighter on the server: the bottom bar's cart button asks WooCommerce for fresh cart data only for visitors who have a cart; sales reports, guest buyers, course students and the panel home's sales total are rebuilt at most once a minute on busy stores (instead of after every order) and keep one cached copy each; the home screen's sales total is no longer summed from up to 500 orders on every visit; project galleries load their photos in two database queries.
* Fixes: the phone buy bar no longer peeks out under the bottom bar while hidden (and is out of the keyboard order then); long amounts on the panel's home tiles wrap instead of ending in «…»; prices in live search results get a space before the currency; forms sent from browsers that hide the referring page return to the form's page instead of the home page.
* Builder: single service pages in three designs (photo hero with price cards, ink band with the booking form, minimal with a price list and the order form), under Pages → Single service pages. «Service pages» makes a page for each service on the services page (professional, religious, content creation, event coverage), each with its own texts, prices and questions, and points the services page's tiles at them once they are published.

= 1.9.1 =
* Lighter on the server: four database lookups less on every page and request (settings never saved and the panel's checks are now loaded with WordPress's options), booking counts without loading every booking, sales reports invalidated once per order instead of several times.
* Live search answers can be reused by the browser and a CDN for five minutes.
* Studio panel: notification checks slow down while nothing happens (up to every 10 minutes, back to every minute on new activity or when the page is opened again); pages are prefetched one at a time and never on data-saver connections.

= 1.9.0 =
* Studio panel: SEO card for Rank Math and Yoast SEO on articles, pages, projects, products and courses (focus keyword, SEO title, meta description, canonical address, noindex, score and a search result preview), written to the plugins' own fields.
* Studio panel: new sections: sales reports (ranges, comparison with the period before, sales bars, best products, categories, customers, discount codes, payment methods, regions, weekdays, stock alerts, CSV), customers (accounts and guest buyers, orders, what they bought, staff notes, CSV), discount codes, course students (buyers of each course, income, seats left, waiting list, CSV, email everyone), wallet (TeraWallet: balances, credit/debit, transactions, settings), activity log (who changed what, from the panel or wp-admin) and search across orders, customers and content.
* Studio panel: orders get refunds, a parcel tracking code (PWS's code is shown too), the invoice email, province and city names from PWS and a wider search (names, phones, addresses, items).
* Studio panel: notifications of new orders, bookings and requests on staff phones and the waiting count on the home-screen icon (switched on per device in My account); the panel is kept out of WP Rocket, LiteSpeed and other page caches, and administrators can clear the site's cache from it.
* Course grid: new «Posters with tabs» look for the home page (most popular / newest tabs, an «all courses» button, tall covers, sessions, students, price and a green pill).

= 1.8.0 =
* New course page design «Course with a side menu»: a summary card on top (cover, title, ticked facts, price and button), the sections beside a menu that stays in view and marks the section being read (a sticky chip bar under the header on tablets and phones), the summary card again at the end (#buy). Sections left empty for a course (no instructor, say) hide themselves, and so do their menu links.
* New widgets: «Course summary» and «Course menu» (links with icons from Elementor's library or uploaded SVGs, smooth scrolling below the sticky header, course facts under the side card).
* The buy bar now shows on every screen by default, with the course title beside the price on wide screens («Show on» keeps the phones-only options).
* Course details: a «Who it is not for» list; the checklist widget shows it (red crosses) and has a simple list look with round ticks.

= 1.7.1 =
* Course photos (hero, course cards, instructor) show in colour even when the site shows photos in black and white; each widget has a «Photo tone» choice.
* Course hero: the chips above the title are spaced out, and on phones the hero is centred with the facts as even tiles (switch «On phones» back to start-aligned if you prefer). A course without a cover keeps its 16:10 frame.
* Instructor widget: four looks (ink card, profile card with number tiles and an achievement list, a large cut-out photo on a coloured shape, a compact row), typed name/position/text/photo/achievements that fill whatever the Lenz expert leaves empty (so it also shows on courses without an expert), heading and accent colour.
* Curriculum: before each session title the session number, an icon from Elementor's library or an uploaded SVG (one for all, or one per session), or nothing.
* Phone buy bar: shown at the bottom of the screen from the top of the course page and hidden only while the buy box button or the enroll form is on screen; rounded sheet look. The form button opens the enroll form, the cart button goes to checkout as before.

= 1.7.0 =
* Studio panel: galleries, cover photos and product photos can take files already in the WordPress media library («From the library»: search, photos/videos filter, several at once).
* Project videos off the main host: a project gallery takes Aparat videos (page link, iframe code or script code) and direct video links on a download host, in the panel and in Lenz's gallery box on the portfolio editor (an embed code pasted there becomes the video's link). Project pages play Aparat videos in Aparat's player and other links as video files; the home and course hero play buttons accept Aparat links too.
* Project photos open in a full-screen preview: previous/next, swipe, arrow keys, counter and caption (switch «Open photos in a preview» on the Project photos widget).
* Ready-made course «از دیدن تا ثبت کردن» (mobile photography workshop): Page templates → Pages → Ready-made courses adds it as a course product with every detail, the curriculum, questions, outcome and a cover drawing; course pages switch to the Course design when they still followed the theme.

= 1.6.0 =
* Studio panel feels like an app: pages change in place without reloading, with a short slide (forward and back) or fade between tabs, a thin progress bar only when the server is slow, pages fetched as soon as a finger touches a link, the back button returning to the same scroll position, and saving without a page reload. Add it to the phone's home screen to open it full screen with its own icon.

= 1.5.0 =
* Studio panel: a simple, phone-first management panel inside WordPress for the studio's staff. Articles, projects (gallery, project details, featured), courses (course details, price, seats), products (price, sale, stock, gallery), orders (status, notes, call or email the customer), project bookings (status), requests, comments (approve, reply) and media (upload, copy link, delete), with a home screen of what is waiting and the last 30 days' sales. It opens from the first item of the wp-admin menu, a dashboard button, the admin bar and WooCommerce's account menu; people log in with their WordPress account. New «Studio manager» role. The text editor of each content type can be the panel's simple editor, WordPress's classic editor, a plain box or WordPress's own editor.

= 1.4.0 =
* Shop pages in the brand style: the cart, checkout, order received and account pages of WooCommerce (classic templates and the cart and checkout blocks) use the designs' colours, fields and buttons, built for phones first. A step bar leads from cart to payment to the finished order, the cart updates itself when a quantity changes, the coupon answers under its field, WooCommerce's messages get one clear look, and the account menu is a simple list (a scrolling row on phones). Prices read «۱۲۰٬۰۰۰ تومان» whatever the store's currency position.
* Order received page: a success hero with what happens next for course, download, paid, waiting and unpaid orders, the courses just enrolled in, and a «Download the files» button for files.
* New feature «Checkout form»: choose which billing fields the checkout asks for (required, optional or hidden), a short form for download, virtual and course orders, Persian digits turned into English ones in phones, postcodes and emails, and editable texts for the order received page. Works with Persian WooCommerce and Persian WooCommerce Shipping.
* Courses never ask for a shipping address.

= 1.3.1 =
* Fix: after an update, admin pages opening at the same time could each install the ready-made designs, leaving two or three copies of every new design (and copies cut short that opened neither in the preview nor in Elementor). Designs are now installed by one request at a time, an existing copy is reused, and this update moves the extra copies to the trash: those nobody edited and no page or setting uses, and any without Elementor data. A design left without data is rebuilt.

= 1.3.0 =
* Project booking: a new widget that takes a booking in steps (project type cards, project details, contact details, review) with a tracking code, in four looks, and four booking page designs. Without JavaScript the same form posts as one page.
* Lenz+ → Bookings: every booking with its code, project and answers; change the status (new, confirmed, done, cancelled) from the list, filter by status, reply by email, download a CSV. The studio gets an email and the customer a confirmation.
* Form fields gain number and choice-button types (shared by the request and booking forms); bookings are included in WordPress's personal data export and erasure.

= 1.2.1 =
* Key numbers: tiles animate on mouse hover (lift with a shadow by default; fill with the ink colour, border with a growing line, or none under Key numbers → On hover). Nothing moves for visitors who ask for reduced motion, and touch screens are left alone.

= 1.2.0 =
* Contact pages in three styles (classic, ink panel, minimal) with the new Contact cards and Map widgets; the map loads only when the visitor asks for it.
* Trust badges widget (eNamad, Samandehi, Zarinpal, any image or a badge's code) and a footer design with a row of trust badges.
* Post cards redesigned: white cards that lift on hover, the category on the photo, date and reading time with icons, a short excerpt, the author and "read more"; new looks with the text on the photo or the photo beside the text. Related articles use the same cards.
* Three more article designs (cover, image beside the title, minimal) with the new Author box and Previous and next post widgets, a header that can sit on or beside the featured image, and a folding table of contents.
* Brand logos widget (grid, tiles, scrolling strip, row), also on the home page design.
* Speed and search: pages built from a site-page template (blog, article, project, course) load their widget styles in the head, so they no longer paint unstyled and shift (layout shift on the blog page 0.21 → 0.01); breadcrumbs carry schema.org BreadcrumbList data (off when an SEO plugin prints its own); course pages keep WooCommerce's product structured data; the first row of post cards on a list page is not lazy-loaded; Lenz's default logo gets its size.

= 1.1.2 =
* Course prices on sale show the struck-through regular price and the discount percent next to the current price (buy box, mobile buy bar, course hero and course cards), with the currency after the amount.
* Course page: the request form section (#enroll) appears only for courses that register through the form; courses sold through the checkout show only the cart button.

= 1.1.1 =
* Course page: the introduction card's text fills the card's width, and the space between the course hero and the content is no longer doubled.

= 1.1.0 =
* Project pages for video projects (the film first, then the other videos and stills) and for photo and video projects (the photos, then the videos on the dark band), next to the photo project page.
* Project type in the Project details box (detected from the gallery by default); each type can use its own design under Page templates → Site pages.
* Project photos widget: show only photos or only videos, an optional title, and the cover as the film's poster. Sections with nothing to show are hidden.

= 1.0.0 =
* First release: mobile bottom navigation, floating support button, and Elementor page templates with their header and footer for the home, about, services, portfolio, blog and courses pages.
* Request forms, newsletter and course waitlist with an inbox, CSV export and privacy tools.
* Persian (fa_IR) translation included.
