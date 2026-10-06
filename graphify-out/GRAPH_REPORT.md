# Graph Report - Lenz-Plus  (2026-10-05)

## Corpus Check
- 501 files · ~400,002 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 3497 nodes · 5784 edges · 345 communities (166 shown, 179 thin omitted)
- Extraction: 79% EXTRACTED · 21% INFERRED · 0% AMBIGUOUS · INFERRED: 1204 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `435bd160`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Utils
- bottom-nav-admin.js
- self
- Slider
- Channels
- PublicScripts
- MJ\WPORM\Blueprint
- Blocks
- AJAX
- ElementorControls
- .is_rtl
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
- studiare-extensions/includes/Core/Theme_Bridge.php
- Contact_Form
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
- lenz-plus/assets/modules/support-button/js/support-button-admin.js
- ContactForm
- Portfolios
- lenz-plus/assets/modules/bottom-nav/js/bottom-nav-admin.js
- Styles
- Post_Parts
- Blog
- CTA
- PlanCart
- ProductsSlider
- Testimonial
- Elementor\Widget_Base
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
- Search_Results
- video-player.js
- AboutItem
- Button
- ReserveWeekDays.php
- GroupImages
- Marquee
- PostSlider
- Contact_Inbox
- Reservation
- ServiceItem
- VideoPortfolios
- Page
- Products
- home.js
- Icon_Library
- Fields
- Icon_Library
- Admin
- Category_Grid
- Frontend
- Countdown
- Heading
- Plugin
- TGMPA_Utils
- Asset
- Design_Pages
- Item_Resolver
- Cart
- lenz-plus/assets/modules/bottom-nav/js/bottom-nav.js
- Frontend
- functions/comments.php
- MiniCart
- studiare-extensions/includes/Core/Site.php
- Module
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
- Module
- Dark_Toggle
- Home_Base
- Features
- Fields
- Icon_List
- Media
- Mobile_Buy_Bar
- Post_Tags
- MenuItems
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
- Renderer
- Post_Author
- Post_Comments
- Post_Share
- Post_Title
- Post_Toc
- Product_Attributes
- Elementor
- Product_Content
- Product_Excerpt
- Product_Highlights
- Product_Price
- Product_Rating
- Product_Reviews
- Product_Stock
- Scripts
- Reading_Progress
- Plugin
- Theme_Bridge
- Career
- AdminUI
- Video
- support-button.js
- Frontend
- booking.js
- reservation.js
- Module
- Info
- Expert
- Portfolio
- pages.js
- slider.js
- Product
- Autoloader
- Styles
- Wishlist.php
- Module
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
- Search_Query
- Live_Search
- Ajax_Controller
- Admin
- Timeline
- Schema
- Archive_Types
- Asset
- Category_Parts
- Course
- Autoloader
- Site
- Color
- WP_Post
- .render_nav
- Notices
- ReserveDayTimes
- Course_Teacher
- lenz-plus/assets/modules/support-button/js/support-button.js
- Ajax_Controller
- Blog_Base
- Blog_Breadcrumb
- Page_Base
- Arr

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
- `lenz_comment_fields()` --calls--> `Utils`  [EXTRACTED]
  reference/lenz/functions/comments.php → reference/lenz/inc/Utils.php
- `lenz_save_comment_stars()` --calls--> `Utils`  [EXTRACTED]
  reference/lenz/functions/comments.php → reference/lenz/inc/Utils.php
- `lenz_comment_star_column()` --calls--> `Utils`  [EXTRACTED]
  reference/lenz/functions/comments.php → reference/lenz/inc/Utils.php

## Import Cycles
- None detected.

## Communities (345 total, 179 thin omitted)

### Community 0 - "Utils"
Cohesion: 0.02
Nodes (9): lenz_breadcrumb(), lenz_modify_post_per_page(), lenz_sort_posts(), lenz_register_elementor_widgets(), Heading, DashboardFont, Wishlist, Update (+1 more)

### Community 1 - "bottom-nav-admin.js"
Cohesion: 0.07
Nodes (50): bindControls(), confirmDialog(), createStore(), escapeHtml(), getPath(), initColorFields(), initModuleSwitches(), paintRange() (+42 more)

### Community 3 - "Slider"
Cohesion: 0.06
Nodes (4): Elementor\Utils, Picture, Post_Image, Slider

### Community 6 - "MJ\WPORM\Blueprint"
Cohesion: 0.12
Nodes (9): MJ\WPORM\Blueprint, MJ\WPORM\Model, Booking, BookingMeta, ReserveLocations, ReservePackages, ReservePlans, ReserveSubjects (+1 more)

