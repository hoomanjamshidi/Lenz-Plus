# Graph Report - Lenz-Plus  (2026-10-05)

## Corpus Check
- 475 files · ~369,737 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 3210 nodes · 5304 edges · 327 communities (157 shown, 170 thin omitted)
- Extraction: 79% EXTRACTED · 21% INFERRED · 0% AMBIGUOUS · INFERRED: 1125 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `dba2446f`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Utils
- bottom-nav-admin.js
- self
- Slider
- PublicScripts
- MJ\WPORM\Blueprint
- Blocks
- AJAX
- ElementorControls
- Cards
- builder.js
- El
- TGM_Plugin_Activation
- Archive
- ReservationSettings
- bottom-nav.js
- Library
- Post_Categories
- Item_Resolver
- Header_Footer
- Sanitizer
- builder-admin.js
- studiare-extensions/includes/Core/Icon_Library.php
- Page_Base
- Module
- Resolver
- Parts
- Footer
- Channels
- WC
- Product_Tabs
- Options
- WC.php
- Module
- Renderer
- Template_Post_Type
- support-button-admin.js
- TGMPA_List_Table
- Base
- Module
- Contact_Messages
- Map
- Nav_Menu
- About
- Post_Grid
- Product_Grid
- Persian
- init.php
- LenzSaveBookingProcess
- Otp_Digits
- Module
- Menu
- ContactForm
- Portfolios
- Module
- Styles
- Post_Parts
- Blog
- CTA
- PlanCart
- ProductsSlider
- Testimonial
- Video
- ArchiveFilter
- blog.js
- Sanitizer
- Contact_Details
- ImageCart
- lenz-plus/assets/admin/js/admin.js
- Theme_Bridge
- Frontend
- Post_Nav
- Newsletter
- Contact
- Search_Query
- video-player.js
- AboutItem
- Button
- FilmstripText
- GroupImages
- Marquee
- PostSlider
- Contact_Inbox
- Reservation
- ServiceItem
- VideoPortfolios
- Scripts
- Products
- home.js
- Icon_Library
- Fields
- Icon_Library
- Live_Search
- Category_Grid
- Frontend
- Countdown
- Heading
- StrokeText
- Assets
- Design_Pages
- Category_Parts
- Cart
- Module
- Elementor\Widget_Base
- functions/comments.php
- ElementorControls.php
- studiare-extensions/includes/Core/Site.php
- Schema
- Integration
- Post_Content
- Add_To_Cart
- Offer_Price
- Post_Meta
- Pricing_Plans
- Product_Spotlight
- post.php
- Persian
- Button
- Faq
- Logo_Strip
- Newsletter_Form
- People
- Product_Badges
- Search
- Site_Logo
- Slides
- Trust_Badges
- Single_Product
- Public_Form
- ReservePackagesMeta
- class-tgm-plugin-activation.php
- Blog_Pages
- Account
- Copyright
- Dark_Toggle
- Home_Base
- Features
- Fields
- Icon_List
- Media
- Mobile_Buy_Bar
- Post_Tags
- Product_Gallery
- Promo_Card
- Related_Products
- Stats
- Testimonials
- Text
- Social
- Gallery
- Wishlist
- otp-digits.js
- Search_Query
- Archive_Title
- Course_Teacher
- Post_Author
- Post_Comments
- Post_Share
- Post_Title
- Post_Parts.php
- Product_Attributes
- Product_Breadcrumb
- Product_Content
- Product_Excerpt
- Product_Highlights
- Product_Price
- Product_Rating
- Product_Reviews
- Product_Stock
- Product_Title
- Reading_Progress
- functions/archive.php
- .is_rtl
- Career
- ReserveWeekDays.php
- Video
- support-button.js
- Product_Info
- booking.js
- reservation.js
- elementor.php
- Info
- Expert
- Portfolio
- pages.js
- slider.js
- Product
- Autoloader
- Elementor
- Contact_Form
- reservation-settings-locations.js
- reservation-settings-suggest_locations.js
- my-account.js
- Messages
- Expert
- Portfolio
- Video
- shortcode-booking.php
- Category
- Tag
- Category
- Tag
- V1_5_0_0
- Wishlist.php
- Schema
- ReserveSubjects.php
- ReserveDayTimes
- Blog_Breadcrumb
- Fix
- Blog_Base
- Asset
- IconPicker
- Course
- Autoloader
- Site
- Color

## God Nodes (most connected - your core abstractions)
1. `Utils` - 307 edges
2. `El` - 121 edges
3. `Base` - 100 edges
4. `ElementorControls` - 96 edges
5. `Blocks` - 65 edges
6. `TGM_Plugin_Activation` - 60 edges
7. `Thumbs` - 58 edges
8. `Parts` - 46 edges
9. `Context` - 43 edges
10. `Module` - 39 edges

