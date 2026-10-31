# Graph Report - Lenz-Plus  (2026-10-25)

## Corpus Check
- 624 files · ~521,722 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 4378 nodes · 6801 edges · 401 communities (178 shown, 223 thin omitted)
- Extraction: 82% EXTRACTED · 18% INFERRED · 0% AMBIGUOUS · INFERRED: 1246 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c99e168a`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Utils
- bottom-nav-admin.js
- self
- Slider
- Channels
- MenuItems
- MJ\WPORM\Blueprint
- Blocks
- AJAX
- ElementorControls
- Header_Footer
- builder.js
- El
- TGM_Plugin_Activation
- WC.php
- ReservationSettings
- bottom-nav.js
- Library
- Post_Categories
- Item_Resolver
- Header_Footer
- Sanitizer
- builder-admin.js
- studiare-extensions/includes/Core/Theme_Bridge.php
- Frontend
- Module
- Resolver
- Parts
- Footer
- Icon_Library
- WC
- Product_Tabs
- Options
- Site_Logo
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
- Author_Box
- LenzSaveBookingProcess
- Otp_Digits
- Module
- lenz-plus/assets/modules/support-button/js/support-button-admin.js
- ContactForm
- Home
- lenz-plus/assets/modules/bottom-nav/js/bottom-nav-admin.js
- Styles
- Post_Parts
- Blog
- PostSlider
- PlanCart
- ProductsSlider
- Testimonial
- init.php
- Inbox
- blog.js
- Sanitizer
- Schema
- ImageCart
- lenz-plus/assets/admin/js/admin.js
- Theme_Bridge
- Contact_Messages
- Post_Toc
- Newsletter
- Contact
- Search_Results
- video-player.js
- Elementor\Widget_Base
- Heading
- Theme_Bridge
- GroupImages
- Marquee
- Portfolio_Data
- Contact_Inbox
- Course_Data
- Post_Grid
- VideoPortfolios
- Archive
- Portfolio
- home.js
- Icon_Library
- Fields
- Booking_Requests
- Library
- Reservation
- MiniCart
- Countdown
- Post_Meta
- Base
- Nav_Menu
- studiare-extensions/includes/Modules/Builder/Elementor/Picture.php
- Design_Pages
- Item_Resolver
- Link_Column
- lenz-plus/assets/modules/bottom-nav/js/bottom-nav.js
- Contact_Box
- Header_Action
- Booking_Form
- studiare-extensions/includes/Core/Site.php
- Module
- Integration
- Button
- Add_To_Cart
- Offer_Price
- Public_Form
- Pricing_Plans
- Product_Spotlight
- post.php
- Admin
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
- Booking_Inbox
- class-tgm-plugin-activation.php
- Blog_Pages
- Account
- Module
- Page_Hero
- Events
- Features
- Fields
- Icon_List
- Search_Query
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
- Picture
- lenz-plus/assets/modules/builder/js/builder-admin.js
- FilmstripText
- Products
- Integration
- ServiceItem
- Catalog
- Product_Content
- Product_Excerpt
- Product_Highlights
- Product_Price
- Product_Rating
- Product_Reviews
- Product_Stock
- Thumbs
- Product_Gallery
- Trust_Badges
- Item_Types
- Career
- .handle
- Video
- support-button.js
- Frontend
- booking.js
- reservation.js
- Courses
- Form_Base
- Plugin
- El
- pages.js
- slider.js
- Product
- Autoloader
- Styles
- Social_Links
- Module
- reservation-settings-locations.js
- reservation-settings-suggest_locations.js
- my-account.js
- Messages
- Expert
- Portfolio
- Video
- Page_Base
- Category
- Tag
- Category
- Tag
- V1_5_0_0
- Blog_Base
- Project_Gallery
- Product_Breadcrumb
- Product_Title
- PublicScripts
- Cart
- Channels
- lenz-plus/assets/modules/builder/js/builder.js
- Breadcrumb
- Autoloader
- Copyright
- Color
- Context
- Resolver
- Info
- Blog
- Button
- lenz-plus/assets/modules/support-button/js/support-button.js
- Post_Tags
- Post_Parts.php
- Form_Fields
- Post_Author
- Post_Comments
- Post_Content
- Post_Nav
- Post_Share
- Post_Title
- Reading_Progress
- Footer
- Header
- Wishlist.php
- Archive_Types
- Module
- Design_Pages
- Contact_Cards
- Contact
- Portfolio_Grid
- Related_Items
- Section_Base
- Text
- Project_Meta_Box
- Site
- Brand_Logos
- Scripts
- Course_Enroll_Band
- Map_Embed
- Stats
- Course
- Post_Header
- Post_Nav
- Ajax_Controller
- Home_Base
- Category_Grid
- Picture
- Portfolios
- Media
- Assets
- Course_Teacher
- Notices
- ArchiveFilter
- GetDayTimes
- ReserveWeekDays.php
- Expert
- Frontend
- AboutItem
- Frontend
- shortcode-booking.php
- Asset
- Booking
- ReservePackagesMeta
- Blog_Breadcrumb
- Product_Attributes
- .is_rtl
- forms.js
- Portfolio

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
10. `Thumbs` - 39 edges

## Surprising Connections (you probably didn't know these)
- `lenz_modify_post_per_page()` --calls--> `Options`  [INFERRED]
  reference/lenz/functions/archive.php → reference/lenz/inc/Utils/utils-options.php
- `lenz_init()` --calls--> `Options`  [INFERRED]
  reference/lenz/functions/init.php → reference/lenz/inc/Utils/utils-options.php
- `lenz_register_elementor_widgets()` --calls--> `Options`  [INFERRED]
  reference/lenz/functions/elementor.php → reference/lenz/inc/Utils/utils-options.php
- `lenz_include_shortcodes()` --calls--> `Options`  [INFERRED]
  reference/lenz/functions/init.php → reference/lenz/inc/Utils/utils-options.php
- `lenz_register_widgets()` --calls--> `Utils`  [EXTRACTED]
  reference/lenz/functions/init.php → reference/lenz/inc/Utils.php

## Import Cycles
- None detected.

## Communities (401 total, 223 thin omitted)

### Community 0 - "Utils"
Cohesion: 0.02
Nodes (15): lenz_breadcrumb(), lenz_modify_post_per_page(), lenz_sort_posts(), lenz_comment_fields(), lenz_comment_star_column(), lenz_comment_stars(), lenz_save_comment_stars(), lenz_register_elementor_widgets() (+7 more)

### Community 1 - "bottom-nav-admin.js"
Cohesion: 0.07
Nodes (50): bindControls(), confirmDialog(), createStore(), escapeHtml(), getPath(), initColorFields(), initModuleSwitches(), paintRange() (+42 more)

### Community 2 - "self"
Cohesion: 0.06
Nodes (3): DateTimeInterface, Thumbs, self

### Community 5 - "MenuItems"
Cohesion: 0.12
Nodes (3): MenuItems, Plans, AdminUI

### Community 6 - "MJ\WPORM\Blueprint"
Cohesion: 0.15
Nodes (9): MJ\WPORM\Blueprint, MJ\WPORM\Model, Booking, BookingMeta, ReserveLocations, ReservePackages, ReservePlans, ReserveSubjects (+1 more)

### Community 8 - "AJAX"
Cohesion: 0.11
Nodes (4): AJAX, GetAvailableTimes, IconPicker, MiniCartSetQTY

### Community 9 - "ElementorControls"
Cohesion: 0.05
Nodes (3): CTA, Experts, ElementorControls

### Community 11 - "builder.js"
Cohesion: 0.12
Nodes (32): bindVariationImages(), boot(), buildChoices(), closeAll(), embedUrl(), escape(), fetchResults(), formOf() (+24 more)

### Community 14 - "WC.php"
Cohesion: 0.10
Nodes (5): lenz_requests_item_endpoint_content(), lenz_wc_body_classes(), lenz_wc_my_account_before_nav(), lenz_wc_order_customer_address_icon(), lenz_woocommerce_account_menu_items()

### Community 15 - "ReservationSettings"
Cohesion: 0.06
Nodes (7): AdminScripts, Locations, Main, Subjects, SuggestLocations, Times, ReservationSettings

### Community 16 - "bottom-nav.js"
Cohesion: 0.12
Nodes (21): bind(), bindSheet(), cart(), close(), dark(), enableDragToClose(), focusables(), init() (+13 more)

### Community 17 - "Library"
Cohesion: 0.09
Nodes (3): Library, Module, Catalog

### Community 18 - "Post_Categories"
Cohesion: 0.09
Nodes (5): Category_Parts, WP_Term, WP_Post, WP_Term, Post_Categories

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

### Community 33 - "Site_Logo"
Cohesion: 0.11
Nodes (3): Base, Brand_Box, Site_Logo

### Community 36 - "Template_Post_Type"
Cohesion: 0.10
Nodes (4): Module, Product_Meta_Box, Module, Template_Post_Type

### Community 37 - "support-button-admin.js"
Cohesion: 0.21
Nodes (19): channelGlyph(), channelUrl(), handleUrl(), initChannels(), initPreview(), international(), move(), queueRender() (+11 more)

### Community 39 - "Base"
Cohesion: 0.08
Nodes (5): Cards, WC_Product, WP_Post, Base, WC_Product

### Community 40 - "Module"
Cohesion: 0.05
Nodes (7): Admin, Ajax_Controller, Arr, Module, Module, Plugin, LenzPlus\Core\Module

### Community 50 - "Author_Box"
Cohesion: 0.13
Nodes (3): Blog_Base, Author_Box, Post_Toc

### Community 53 - "Module"
Cohesion: 0.14
Nodes (3): Fixes, Module, Schema

### Community 54 - "lenz-plus/assets/modules/support-button/js/support-button-admin.js"
Cohesion: 0.20
Nodes (19): channelGlyph(), channelUrl(), handleUrl(), initChannels(), initPreview(), international(), move(), queueRender() (+11 more)

### Community 57 - "lenz-plus/assets/modules/bottom-nav/js/bottom-nav-admin.js"
Cohesion: 0.11
Nodes (38): buildRow(), close(), commitItems(), decorate(), faClass(), fillChecks(), fillOptions(), hasActiveVariant() (+30 more)

### Community 65 - "init.php"
Cohesion: 0.12
Nodes (3): lenz_include_shortcodes(), lenz_init(), lenz_register_widgets()

### Community 66 - "Inbox"
Cohesion: 0.09
Nodes (4): Contact_Inbox, WP_Post, Inbox, WP_Post

### Community 67 - "blog.js"
Cohesion: 0.29
Nodes (13): announce(), copyText(), hookElementor(), initCategoryMenu(), initCategoryRow(), initProgress(), initScope(), initShare() (+5 more)

### Community 72 - "lenz-plus/assets/admin/js/admin.js"
Cohesion: 0.14
Nodes (13): bindControls(), confirmDialog(), createStore(), escapeHtml(), getPath(), initColorFields(), initModuleSwitches(), paintRange() (+5 more)

### Community 73 - "Theme_Bridge"
Cohesion: 0.10
Nodes (3): WP_Term, Theme_Bridge, Dark_Toggle

### Community 79 - "video-player.js"
Cohesion: 0.27
Nodes (9): fadeInVideoData(), fadeOutVideoData(), pauseOtherVideos(), playActions(), resetFadeOutTimer(), setResponsiveClass(), setVolumeIcon(), togglePlayPause() (+1 more)

### Community 80 - "Elementor\Widget_Base"
Cohesion: 0.05
Nodes (5): Elementor\Widget_Base, AccountButton, Heading, Menu, StrokeText

### Community 89 - "VideoPortfolios"
Cohesion: 0.09
Nodes (4): GetReservationData, Button, VideoPortfolios, Sanitizers

### Community 90 - "Archive"
Cohesion: 0.09
Nodes (3): PostsArchive, Archive, lenz_wishlist_item_endpoint_content()

### Community 92 - "home.js"
Cohesion: 0.32
Nodes (11): formatCount(), hookElementor(), initCount(), initCountdown(), initFilter(), initNewsletter(), initRail(), initScope() (+3 more)

### Community 107 - "lenz-plus/assets/modules/bottom-nav/js/bottom-nav.js"
Cohesion: 0.14
Nodes (17): bind(), bindSheet(), close(), enableDragToClose(), focusables(), init(), isInterceptableLink(), isSamePage() (+9 more)

### Community 112 - "Module"
Cohesion: 0.09
Nodes (3): Item_Types, Module, Schema

### Community 121 - "post.php"
Cohesion: 0.25
Nodes (4): lenz_add_post_views(), lenz_get_post_views(), lenz_post_thumbnail(), lenz_woocommerce_template_loop_product_thumbnail()

### Community 136 - "class-tgm-plugin-activation.php"
Cohesion: 0.15
Nodes (6): load_tgm_plugin_activation(), TGM_Bulk_Installer, TGM_Bulk_Installer_Skin, tgmpa(), TGMPA_Utils, lenz_register_required_plugins()

### Community 145 - "Search_Query"
Cohesion: 0.09
Nodes (5): WP_Post, Search_Query, Live_Search, WP_Post, Search_Scope

### Community 157 - "otp-digits.js"
Cohesion: 0.52
Nodes (6): digits(), finish(), finishForm(), normalize(), otpField(), write()

### Community 158 - "Search_Query"
Cohesion: 0.06
Nodes (6): Persian, WP_Post, Search_Query, Live_Search, WP_Post, Search_Scope

### Community 162 - "lenz-plus/assets/modules/builder/js/builder-admin.js"
Cohesion: 0.20
Nodes (15): applyResult(), cardHtml(), createPageDialog(), designCard(), keywordOption(), newTemplateDialog(), pageKind(), renderAll() (+7 more)

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

### Community 317 - "Channels"
Cohesion: 0.05
Nodes (4): Contact_Details, Channels, Module, Schema

### Community 318 - "lenz-plus/assets/modules/builder/js/builder.js"
Cohesion: 0.36
Nodes (6): boot(), closeMenus(), hideLayer(), initStickyHeader(), showLayer(), toggleSubmenu()

### Community 337 - "lenz-plus/assets/modules/support-button/js/support-button.js"
Cohesion: 0.60
Nodes (5): closeMenu(), greetingDismissed(), hideGreeting(), initGreeting(), initMenu()

### Community 366 - "Scripts"
Cohesion: 0.10
Nodes (3): Settings, Scripts, Page

### Community 393 - "Asset"
Cohesion: 0.13
Nodes (3): Asset, Assets, Live_Search

### Community 399 - ".is_rtl"
Cohesion: 0.29
Nodes (3): lenz_admin_enqueue(), lenz_wc_product_footer(), lenz_woocommerce_pagination_icons()

### Community 400 - "forms.js"
Cohesion: 0.70
Nodes (4): hookElementor(), initBooking(), initForm(), initScope()

## Knowledge Gaps
- **2 isolated node(s):** `TGM_Bulk_Installer`, `TGM_Bulk_Installer_Skin`
  These have ≤1 connection - possible missing edges or undocumented components.
- **223 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Utils` connect `Utils` to `ArchiveFilter`, `GetDayTimes`, `ReserveWeekDays.php`, `Expert`, `Slider`, `MenuItems`, `MJ\WPORM\Blueprint`, `AboutItem`, `AJAX`, `ElementorControls`, `shortcode-booking.php`, `ReservePackagesMeta`, `WC.php`, `ReservationSettings`, `Portfolio`, `Social`, `Gallery`, `Wishlist`, `WC`, `Video`, `Options`, `FilmstripText`, `Products`, `ServiceItem`, `.time`, `Career`, `LenzSaveBookingProcess`, `Video`, `PublicScripts`, `PostSlider`, `PlanCart`, `ProductsSlider`, `Testimonial`, `init.php`, `ImageCart`, `Info`, `Elementor\Widget_Base`, `Button`, `GroupImages`, `VideoPortfolios`, `Archive`, `Wishlist.php`, `Reservation`, `MiniCart`, `Scripts`, `Notices`?**
  _High betweenness centrality (0.164) - this node is a cross-community bridge._
