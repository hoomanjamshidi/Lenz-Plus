# Graph Report - Lenz-Plus  (2026-10-06)

## Corpus Check
- 534 files · ~433,036 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 3848 nodes · 6223 edges · 356 communities (174 shown, 182 thin omitted)
- Extraction: 80% EXTRACTED · 20% INFERRED · 0% AMBIGUOUS · INFERRED: 1255 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `0fb48ab4`
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
- Header_Footer
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
- builder-admin.js
- studiare-extensions/includes/Core/Theme_Bridge.php
- Page_Base
- Module
- Resolver
- Parts
- Footer
- Channels
- WC
- Product_Tabs
- WC.php
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
- Post_Parts.php
- Newsletter
- Contact
- Search_Results
- video-player.js
- AboutItem
- Button
- Theme_Bridge
- GroupImages
- Marquee
- Options.php
- Contact_Inbox
- Reservation
- ServiceItem
- VideoPortfolios
- Archive
- MenuItems
- home.js
- Icon_Library
- Fields
- Icon_Library
- Library
- Category_Parts
- Frontend
- Countdown
- Heading
- Base
- Nav_Menu
- Live_Search
- Design_Pages
- Item_Resolver
- Link_Column
- lenz-plus/assets/modules/bottom-nav/js/bottom-nav.js
- Contact_Box
- functions/comments.php
- MiniCart
- studiare-extensions/includes/Core/Site.php
- Module
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
- PostSlider
- class-tgm-plugin-activation.php
- Renderer
- Account
- Module
- Dark_Toggle
- Home_Base
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
- StrokeText
- lenz-plus/assets/modules/builder/js/builder-admin.js
- FilmstripText
- Elementor\Widget_Base
- Integration
- Product_Attributes
- Schema
- Product_Content
- Product_Excerpt
- Product_Highlights
- Product_Price
- Product_Rating
- Product_Reviews
- Product_Stock
- Thumbs
- Product_Gallery
- Plugin
- Item_Types
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
- Header_Action
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
- shortcode-booking.php
- Category
- Tag
- Category
- Tag
- V1_5_0_0
- Live_Search
- Live_Search
- Product_Breadcrumb
- Admin
- Product_Title
- Contact_Form
- Ajax_Controller
- Asset
- lenz-plus/assets/modules/builder/js/builder.js
- Arr
- Autoloader
- Copyright
- Color
- WP_Post
- Resolver
- Media
- Course_Teacher
- lenz-plus/assets/modules/support-button/js/support-button.js
- Post_Tags
- Archive_Title
- Blog_Breadcrumb
- Post_Author
- Post_Comments
- Post_Content
- Post_Nav
- Post_Share
- Post_Title
- Reading_Progress
- Footer
- Header
- Blog_Base
- Archive_Types
- Module

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
- `lenz_init()` --calls--> `Options`  [INFERRED]
  reference/lenz/functions/init.php → reference/lenz/inc/Utils/utils-options.php
- `lenz_modify_post_per_page()` --calls--> `Options`  [INFERRED]
  reference/lenz/functions/archive.php → reference/lenz/inc/Utils/utils-options.php
- `lenz_sort_posts()` --calls--> `Utils`  [EXTRACTED]
  reference/lenz/functions/archive.php → reference/lenz/inc/Utils.php
- `lenz_comment_fields()` --calls--> `Utils`  [EXTRACTED]
  reference/lenz/functions/comments.php → reference/lenz/inc/Utils.php
- `lenz_save_comment_stars()` --calls--> `Utils`  [EXTRACTED]
  reference/lenz/functions/comments.php → reference/lenz/inc/Utils.php

## Import Cycles
- None detected.

## Communities (356 total, 182 thin omitted)

### Community 0 - "Utils"
Cohesion: 0.02
Nodes (8): Button, Heading, Wishlist, Update, Utils, Elementor, Portfolio, Sanitizers

### Community 1 - "bottom-nav-admin.js"
Cohesion: 0.07
Nodes (50): bindControls(), confirmDialog(), createStore(), escapeHtml(), getPath(), initColorFields(), initModuleSwitches(), paintRange() (+42 more)