## Surprising Connections (you probably didn't know these)
- `lenz_modify_post_per_page()` --calls--> `Options`  [INFERRED]
  reference/lenz/functions/archive.php → reference/lenz/inc/Utils/utils-options.php
- `lenz_init()` --calls--> `Options`  [INFERRED]
  reference/lenz/functions/init.php → reference/lenz/inc/Utils/utils-options.php
- `lenz_sort_posts()` --calls--> `Utils`  [EXTRACTED]
  reference/lenz/functions/archive.php → reference/lenz/inc/Utils.php
- `lenz_comment_fields()` --calls--> `Utils`  [EXTRACTED]
  reference/lenz/functions/comments.php → reference/lenz/inc/Utils.php
- `lenz_save_comment_stars()` --calls--> `Utils`  [EXTRACTED]
  reference/lenz/functions/comments.php → reference/lenz/inc/Utils.php

## Import Cycles
- None detected.

## Communities (327 total, 170 thin omitted)

### Community 0 - "Utils"
Cohesion: 0.02
Nodes (5): Heading, DashboardFont, Wishlist, Update, Utils

### Community 1 - "bottom-nav-admin.js"
Cohesion: 0.07
Nodes (50): bindControls(), confirmDialog(), createStore(), escapeHtml(), getPath(), initColorFields(), initModuleSwitches(), paintRange() (+42 more)

### Community 3 - "Slider"
Cohesion: 0.06
Nodes (4): Elementor\Utils, Picture, Post_Image, Slider

### Community 5 - "PublicScripts"
Cohesion: 0.06
Nodes (6): AdminScripts, MenuItems, File, PublicScripts, Plans, AdminUI

### Community 6 - "MJ\WPORM\Blueprint"
Cohesion: 0.17
Nodes (7): MJ\WPORM\Blueprint, MJ\WPORM\Model, Booking, BookingMeta, ReservePackages, ReservePlans, ReserveSuggestLocations

### Community 8 - "AJAX"
Cohesion: 0.10
Nodes (4): AJAX, GetAvailableTimes, MiniCartSetQTY, Notices

### Community 10 - "Cards"
Cohesion: 0.18
Nodes (3): Cards, WC_Product, WP_Post

### Community 11 - "builder.js"
Cohesion: 0.12
Nodes (32): bindVariationImages(), boot(), buildChoices(), closeAll(), embedUrl(), escape(), fetchResults(), formOf() (+24 more)

### Community 14 - "Archive"
Cohesion: 0.09
Nodes (3): PostsArchive, Archive, lenz_wishlist_item_endpoint_content()

### Community 15 - "ReservationSettings"
Cohesion: 0.07
Nodes (6): Locations, Main, Subjects, SuggestLocations, Times, ReservationSettings

### Community 16 - "bottom-nav.js"
Cohesion: 0.12
Nodes (21): bind(), bindSheet(), cart(), close(), dark(), enableDragToClose(), focusables(), init() (+13 more)

### Community 17 - "Library"
Cohesion: 0.09
Nodes (3): Library, Module, Catalog

### Community 21 - "Sanitizer"
Cohesion: 0.09
Nodes (3): Sanitizer, Library_Ajax, Module

### Community 22 - "builder-admin.js"
Cohesion: 0.14
Nodes (21): applyResult(), cardHtml(), closeTermPicker(), createPageDialog(), designCard(), keywordOption(), newTemplateDialog(), openTermPicker() (+13 more)

### Community 27 - "Parts"
Cohesion: 0.09
Nodes (5): Context, WC_Product, WP_Post, Parts, WC_Product

### Community 30 - "WC"
Cohesion: 0.11
Nodes (6): WC, WCAttributeFields, lenz_wc_add_to_cart_fragments(), lenz_wc_order_get_formatted_billing_address(), lenz_wc_order_get_formatted_shipping_address(), lenz_woocommerce_widget_shopping_cart_subtotal()

### Community 31 - "Product_Tabs"
Cohesion: 0.11
Nodes (3): Course_Curriculum, WC_Product, Product_Tabs

### Community 32 - "Options"
Cohesion: 0.10
Nodes (8): Booking, Options, lenz_wc_cart_empty(), lenz_wc_pay_order_text(), lenz_wc_product_header_actions(), lenz_wc_return_to_shop_text(), lenz_wc_single_add_to_cart_text(), lenz_woocommerce_widget_shopping_cart_proceed_to_checkout()

