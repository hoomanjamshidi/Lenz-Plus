# Graph Report - Lenz-Plus  (2026-10-06)

## Corpus Check
- 520 files · ~418,475 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 3685 nodes · 6053 edges · 341 communities (173 shown, 168 thin omitted)
- Extraction: 79% EXTRACTED · 21% INFERRED · 0% AMBIGUOUS · INFERRED: 1276 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `3af7f0af`
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
- PostsArchive
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
- studiare-extensions/includes/Core/Persian.php
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
- ElementorControls.php
- ArchiveFilter
- blog.js
- Sanitizer
- Contact_Details
- ImageCart
- lenz-plus/assets/admin/js/admin.js
- Theme_Bridge
- Frontend
- Blog_Base
- Newsletter
- Contact
- Search_Results
- video-player.js
- AboutItem
- Button
- ReserveWeekDays.php
- GroupImages
- Marquee
- Options.php
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
- Library
- Category_Grid
- Frontend
- Countdown
- Heading
- Base
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
- Parts.php
- Integration
- Button
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
- ReservePackagesMeta.php
- class-tgm-plugin-activation.php
- Blog_Pages
- Account
- Module
- Dark_Toggle
- Events
- Features
- Fields
- Icon_List
- Archive
- Mobile_Buy_Bar
- Module
- Template_Post_Type
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
- Video
- Renderer
- Elementor\Widget_Base
- lenz-plus/assets/modules/builder/js/builder-admin.js
- FilmstripText
- Design_Pages
- Integration
- Product_Attributes
- Text
- Product_Content
- Product_Excerpt
- Product_Highlights
- Product_Price
- Product_Rating
- Product_Reviews
- Product_Stock
- Scripts
- Product_Gallery
- Plugin
- Theme_Bridge
- Career
- .handle
- Video
- support-button.js
- Frontend
- booking.js
- reservation.js
- Copyright
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
- Product_Breadcrumb
- Admin
- Product_Title
- functions/archive.php
- IconPicker
- Asset
- Category_Parts
- Arr
- Autoloader
- El
- Color
- WP_Post
- Home_Base
- ReserveDayTimes
- Course_Teacher
- lenz-plus/assets/modules/support-button/js/support-button.js
- Blog_Breadcrumb

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

## Communities (341 total, 168 thin omitted)

### Community 0 - "Utils"
Cohesion: 0.02
Nodes (6): Heading, DashboardFont, Wishlist, Update, Utils, Elementor

### Community 1 - "bottom-nav-admin.js"
Cohesion: 0.07
Nodes (50): bindControls(), confirmDialog(), createStore(), escapeHtml(), getPath(), initColorFields(), initModuleSwitches(), paintRange() (+42 more)

### Community 5 - "PublicScripts"
Cohesion: 0.06
Nodes (6): AdminScripts, MenuItems, File, PublicScripts, Plans, AdminUI

### Community 6 - "MJ\WPORM\Blueprint"
Cohesion: 0.13
Nodes (9): MJ\WPORM\Blueprint, MJ\WPORM\Model, Booking, BookingMeta, ReserveLocations, ReservePackages, ReservePlans, ReserveSubjects (+1 more)

### Community 8 - "AJAX"
Cohesion: 0.10
Nodes (4): AJAX, GetAvailableTimes, MiniCartSetQTY, Notices

### Community 9 - "ElementorControls"
Cohesion: 0.05
Nodes (3): Experts, PostSlider, ElementorControls

### Community 10 - ".is_rtl"
Cohesion: 0.33
Nodes (3): lenz_admin_enqueue(), lenz_wc_product_footer(), lenz_woocommerce_pagination_icons()

### Community 11 - "builder.js"
Cohesion: 0.12
Nodes (32): bindVariationImages(), boot(), buildChoices(), closeAll(), embedUrl(), escape(), fetchResults(), formOf() (+24 more)

### Community 12 - "El"
Cohesion: 0.09
Nodes (3): Course, El, Header

### Community 15 - "ReservationSettings"
Cohesion: 0.06
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

### Community 24 - "Contact_Form"
Cohesion: 0.09
Nodes (3): Contact_Form, Page_Base, Timeline

### Community 27 - "Parts"
Cohesion: 0.08
Nodes (6): Context, WC_Product, WP_Post, Parts, WC_Product, Product_Info