### Community 3 - "Slider"
Cohesion: 0.06
Nodes (4): Elementor\Utils, Picture, Post_Image, Slider

### Community 4 - "Channels"
Cohesion: 0.09
Nodes (3): Channels, Frontend, Schema

### Community 5 - "PublicScripts"
Cohesion: 0.10
Nodes (3): AdminScripts, File, PublicScripts

### Community 6 - "MJ\WPORM\Blueprint"
Cohesion: 0.09
Nodes (12): MJ\WPORM\Blueprint, MJ\WPORM\Model, Booking, BookingMeta, ReserveDayTimes, ReserveLocations, ReservePackages, ReservePlans (+4 more)

### Community 8 - "AJAX"
Cohesion: 0.06
Nodes (8): AJAX, GetAvailableTimes, GetDayTimes, GetReservationData, IconPicker, MiniCartSetQTY, Notices, ToggleProductWishlist

### Community 11 - "builder.js"
Cohesion: 0.12
Nodes (32): bindVariationImages(), boot(), buildChoices(), closeAll(), embedUrl(), escape(), fetchResults(), formOf() (+24 more)

### Community 12 - "El"
Cohesion: 0.09
Nodes (3): Course, El, Header

### Community 15 - "ReservationSettings"
Cohesion: 0.07
Nodes (6): Locations, Main, Subjects, SuggestLocations, Times, ReservationSettings

### Community 16 - "bottom-nav.js"
Cohesion: 0.12
Nodes (21): bind(), bindSheet(), cart(), close(), dark(), enableDragToClose(), focusables(), init() (+13 more)

### Community 17 - "Library"
Cohesion: 0.09
Nodes (3): Library, Module, Catalog

### Community 18 - "Post_Categories"
Cohesion: 0.10
Nodes (3): WP_Post, WP_Term, Post_Categories

### Community 22 - "builder-admin.js"
Cohesion: 0.14
Nodes (21): applyResult(), cardHtml(), closeTermPicker(), createPageDialog(), designCard(), keywordOption(), newTemplateDialog(), openTermPicker() (+13 more)

### Community 27 - "Parts"
Cohesion: 0.08
Nodes (6): Context, WC_Product, WP_Post, Parts, WC_Product, Product_Info

### Community 29 - "Channels"
Cohesion: 0.07
Nodes (3): Channels, Module, Schema

### Community 30 - "WC"
Cohesion: 0.07
Nodes (7): WC, WCAttributeFields, lenz_wc_add_to_cart_fragments(), lenz_wc_order_get_formatted_billing_address(), lenz_wc_order_get_formatted_shipping_address(), lenz_woocommerce_widget_shopping_cart_subtotal(), Cart

### Community 31 - "Product_Tabs"
Cohesion: 0.11
Nodes (3): Course_Curriculum, WC_Product, Product_Tabs

### Community 32 - "WC.php"
Cohesion: 0.03
Nodes (23): lenz_breadcrumb(), lenz_modify_post_per_page(), lenz_sort_posts(), lenz_admin_enqueue(), lenz_post_thumbnail(), Booking, Options, Products (+15 more)

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
Cohesion: 0.07
Nodes (5): Cards, WC_Product, WP_Post, Base, WC_Product

### Community 40 - "Module"
Cohesion: 0.05
Nodes (7): Admin, Ajax_Controller, Arr, Module, Module, Plugin, LenzPlus\Core\Module

### Community 49 - "Persian"
Cohesion: 0.09
Nodes (3): Asset, Persian, Assets

### Community 50 - "init.php"
Cohesion: 0.12
Nodes (3): lenz_include_shortcodes(), lenz_init(), lenz_register_widgets()

### Community 51 - "LenzSaveBookingProcess"
Cohesion: 0.08
Nodes (3): ReservePackagesMeta, Packages, LenzSaveBookingProcess

### Community 53 - "Module"
Cohesion: 0.15
Nodes (3): Fixes, Module, Schema