### Community 8 - "AJAX"
Cohesion: 0.11
Nodes (4): AJAX, GetAvailableTimes, IconPicker, MiniCartSetQTY

### Community 10 - ".is_rtl"
Cohesion: 0.33
Nodes (3): lenz_admin_enqueue(), lenz_wc_product_footer(), lenz_woocommerce_pagination_icons()

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
Cohesion: 0.07
Nodes (6): Context, WC_Product, WP_Post, Parts, WC_Product, Product_Info

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

### Community 36 - "Template_Post_Type"
Cohesion: 0.10
Nodes (4): Module, Product_Meta_Box, Module, Template_Post_Type

### Community 37 - "support-button-admin.js"
Cohesion: 0.21
Nodes (19): channelGlyph(), channelUrl(), handleUrl(), initChannels(), initPreview(), international(), move(), queueRender() (+11 more)

### Community 39 - "Base"
Cohesion: 0.08
Nodes (5): Cards, WC_Product, WP_Post, Base, WC_Product

### Community 50 - "init.php"
Cohesion: 0.12
Nodes (3): lenz_include_shortcodes(), lenz_init(), lenz_register_widgets()

### Community 53 - "Module"
Cohesion: 0.14
Nodes (3): Fixes, Module, Schema

### Community 54 - "lenz-plus/assets/modules/support-button/js/support-button-admin.js"
Cohesion: 0.20
Nodes (19): channelGlyph(), channelUrl(), handleUrl(), initChannels(), initPreview(), international(), move(), queueRender() (+11 more)

### Community 57 - "lenz-plus/assets/modules/bottom-nav/js/bottom-nav-admin.js"
Cohesion: 0.11
Nodes (38): buildRow(), close(), commitItems(), decorate(), faClass(), fillChecks(), fillOptions(), hasActiveVariant() (+30 more)

### Community 64 - "Testimonial"
Cohesion: 0.10
Nodes (3): Button, Testimonial, Sanitizers

### Community 65 - "Elementor\Widget_Base"
Cohesion: 0.07
Nodes (4): Elementor\Widget_Base, AccountButton, Menu, Video

### Community 66 - "ArchiveFilter"
Cohesion: 0.15
Nodes (3): ArchiveFilter, PostToc, WP_Widget

### Community 67 - "blog.js"
Cohesion: 0.29
Nodes (13): announce(), copyText(), hookElementor(), initCategoryMenu(), initCategoryRow(), initProgress(), initScope(), initShare() (+5 more)

### Community 72 - "lenz-plus/assets/admin/js/admin.js"
Cohesion: 0.14
Nodes (13): bindControls(), confirmDialog(), createStore(), escapeHtml(), getPath(), initColorFields(), initModuleSwitches(), paintRange() (+5 more)

### Community 79 - "video-player.js"
Cohesion: 0.27
Nodes (9): fadeInVideoData(), fadeOutVideoData(), pauseOtherVideos(), playActions(), resetFadeOutTimer(), setResponsiveClass(), setVolumeIcon(), togglePlayPause() (+1 more)

### Community 80 - "AboutItem"
Cohesion: 0.07
Nodes (3): AboutItem, FilmstripText, StrokeText

### Community 92 - "home.js"
Cohesion: 0.32
Nodes (11): formatCount(), hookElementor(), initCount(), initCountdown(), initFilter(), initNewsletter(), initRail(), initScope() (+3 more)

### Community 101 - "Plugin"
Cohesion: 0.18
Nodes (3): Module, Plugin, LenzPlus\Core\Module

### Community 103 - "Asset"
Cohesion: 0.14
Nodes (3): Asset, Assets, Live_Search

### Community 107 - "lenz-plus/assets/modules/bottom-nav/js/bottom-nav.js"
Cohesion: 0.14
Nodes (17): bind(), bindSheet(), close(), enableDragToClose(), focusables(), init(), isInterceptableLink(), isSamePage() (+9 more)

### Community 109 - "functions/comments.php"
Cohesion: 0.20
Nodes (4): lenz_comment_fields(), lenz_comment_star_column(), lenz_comment_stars(), lenz_save_comment_stars()

### Community 112 - "Module"
Cohesion: 0.09
Nodes (3): Item_Types, Module, Schema

### Community 121 - "post.php"
Cohesion: 0.25
Nodes (4): lenz_add_post_views(), lenz_get_post_views(), lenz_post_thumbnail(), lenz_woocommerce_template_loop_product_thumbnail()

### Community 129 - "Search"
Cohesion: 0.07
Nodes (4): Product_Breadcrumb, Product_Gallery, Product_Title, Search

### Community 136 - "class-tgm-plugin-activation.php"
Cohesion: 0.25
Nodes (5): load_tgm_plugin_activation(), TGM_Bulk_Installer, TGM_Bulk_Installer_Skin, tgmpa(), lenz_register_required_plugins()