### Community 29 - "Channels"
Cohesion: 0.07
Nodes (3): Channels, Module, Schema

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
Cohesion: 0.07
Nodes (5): Cards, WC_Product, WP_Post, Base, WC_Product

### Community 40 - "Module"
Cohesion: 0.05
Nodes (7): Admin, Ajax_Controller, Arr, Module, Module, Plugin, LenzPlus\Core\Module

### Community 48 - "Product_Grid"
Cohesion: 0.07
Nodes (3): Heading, Media, Product_Grid

### Community 50 - "init.php"
Cohesion: 0.12
Nodes (3): lenz_include_shortcodes(), lenz_init(), lenz_register_widgets()

### Community 53 - "Module"
Cohesion: 0.15
Nodes (3): Fixes, Module, Schema

### Community 54 - "lenz-plus/assets/modules/support-button/js/support-button-admin.js"
Cohesion: 0.20
Nodes (19): channelGlyph(), channelUrl(), handleUrl(), initChannels(), initPreview(), international(), move(), queueRender() (+11 more)

### Community 57 - "lenz-plus/assets/modules/bottom-nav/js/bottom-nav-admin.js"
Cohesion: 0.11
Nodes (38): buildRow(), close(), commitItems(), decorate(), faClass(), fillChecks(), fillOptions(), hasActiveVariant() (+30 more)

### Community 59 - "Post_Parts"
Cohesion: 0.04
Nodes (8): WP_Post, Post_Parts, Archive_Title, Post_Author, Post_Content, Post_Share, Post_Tags, Post_Title

### Community 66 - "ArchiveFilter"
Cohesion: 0.15
Nodes (3): ArchiveFilter, PostToc, WP_Widget

### Community 67 - "blog.js"
Cohesion: 0.29
Nodes (13): announce(), copyText(), hookElementor(), initCategoryMenu(), initCategoryRow(), initProgress(), initScope(), initShare() (+5 more)

### Community 72 - "lenz-plus/assets/admin/js/admin.js"
Cohesion: 0.14
Nodes (13): bindControls(), confirmDialog(), createStore(), escapeHtml(), getPath(), initColorFields(), initModuleSwitches(), paintRange() (+5 more)

### Community 75 - "Blog_Base"
Cohesion: 0.06
Nodes (5): Blog_Base, Post_Comments, Post_Nav, Post_Toc, Reading_Progress

### Community 79 - "video-player.js"
Cohesion: 0.27
Nodes (9): fadeInVideoData(), fadeOutVideoData(), pauseOtherVideos(), playActions(), resetFadeOutTimer(), setResponsiveClass(), setVolumeIcon(), togglePlayPause() (+1 more)

### Community 92 - "home.js"
Cohesion: 0.32
Nodes (11): formatCount(), hookElementor(), initCount(), initCountdown(), initFilter(), initNewsletter(), initRail(), initScope() (+3 more)

### Community 96 - "Library"
Cohesion: 0.08
Nodes (4): Library, Module, Catalog, Thumbs

### Community 103 - "Asset"
Cohesion: 0.13
Nodes (3): Asset, Assets, Live_Search

### Community 105 - "Item_Resolver"
Cohesion: 0.07
Nodes (3): Archive_Types, Item_Resolver, Search_Scope

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

### Community 136 - "class-tgm-plugin-activation.php"
Cohesion: 0.25
Nodes (5): load_tgm_plugin_activation(), TGM_Bulk_Installer, TGM_Bulk_Installer_Skin, tgmpa(), lenz_register_required_plugins()

### Community 145 - "Archive"
Cohesion: 0.10
Nodes (4): Button, Archive, Sanitizers, lenz_wishlist_item_endpoint_content()

### Community 157 - "otp-digits.js"
Cohesion: 0.52
Nodes (6): digits(), finish(), finishForm(), normalize(), otpField(), write()

### Community 162 - "lenz-plus/assets/modules/builder/js/builder-admin.js"
Cohesion: 0.27
Nodes (10): applyResult(), createPageDialog(), designCard(), newTemplateDialog(), pageKind(), renderAll(), renderHomeDesigns(), renderHomePages() (+2 more)

### Community 177 - "Plugin"
Cohesion: 0.13
Nodes (3): Ajax_Controller, Module, Plugin

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
Cohesion: 0.08
Nodes (5): WP_Post, Search_Query, Live_Search, WP_Post, Search_Scope

### Community 315 - "functions/archive.php"
Cohesion: 0.33
Nodes (3): lenz_breadcrumb(), lenz_modify_post_per_page(), lenz_sort_posts()