### Community 33 - "WC.php"
Cohesion: 0.10
Nodes (5): lenz_requests_item_endpoint_content(), lenz_wc_body_classes(), lenz_wc_my_account_before_nav(), lenz_wc_order_customer_address_icon(), lenz_woocommerce_account_menu_items()

### Community 34 - "Module"
Cohesion: 0.05
Nodes (6): Admin, Ajax_Controller, Arr, Module, Module, Plugin

### Community 36 - "Template_Post_Type"
Cohesion: 0.10
Nodes (4): Module, Product_Meta_Box, Module, Template_Post_Type

### Community 37 - "support-button-admin.js"
Cohesion: 0.21
Nodes (19): channelGlyph(), channelUrl(), handleUrl(), initChannels(), initPreview(), international(), move(), queueRender() (+11 more)

### Community 40 - "Module"
Cohesion: 0.06
Nodes (5): Admin, Ajax_Controller, Arr, Module, Plugin

### Community 50 - "init.php"
Cohesion: 0.12
Nodes (3): lenz_include_shortcodes(), lenz_init(), lenz_register_widgets()

### Community 53 - "Module"
Cohesion: 0.15
Nodes (3): Fixes, Module, Schema

### Community 64 - "Testimonial"
Cohesion: 0.11
Nodes (3): Button, Testimonial, Sanitizers

### Community 66 - "ArchiveFilter"
Cohesion: 0.15
Nodes (3): ArchiveFilter, PostToc, WP_Widget

### Community 67 - "blog.js"
Cohesion: 0.29
Nodes (13): announce(), copyText(), hookElementor(), initCategoryMenu(), initCategoryRow(), initProgress(), initScope(), initShare() (+5 more)

### Community 72 - "lenz-plus/assets/admin/js/admin.js"
Cohesion: 0.14
Nodes (13): bindControls(), confirmDialog(), createStore(), escapeHtml(), getPath(), initColorFields(), initModuleSwitches(), paintRange() (+5 more)

### Community 78 - "Search_Query"
Cohesion: 0.07
Nodes (5): WP_Post, Search_Query, Live_Search, WP_Post, Search_Results

### Community 79 - "video-player.js"
Cohesion: 0.27
Nodes (9): fadeInVideoData(), fadeOutVideoData(), pauseOtherVideos(), playActions(), resetFadeOutTimer(), setResponsiveClass(), setVolumeIcon(), togglePlayPause() (+1 more)

### Community 90 - "Scripts"
Cohesion: 0.10
Nodes (3): Settings, Scripts, Page

### Community 92 - "home.js"
Cohesion: 0.32
Nodes (11): formatCount(), hookElementor(), initCount(), initCountdown(), initFilter(), initNewsletter(), initRail(), initScope() (+3 more)

### Community 96 - "Live_Search"
Cohesion: 0.18
Nodes (3): Live_Search, WP_Post, Search_Scope

### Community 105 - "Category_Parts"
Cohesion: 0.25
Nodes (3): Category_Parts, WP_Term, WP_Post

### Community 109 - "functions/comments.php"
Cohesion: 0.20
Nodes (4): lenz_comment_fields(), lenz_comment_star_column(), lenz_comment_stars(), lenz_save_comment_stars()

### Community 121 - "post.php"
Cohesion: 0.25
Nodes (4): lenz_add_post_views(), lenz_get_post_views(), lenz_post_thumbnail(), lenz_woocommerce_template_loop_product_thumbnail()

### Community 136 - "class-tgm-plugin-activation.php"
Cohesion: 0.15
Nodes (6): load_tgm_plugin_activation(), TGM_Bulk_Installer, TGM_Bulk_Installer_Skin, tgmpa(), TGMPA_Utils, lenz_register_required_plugins()

### Community 157 - "otp-digits.js"
Cohesion: 0.52
Nodes (6): digits(), finish(), finishForm(), normalize(), otpField(), write()

### Community 177 - "functions/archive.php"
Cohesion: 0.33
Nodes (3): lenz_breadcrumb(), lenz_modify_post_per_page(), lenz_sort_posts()

### Community 178 - ".is_rtl"
Cohesion: 0.22
Nodes (3): lenz_admin_enqueue(), lenz_wc_product_footer(), lenz_woocommerce_pagination_icons()

### Community 182 - "support-button.js"
Cohesion: 0.60
Nodes (5): closeMenu(), greetingDismissed(), hideGreeting(), initGreeting(), initMenu()

### Community 184 - "booking.js"
Cohesion: 0.60
Nodes (3): createTimeSlotElements(), getTimeRanges(), setDayTimes()

