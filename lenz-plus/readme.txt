=== Lenz Plus ===
Tags: lenz, photography, bottom navigation, elementor, rtl
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
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

* 15 ready-made designs installed as normal Elementor templates: header, two footers, home, about, services, portfolio list, three project pages (photo, video, photo and video), blog list, article, courses page, and two course pages.
* Header and footer for desktop and for phones separately (a template, Lenz's own, or nothing), switched with CSS so page caching keeps working; sticky header modes; Lenz's mobile menu keeps working.
* Pages: create a real WordPress page from the home, about, services or courses design in one click; the home design can become the site's home page.
* Site pages: templates for the portfolio archive and categories, single projects (with a separate design for video projects and for photo and video projects), the blog (categories, tags, authors, dates, searches), single posts and course products, each filled from the site's content.
* Courses are WooCommerce products marked as courses: status (open, coming soon, archive), online or in-person, seats, curriculum, outcomes, instructor (Lenz's experts), intro video, FAQ, and registration through the checkout or a request form, with a waitlist for upcoming courses and a buy bar on phones.
* Request forms, newsletter and waitlist sign-ups are kept in Lenz+ → Requests (new-count badge, CSV download, WordPress privacy tools) and can be emailed. They work without JavaScript and on cached pages (honeypot and rate limit).
* 56 Elementor widgets in a "Lenz+" category, built from the designs: page hero (with the film strip and showreel layout), service cards, key numbers, steps, cards, lists, timeline, framed band, quotes, testimonials, photo frames and rows, pricing plans, FAQ (with structured data), call to action, portfolio grid with category chips, featured projects, project header and gallery, post grid, featured post, article header, table of contents, reading progress, comments, popular posts, course grid, course hero, curriculum, buy box, request and sign-up forms, and the header and footer pieces.
* Brand colours in one place, black-and-white or colour photos, and the designs' guide lines on or off.

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

= 1.1.0 =
* Project pages for video projects (the film first, then the other videos and stills) and for photo and video projects (the photos, then the videos on the dark band), next to the photo project page.
* Project type in the Project details box (detected from the gallery by default); each type can use its own design under Page templates → Site pages.
* Project photos widget: show only photos or only videos, an optional title, and the cover as the film's poster. Sections with nothing to show are hidden.

= 1.0.0 =
* First release: mobile bottom navigation, floating support button, and Elementor page templates with their header and footer for the home, about, services, portfolio, blog and courses pages.
* Request forms, newsletter and course waitlist with an inbox, CSV export and privacy tools.
* Persian (fa_IR) translation included.