### Community 54 - "lenz-plus/assets/modules/support-button/js/support-button-admin.js"
Cohesion: 0.20
Nodes (19): channelGlyph(), channelUrl(), handleUrl(), initChannels(), initPreview(), international(), move(), queueRender() (+11 more)

### Community 57 - "lenz-plus/assets/modules/bottom-nav/js/bottom-nav-admin.js"
Cohesion: 0.11
Nodes (38): buildRow(), close(), commitItems(), decorate(), faClass(), fillChecks(), fillOptions(), hasActiveVariant() (+30 more)

### Community 66 - "ArchiveFilter"
Cohesion: 0.15
Nodes (3): ArchiveFilter, PostToc, WP_Widget

### Community 67 - "blog.js"
Cohesion: 0.29
Nodes (13): announce(), copyText(), hookElementor(), initCategoryMenu(), initCategoryRow(), initProgress(), initScope(), initShare() (+5 more)

### Community 72 - "lenz-plus/assets/admin/js/admin.js"
Cohesion: 0.14
Nodes (13): bindControls(), confirmDialog(), createStore(), escapeHtml(), getPath(), initColorFields(), initModuleSwitches(), paintRange() (+5 more)

### Community 73 - "Theme_Bridge"
Cohesion: 0.09
Nodes (3): Sanitizer, WP_Term, Theme_Bridge

### Community 79 - "video-player.js"
Cohesion: 0.27
Nodes (9): fadeInVideoData(), fadeOutVideoData(), pauseOtherVideos(), playActions(), resetFadeOutTimer(), setResponsiveClass(), setVolumeIcon(), togglePlayPause() (+1 more)

### Community 90 - "Archive"
Cohesion: 0.06
Nodes (5): Settings, Scripts, Archive, Page, lenz_wishlist_item_endpoint_content()

### Community 91 - "MenuItems"
Cohesion: 0.12
Nodes (3): MenuItems, Plans, AdminUI

### Community 92 - "home.js"
Cohesion: 0.32
Nodes (11): formatCount(), hookElementor(), initCount(), initCountdown(), initFilter(), initNewsletter(), initRail(), initScope() (+3 more)

### Community 97 - "Category_Parts"
Cohesion: 0.18
Nodes (3): Category_Parts, WP_Term, Category_Grid

### Community 101 - "Base"
Cohesion: 0.04
Nodes (5): Site, Context, Base, Text, El

### Community 104 - "Design_Pages"
Cohesion: 0.12
Nodes (3): Design_Pages, Library_Ajax, Module

### Community 107 - "lenz-plus/assets/modules/bottom-nav/js/bottom-nav.js"
Cohesion: 0.14
Nodes (17): bind(), bindSheet(), close(), enableDragToClose(), focusables(), init(), isInterceptableLink(), isSamePage() (+9 more)

### Community 109 - "functions/comments.php"
Cohesion: 0.20
Nodes (4): lenz_comment_fields(), lenz_comment_star_column(), lenz_comment_stars(), lenz_save_comment_stars()

### Community 112 - "Module"
Cohesion: 0.09
Nodes (3): Item_Types, Module, Schema

### Community 136 - "class-tgm-plugin-activation.php"
Cohesion: 0.15
Nodes (6): load_tgm_plugin_activation(), TGM_Bulk_Installer, TGM_Bulk_Installer_Skin, tgmpa(), TGMPA_Utils, lenz_register_required_plugins()

### Community 148 - "Template_Post_Type"
Cohesion: 0.09
Nodes (3): Design_Pages, Module, Template_Post_Type

### Community 157 - "otp-digits.js"
Cohesion: 0.52
Nodes (6): digits(), finish(), finishForm(), normalize(), otpField(), write()

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

### Community 310 - "Live_Search"
Cohesion: 0.18
Nodes (3): Live_Search, WP_Post, Search_Scope

### Community 318 - "lenz-plus/assets/modules/builder/js/builder.js"
Cohesion: 0.36
Nodes (6): boot(), closeMenus(), hideLayer(), initStickyHeader(), showLayer(), toggleSubmenu()