### Community 185 - "reservation.js"
Cohesion: 0.70
Nodes (4): createTimeSlotElements(), getTimeRanges(), initCalendar(), setDayTimes()

### Community 190 - "pages.js"
Cohesion: 0.70
Nodes (4): hookElementor(), initContact(), initMap(), initScope()

### Community 191 - "slider.js"
Cohesion: 0.70
Nodes (4): afterLoad(), hookElementor(), init(), initScope()

## Knowledge Gaps
- **2 isolated node(s):** `TGM_Bulk_Installer`, `TGM_Bulk_Installer_Skin`
  These have ≤1 connection - possible missing edges or undocumented components.
- **170 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Utils` connect `Utils` to `Slider`, `Options.php`, `PublicScripts`, `MJ\WPORM\Blueprint`, `ReservePackagesMeta`, `AJAX`, `ElementorControls`, `Archive`, `ReservationSettings`, `Video`, `Social`, `Gallery`, `Wishlist`, `WC`, `Options`, `WC.php`, `.can_plugin_activate`, `functions/archive.php`, `init.php`, `Career`, `ReserveWeekDays.php`, `LenzSaveBookingProcess`, `Wishlist.php`, `Menu`, `ReserveSubjects.php`, `.is_rtl`, `elementor.php`, `Info`, `ReserveDayTimes`, `CTA`, `IconPicker`, `PlanCart`, `Testimonial`, `ProductsSlider`, `Video`, `Expert`, `Elementor`, `Portfolio`, `ImageCart`, `ArchiveFilter`, `Search_Query`, `AboutItem`, `Button`, `FilmstripText`, `GroupImages`, `shortcode-booking.php`, `PostSlider`, `Reservation`, `ServiceItem`, `VideoPortfolios`, `Scripts`, `Products`, `Heading`, `StrokeText`, `Elementor\Widget_Base`, `functions/comments.php`, `ElementorControls.php`?**
  _High betweenness centrality (0.202) - this node is a cross-community bridge._
- **Why does `Base` connect `Base` to `Product_Badges`, `Search`, `Site_Logo`, `Slider`, `Trust_Badges`, `Cards`, `Account`, `Copyright`, `Dark_Toggle`, `Home_Base`, `Icon_List`, `Mobile_Buy_Bar`, `Product_Gallery`, `Related_Products`, `Page_Base`, `Text`, `Parts`, `Product_Tabs`, `Course_Teacher`, `Post_Parts.php`, `Product_Attributes`, `Product_Breadcrumb`, `Product_Content`, `Product_Excerpt`, `Product_Highlights`, `Product_Price`, `Nav_Menu`, `Product_Rating`, `studiare-extensions/includes/Core/Persian.php`, `Product_Reviews`, `Product_Grid`, `Persian`, `.is_rtl`, `Product_Stock`, `Product_Title`, `Product_Info`, `Post_Parts`, `Blog_Base`, `studiare-extensions/includes/Core/Theme_Bridge.php`, `Search_Query`, `Icon_Library`, `Category_Parts`, `Cart`, `Elementor\Widget_Base`, `Parts.php`, `Integration`, `Add_To_Cart`, `Button`?**
  _High betweenness centrality (0.131) - this node is a cross-community bridge._
- **Why does `ElementorControls` connect `ElementorControls` to `Archive`, `Menu`, `ContactForm`, `Portfolios`, `CTA`, `PlanCart`, `ProductsSlider`, `Testimonial`, `Video`, `Elementor`, `ImageCart`, `AboutItem`, `Button`, `FilmstripText`, `GroupImages`, `Marquee`, `PostSlider`, `Reservation`, `ServiceItem`, `VideoPortfolios`, `Heading`, `StrokeText`, `Elementor\Widget_Base`, `ElementorControls.php`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Are the 457 inferred relationships involving `self` (e.g. with `.color()` and `.range()`) actually correct?**
  _`self` has 457 INFERRED edges - model-reasoned connections that need verification._
- **Are the 30 inferred relationships involving `Utils` (e.g. with `.check_nonce()` and `.check_requires()`) actually correct?**
  _`Utils` has 30 INFERRED edges - model-reasoned connections that need verification._
- **Are the 110 inferred relationships involving `El` (e.g. with `.academy()` and `.academy_hero()`) actually correct?**
  _`El` has 110 INFERRED edges - model-reasoned connections that need verification._
- **Are the 70 inferred relationships involving `ElementorControls` (e.g. with `.general_style_controls()` and `.text_style_controls()`) actually correct?**
  _`ElementorControls` has 70 INFERRED edges - model-reasoned connections that need verification._