### Community 157 - "otp-digits.js"
Cohesion: 0.52
Nodes (6): digits(), finish(), finishForm(), normalize(), otpField(), write()

### Community 158 - "Search_Query"
Cohesion: 0.13
Nodes (3): WP_Post, Search_Query, WP_Post

### Community 178 - "Theme_Bridge"
Cohesion: 0.07
Nodes (3): Theme_Bridge, Item_Types, Schema

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

### Community 310 - "Search_Query"
Cohesion: 0.09
Nodes (5): WP_Post, Search_Query, Live_Search, WP_Post, Search_Scope

### Community 337 - "lenz-plus/assets/modules/support-button/js/support-button.js"
Cohesion: 0.60
Nodes (5): closeMenu(), greetingDismissed(), hideGreeting(), initGreeting(), initMenu()

## Knowledge Gaps
- **2 isolated node(s):** `TGM_Bulk_Installer`, `TGM_Bulk_Installer_Skin`
  These have ≤1 connection - possible missing edges or undocumented components.
- **179 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Utils` connect `Utils` to `Slider`, `PublicScripts`, `MJ\WPORM\Blueprint`, `ReservePackagesMeta`, `AJAX`, `ElementorControls`, `Archive`, `ReservationSettings`, `MenuItems`, `Social`, `Gallery`, `Wishlist`, `WC`, `Options`, `WC.php`, `Elementor`, `.can_plugin_activate`, `Scripts`, `init.php`, `Career`, `AdminUI`, `LenzSaveBookingProcess`, `Video`, `Info`, `Expert`, `CTA`, `PlanCart`, `ProductsSlider`, `Testimonial`, `Elementor\Widget_Base`, `Portfolio`, `ArchiveFilter`, `Wishlist.php`, `ImageCart`, `Notices`, `ReserveDayTimes`, `AboutItem`, `Button`, `ReserveWeekDays.php`, `GroupImages`, `shortcode-booking.php`, `PostSlider`, `Reservation`, `ServiceItem`, `VideoPortfolios`, `Page`, `Products`, `Heading`, `functions/comments.php`, `MiniCart`?**
  _High betweenness centrality (0.204) - this node is a cross-community bridge._
- **Why does `Base` connect `Base` to `Product_Badges`, `Search`, `Site_Logo`, `Slider`, `Trust_Badges`, `Account`, `.is_rtl`, `Dark_Toggle`, `Home_Base`, `Icon_List`, `Mobile_Buy_Bar`, `Related_Products`, `studiare-extensions/includes/Core/Theme_Bridge.php`, `Text`, `Parts`, `Product_Tabs`, `Product_Attributes`, `Product_Content`, `Product_Excerpt`, `Product_Highlights`, `Product_Price`, `Nav_Menu`, `Product_Rating`, `studiare-extensions/includes/Core/Persian.php`, `Product_Reviews`, `Product_Grid`, `Persian`, `Product_Stock`, `Post_Parts`, `Category_Parts`, `Elementor\Widget_Base`, `Search_Results`, `Course_Teacher`, `Blog_Base`, `Page_Base`, `Icon_Library`, `Cart`, `Parts.php`, `Integration`, `Add_To_Cart`, `Button`?**
  _High betweenness centrality (0.152) - this node is a cross-community bridge._
- **Why does `ElementorControls` connect `ElementorControls` to `Archive`, `Elementor`, `ContactForm`, `Portfolios`, `CTA`, `PlanCart`, `ProductsSlider`, `Testimonial`, `Elementor\Widget_Base`, `ImageCart`, `AboutItem`, `Button`, `GroupImages`, `Marquee`, `PostSlider`, `Reservation`, `ServiceItem`, `VideoPortfolios`, `Heading`, `MiniCart`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **Are the 488 inferred relationships involving `self` (e.g. with `.color()` and `.range()`) actually correct?**
  _`self` has 488 INFERRED edges - model-reasoned connections that need verification._
- **Are the 30 inferred relationships involving `Utils` (e.g. with `.check_nonce()` and `.check_requires()`) actually correct?**
  _`Utils` has 30 INFERRED edges - model-reasoned connections that need verification._
- **Are the 110 inferred relationships involving `El` (e.g. with `.academy()` and `.academy_hero()`) actually correct?**
  _`El` has 110 INFERRED edges - model-reasoned connections that need verification._
- **Are the 70 inferred relationships involving `ElementorControls` (e.g. with `.general_style_controls()` and `.text_style_controls()`) actually correct?**
  _`ElementorControls` has 70 INFERRED edges - model-reasoned connections that need verification._