### Community 337 - "lenz-plus/assets/modules/support-button/js/support-button.js"
Cohesion: 0.60
Nodes (5): closeMenu(), greetingDismissed(), hideGreeting(), initGreeting(), initMenu()

## Knowledge Gaps
- **2 isolated node(s):** `TGM_Bulk_Installer`, `TGM_Bulk_Installer_Skin`
  These have ≤1 connection - possible missing edges or undocumented components.
- **182 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Utils` connect `Utils` to `Slider`, `PublicScripts`, `MJ\WPORM\Blueprint`, `PostSlider`, `AJAX`, `ElementorControls`, `PostsArchive`, `ReservationSettings`, `Social`, `Gallery`, `Wishlist`, `WC`, `Video`, `WC.php`, `StrokeText`, `FilmstripText`, `Elementor\Widget_Base`, `.can_plugin_activate`, `init.php`, `Career`, `LenzSaveBookingProcess`, `Video`, `Portfolios`, `Info`, `Expert`, `CTA`, `Testimonial`, `ElementorControls.php`, `ArchiveFilter`, `ImageCart`, `AboutItem`, `Button`, `shortcode-booking.php`, `GroupImages`, `Options.php`, `Reservation`, `ServiceItem`, `VideoPortfolios`, `Archive`, `MenuItems`, `Heading`, `functions/comments.php`, `MiniCart`?**
  _High betweenness centrality (0.174) - this node is a cross-community bridge._
- **Why does `Base` connect `Base` to `Product_Badges`, `Search`, `Site_Logo`, `Slider`, `Trust_Badges`, `Account`, `Dark_Toggle`, `Home_Base`, `Icon_List`, `Search_Query`, `Mobile_Buy_Bar`, `Related_Products`, `studiare-extensions/includes/Core/Theme_Bridge.php`, `Page_Base`, `Text`, `Parts`, `WC`, `Product_Tabs`, `WC.php`, `Elementor\Widget_Base`, `Product_Attributes`, `Product_Content`, `Product_Excerpt`, `Product_Highlights`, `Product_Price`, `Nav_Menu`, `Product_Rating`, `studiare-extensions/includes/Core/Persian.php`, `Product_Reviews`, `Product_Grid`, `Persian`, `Product_Gallery`, `Product_Stock`, `Product_Breadcrumb`, `Copyright`, `Post_Parts`, `Product_Title`, `Post_Parts.php`, `Search_Results`, `Course_Teacher`, `Icon_Library`, `Blog_Base`, `Category_Parts`, `Parts.php`, `Integration`, `Add_To_Cart`, `Button`?**
  _High betweenness centrality (0.124) - this node is a cross-community bridge._
- **Why does `Theme_Bridge` connect `Theme_Bridge` to `Site_Logo`, `Copyright`, `Nav_Menu`, `Schema`, `Module`, `Item_Resolver`, `Link_Column`, `Header_Footer`, `Contact_Box`, `Item_Types`, `Header_Action`, `Frontend`, `Icon_Library`?**
  _High betweenness centrality (0.075) - this node is a cross-community bridge._
- **Are the 489 inferred relationships involving `self` (e.g. with `.color()` and `.range()`) actually correct?**
  _`self` has 489 INFERRED edges - model-reasoned connections that need verification._
- **Are the 30 inferred relationships involving `Utils` (e.g. with `.check_nonce()` and `.check_requires()`) actually correct?**
  _`Utils` has 30 INFERRED edges - model-reasoned connections that need verification._
- **Are the 110 inferred relationships involving `El` (e.g. with `.academy()` and `.academy_hero()`) actually correct?**
  _`El` has 110 INFERRED edges - model-reasoned connections that need verification._
- **Are the 70 inferred relationships involving `ElementorControls` (e.g. with `.general_style_controls()` and `.text_style_controls()`) actually correct?**
  _`ElementorControls` has 70 INFERRED edges - model-reasoned connections that need verification._