### Community 318 - "Category_Parts"
Cohesion: 0.25
Nodes (3): Category_Parts, WP_Term, WP_Post

### Community 337 - "lenz-plus/assets/modules/support-button/js/support-button.js"
Cohesion: 0.60
Nodes (5): closeMenu(), greetingDismissed(), hideGreeting(), initGreeting(), initMenu()

## Knowledge Gaps
- **2 isolated node(s):** `TGM_Bulk_Installer`, `TGM_Bulk_Installer_Skin`
  These have ≤1 connection - possible missing edges or undocumented components.
- **168 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Utils` connect `Utils` to `Slider`, `PublicScripts`, `MJ\WPORM\Blueprint`, `ReservePackagesMeta.php`, `AJAX`, `ElementorControls`, `PostsArchive`, `ReservationSettings`, `Archive`, `Social`, `Gallery`, `Wishlist`, `WC`, `Video`, `Options`, `Elementor\Widget_Base`, `WC.php`, `FilmstripText`, `.can_plugin_activate`, `Scripts`, `init.php`, `Career`, `LenzSaveBookingProcess`, `Video`, `Search_Query`, `functions/archive.php`, `IconPicker`, `CTA`, `PlanCart`, `ProductsSlider`, `Testimonial`, `ElementorControls.php`, `Info`, `Expert`, `Wishlist.php`, `Portfolio`, `ImageCart`, `ArchiveFilter`, `ReserveDayTimes`, `AboutItem`, `Button`, `ReserveWeekDays.php`, `GroupImages`, `shortcode-booking.php`, `Options.php`, `Reservation`, `ServiceItem`, `Page`, `Products`, `Heading`, `functions/comments.php`, `MiniCart`?**
  _High betweenness centrality (0.191) - this node is a cross-community bridge._
- **Why does `Base` connect `Base` to `Product_Badges`, `Search`, `Site_Logo`, `Slider`, `Trust_Badges`, `Account`, `.is_rtl`, `Dark_Toggle`, `Icon_List`, `Mobile_Buy_Bar`, `Related_Products`, `Contact_Form`, `Text`, `Parts`, `Product_Tabs`, `Elementor\Widget_Base`, `Product_Attributes`, `Product_Content`, `Product_Excerpt`, `Product_Highlights`, `Product_Price`, `Nav_Menu`, `Product_Rating`, `studiare-extensions/includes/Core/Persian.php`, `Product_Reviews`, `Product_Grid`, `Persian`, `Product_Gallery`, `Product_Stock`, `Product_Breadcrumb`, `Copyright`, `Post_Parts`, `Product_Title`, `Category_Parts`, `Blog_Base`, `Home_Base`, `Search_Results`, `Course_Teacher`, `Icon_Library`, `Cart`, `Parts.php`, `Integration`, `Add_To_Cart`, `Button`?**
  _High betweenness centrality (0.123) - this node is a cross-community bridge._
- **Why does `ElementorControls` connect `ElementorControls` to `Utils`, `PostsArchive`, `Archive`, `Video`, `Elementor\Widget_Base`, `FilmstripText`, `ContactForm`, `Portfolios`, `CTA`, `PlanCart`, `ProductsSlider`, `Testimonial`, `ElementorControls.php`, `ImageCart`, `AboutItem`, `Button`, `GroupImages`, `Marquee`, `Reservation`, `ServiceItem`, `VideoPortfolios`, `Heading`, `MiniCart`?**
  _High betweenness centrality (0.045) - this node is a cross-community bridge._
- **Are the 525 inferred relationships involving `self` (e.g. with `.color()` and `.range()`) actually correct?**
  _`self` has 525 INFERRED edges - model-reasoned connections that need verification._
- **Are the 30 inferred relationships involving `Utils` (e.g. with `.check_nonce()` and `.check_requires()`) actually correct?**
  _`Utils` has 30 INFERRED edges - model-reasoned connections that need verification._
- **Are the 110 inferred relationships involving `El` (e.g. with `.academy()` and `.academy_hero()`) actually correct?**
  _`El` has 110 INFERRED edges - model-reasoned connections that need verification._
- **Are the 70 inferred relationships involving `ElementorControls` (e.g. with `.general_style_controls()` and `.text_style_controls()`) actually correct?**
  _`ElementorControls` has 70 INFERRED edges - model-reasoned connections that need verification._