- **Why does `Base` connect `Base` to `Product_Badges`, `Search`, `Site_Logo`, `Slider`, `Trust_Badges`, `Account`, `Product_Attributes`, `.is_rtl`, `Icon_List`, `Post_Categories`, `Mobile_Buy_Bar`, `Related_Products`, `studiare-extensions/includes/Core/Theme_Bridge.php`, `Text`, `Parts`, `Icon_Library`, `Product_Tabs`, `Product_Content`, `Product_Excerpt`, `Product_Highlights`, `Product_Price`, `Nav_Menu`, `Product_Rating`, `studiare-extensions/includes/Core/Persian.php`, `Product_Reviews`, `Product_Grid`, `Persian`, `Product_Gallery`, `Product_Stock`, `Blog_Base`, `Product_Breadcrumb`, `Product_Title`, `Post_Parts`, `Cart`, `Theme_Bridge`, `Search_Results`, `Elementor\Widget_Base`, `Page_Base`, `Post_Parts.php`, `Parts.php`, `Integration`, `Add_To_Cart`, `Home_Base`, `Button`, `Course_Teacher`?**
  _High betweenness centrality (0.107) - this node is a cross-community bridge._
- **Why does `Theme_Bridge` connect `Theme_Bridge` to `Site_Logo`, `Copyright`, `Nav_Menu`, `Module`, `Link_Column`, `Header_Footer`, `Contact_Box`, `Item_Types`, `Frontend`, `Icon_Library`?**
  _High betweenness centrality (0.080) - this node is a cross-community bridge._
- **Are the 477 inferred relationships involving `self` (e.g. with `.color()` and `.range()`) actually correct?**
  _`self` has 477 INFERRED edges - model-reasoned connections that need verification._
- **Are the 30 inferred relationships involving `Utils` (e.g. with `.check_nonce()` and `.check_requires()`) actually correct?**
  _`Utils` has 30 INFERRED edges - model-reasoned connections that need verification._
- **Are the 110 inferred relationships involving `El` (e.g. with `.academy()` and `.academy_hero()`) actually correct?**
  _`El` has 110 INFERRED edges - model-reasoned connections that need verification._
- **Are the 70 inferred relationships involving `ElementorControls` (e.g. with `.general_style_controls()` and `.text_style_controls()`) actually correct?**
  _`ElementorControls` has 70 INFERRED edges - model-reasoned connections that need verification._