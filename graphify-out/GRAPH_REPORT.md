# Graph Report - .  (2026-10-05)

## Corpus Check
- 451 files · ~354,242 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 3039 nodes · 5068 edges · 310 communities (147 shown, 163 thin omitted)
- Extraction: 79% EXTRACTED · 21% INFERRED · 0% AMBIGUOUS · INFERRED: 1085 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Lenz Utils & Components
- Studiare Admin JS Shell
- Studiare Builder Thumbs
- Studiare Picture & Media
- Lenz Page Templates
- Script Enqueueing
- Lenz Booking Models
- Studiare Preset Blocks
- Lenz AJAX Handlers
- Lenz Elementor Controls
- Studiare Product Cards
- Studiare Builder JS
- Studiare Course Presets
- Lenz TGM Plugins
- Lenz Reservation Data
- Lenz Reservation Settings
- Studiare Bottom Nav JS
- Studiare Template Library
- Studiare Post Categories
- Studiare Bottom Nav Items
- Studiare Header Footer Swap
- Studiare Sanitizer
- Studiare Builder Admin JS
- Studiare Admin Fields & Views
- Studiare Contact Form
- Studiare Builder Module
- Studiare Blog Breadcrumb
- Studiare Builder Parts
- Studiare Footer Presets
- Studiare Support Channels
- Community 30
- Community 31
- Community 32
- Community 33
- Community 34
- Community 35
- Community 36
- Community 37
- Community 38
- Community 39
- Community 40
- Community 42
- Community 43
- Community 44
- Community 45
- Community 47
- Community 48
- Community 49
- Community 50
- Community 51
- Community 52
- Community 53
- Community 54
- Community 55
- Community 56
- Community 57
- Community 58
- Community 59
- Community 60
- Community 61
- Community 62
- Community 63
- Community 64
- Community 65
- Community 66
- Community 67
- Community 68
- Community 69
- Community 70
- Community 72
- Community 73
- Community 74
- Community 75
- Community 76
- Community 77
- Community 78
- Community 79
- Community 80
- Community 81
- Community 82
- Community 83
- Community 84
- Community 85
- Community 86
- Community 87
- Community 88
- Community 89
- Community 90
- Community 91
- Community 92
- Community 93
- Community 94
- Community 95
- Community 96
- Community 97
- Community 98
- Community 99
- Community 100
- Community 101
- Community 102
- Community 103
- Community 104
- Community 105
- Community 106
- Community 107
- Community 108
- Community 109
- Community 110
- Community 111
- Community 112
- Community 114
- Community 115
- Community 116
- Community 117
- Community 118
- Community 119
- Community 120
- Community 121
- Community 122
- Community 123
- Community 124
- Community 125
- Community 126
- Community 127
- Community 128
- Community 129
- Community 130
- Community 131
- Community 132
- Community 133
- Community 134
- Community 135
- Community 136
- Community 137
- Community 138
- Community 139
- Community 140
- Community 141
- Community 142
- Community 143
- Community 144
- Community 145
- Community 146
- Community 147
- Community 148
- Community 149
- Community 150
- Community 151
- Community 152
- Community 153
- Community 154
- Community 155
- Community 156
- Community 157
- Community 158
- Community 159
- Community 160
- Community 161
- Community 162
- Community 163
- Community 164
- Community 165
- Community 166
- Community 167
- Community 168
- Community 169
- Community 170
- Community 171
- Community 172
- Community 173
- Community 174
- Community 175
- Community 176
- Community 177
- Community 178
- Community 179
- Community 180
- Community 181
- Community 182
- Community 183
- Community 184
- Community 185
- Community 186
- Community 187
- Community 188
- Community 189
- Community 190
- Community 191
- Community 192
- Community 194
- Community 195
- Community 196
- Community 197
- Community 200
- Community 201
- Community 203
- Community 205
- Community 206
- Community 207
- Community 208
- Community 209
- Community 210
- Community 211
- Community 212
- Community 213
- Community 214

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

## Communities (310 total, 163 thin omitted)

### Community 0 - "Lenz Utils & Components"
Cohesion: 0.02
Nodes (5): Heading, Wishlist, Update, Utils, Elementor

### Community 1 - "Studiare Admin JS Shell"
Cohesion: 0.07
Nodes (50): bindControls(), confirmDialog(), createStore(), escapeHtml(), getPath(), initColorFields(), initModuleSwitches(), paintRange() (+42 more)

### Community 3 - "Studiare Picture & Media"
Cohesion: 0.06
Nodes (4): Elementor\Utils, Picture, Post_Image, Slider

### Community 5 - "Script Enqueueing"
Cohesion: 0.04
Nodes (6): AdminScripts, File, PublicScripts, Scripts, Contact_Inbox, WP_Post

### Community 6 - "Lenz Booking Models"
Cohesion: 0.09
Nodes (12): MJ\WPORM\Blueprint, MJ\WPORM\Model, Booking, BookingMeta, ReserveDayTimes, ReserveLocations, ReservePackages, ReservePlans (+4 more)

### Community 8 - "Lenz AJAX Handlers"
Cohesion: 0.06
Nodes (7): AJAX, GetAvailableTimes, GetDayTimes, IconPicker, MiniCartSetQTY, Notices, ToggleProductWishlist

### Community 10 - "Studiare Product Cards"
Cohesion: 0.08
Nodes (4): Cards, WC_Product, WP_Post, Base

### Community 11 - "Studiare Builder JS"
Cohesion: 0.12
Nodes (32): bindVariationImages(), boot(), buildChoices(), closeAll(), embedUrl(), escape(), fetchResults(), formOf() (+24 more)

### Community 12 - "Studiare Course Presets"
Cohesion: 0.09
Nodes (3): Course, El, Header

### Community 14 - "Lenz Reservation Data"
Cohesion: 0.07
Nodes (5): GetReservationData, Button, Archive, Sanitizers, lenz_wishlist_item_endpoint_content()

### Community 15 - "Lenz Reservation Settings"
Cohesion: 0.07
Nodes (5): Locations, Main, Subjects, SuggestLocations, ReservationSettings

### Community 16 - "Studiare Bottom Nav JS"
Cohesion: 0.12
Nodes (21): bind(), bindSheet(), cart(), close(), dark(), enableDragToClose(), focusables(), init() (+13 more)

### Community 17 - "Studiare Template Library"
Cohesion: 0.09
Nodes (3): Library, Module, Catalog

### Community 21 - "Studiare Sanitizer"
Cohesion: 0.09
Nodes (3): Sanitizer, Library_Ajax, Module

### Community 22 - "Studiare Builder Admin JS"
Cohesion: 0.14
Nodes (21): applyResult(), cardHtml(), closeTermPicker(), createPageDialog(), designCard(), keywordOption(), newTemplateDialog(), openTermPicker() (+13 more)

### Community 24 - "Studiare Contact Form"
Cohesion: 0.09
Nodes (3): Contact_Form, Page_Base, Timeline

### Community 30 - "Community 30"
Cohesion: 0.11
Nodes (6): WC, WCAttributeFields, lenz_wc_add_to_cart_fragments(), lenz_wc_order_get_formatted_billing_address(), lenz_wc_order_get_formatted_shipping_address(), lenz_woocommerce_widget_shopping_cart_subtotal()

### Community 31 - "Community 31"
Cohesion: 0.11
Nodes (3): Course_Curriculum, WC_Product, Product_Tabs

### Community 32 - "Community 32"
Cohesion: 0.10
Nodes (8): Booking, Options, lenz_wc_cart_empty(), lenz_wc_pay_order_text(), lenz_wc_product_header_actions(), lenz_wc_return_to_shop_text(), lenz_wc_single_add_to_cart_text(), lenz_woocommerce_widget_shopping_cart_proceed_to_checkout()

### Community 33 - "Community 33"
Cohesion: 0.10
Nodes (5): lenz_requests_item_endpoint_content(), lenz_wc_body_classes(), lenz_wc_my_account_before_nav(), lenz_wc_order_customer_address_icon(), lenz_woocommerce_account_menu_items()

### Community 36 - "Community 36"
Cohesion: 0.10
Nodes (4): Module, Product_Meta_Box, Module, Template_Post_Type

### Community 37 - "Community 37"
Cohesion: 0.21
Nodes (19): channelGlyph(), channelUrl(), handleUrl(), initChannels(), initPreview(), international(), move(), queueRender() (+11 more)

### Community 39 - "Community 39"
Cohesion: 0.13
Nodes (4): Context, WC_Product, WP_Post, WC_Product

### Community 40 - "Community 40"
Cohesion: 0.12
Nodes (3): MenuItems, Plans, AdminUI

### Community 50 - "Community 50"
Cohesion: 0.12
Nodes (3): lenz_include_shortcodes(), lenz_init(), lenz_register_widgets()

### Community 53 - "Community 53"
Cohesion: 0.15
Nodes (3): Fixes, Module, Schema

### Community 66 - "Community 66"
Cohesion: 0.15
Nodes (3): ArchiveFilter, PostToc, WP_Widget

### Community 67 - "Community 67"
Cohesion: 0.29
Nodes (13): announce(), copyText(), hookElementor(), initCategoryMenu(), initCategoryRow(), initProgress(), initScope(), initShare() (+5 more)

### Community 68 - "Community 68"
Cohesion: 0.14
Nodes (3): WP_Post, Search_Query, WP_Post

### Community 79 - "Community 79"
Cohesion: 0.27
Nodes (9): fadeInVideoData(), fadeOutVideoData(), pauseOtherVideos(), playActions(), resetFadeOutTimer(), setResponsiveClass(), setVolumeIcon(), togglePlayPause() (+1 more)

### Community 92 - "Community 92"
Cohesion: 0.32
Nodes (11): formatCount(), hookElementor(), initCount(), initCountdown(), initFilter(), initNewsletter(), initRail(), initScope() (+3 more)

### Community 105 - "Community 105"
Cohesion: 0.25
Nodes (3): Category_Parts, WP_Term, WP_Post

### Community 109 - "Community 109"
Cohesion: 0.20
Nodes (4): lenz_comment_fields(), lenz_comment_star_column(), lenz_comment_stars(), lenz_save_comment_stars()

### Community 121 - "Community 121"
Cohesion: 0.25
Nodes (4): lenz_add_post_views(), lenz_get_post_views(), lenz_post_thumbnail(), lenz_woocommerce_template_loop_product_thumbnail()

### Community 136 - "Community 136"
Cohesion: 0.25
Nodes (5): load_tgm_plugin_activation(), TGM_Bulk_Installer, TGM_Bulk_Installer_Skin, tgmpa(), lenz_register_required_plugins()

### Community 157 - "Community 157"
Cohesion: 0.52
Nodes (6): digits(), finish(), finishForm(), normalize(), otpField(), write()

### Community 177 - "Community 177"
Cohesion: 0.33
Nodes (3): lenz_breadcrumb(), lenz_modify_post_per_page(), lenz_sort_posts()

### Community 178 - "Community 178"
Cohesion: 0.33
Nodes (3): lenz_admin_enqueue(), lenz_wc_product_footer(), lenz_woocommerce_pagination_icons()

### Community 182 - "Community 182"
Cohesion: 0.60
Nodes (5): closeMenu(), greetingDismissed(), hideGreeting(), initGreeting(), initMenu()

### Community 184 - "Community 184"
Cohesion: 0.60
Nodes (3): createTimeSlotElements(), getTimeRanges(), setDayTimes()

### Community 185 - "Community 185"
Cohesion: 0.70
Nodes (4): createTimeSlotElements(), getTimeRanges(), initCalendar(), setDayTimes()

### Community 190 - "Community 190"
Cohesion: 0.70
Nodes (4): hookElementor(), initContact(), initMap(), initScope()

### Community 191 - "Community 191"
Cohesion: 0.70
Nodes (4): afterLoad(), hookElementor(), init(), initScope()

## Knowledge Gaps
- **2 isolated node(s):** `TGM_Bulk_Installer`, `TGM_Bulk_Installer_Skin`
  These have ≤1 connection - possible missing edges or undocumented components.
- **163 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Utils` connect `Lenz Utils & Components` to `Studiare Picture & Media`, `Lenz Page Templates`, `Script Enqueueing`, `Lenz Booking Models`, `Community 135`, `Lenz AJAX Handlers`, `Lenz Elementor Controls`, `Lenz Reservation Data`, `Lenz Reservation Settings`, `Community 154`, `Community 155`, `Community 156`, `Community 30`, `Community 32`, `Community 33`, `Community 40`, `Community 41`, `Community 177`, `Community 50`, `Community 179`, `Community 180`, `Community 51`, `Community 54`, `Community 181`, `Community 56`, `Community 186`, `Community 187`, `Community 188`, `Community 61`, `Community 62`, `Community 63`, `Community 64`, `Community 65`, `Community 189`, `Community 66`, `Community 70`, `Community 80`, `Community 81`, `Community 82`, `Community 83`, `Community 209`, `Community 85`, `Community 86`, `Community 87`, `Community 88`, `Community 90`, `Community 91`, `Community 100`, `Community 101`, `Community 108`, `Community 109`, `Community 110`?**
  _High betweenness centrality (0.265) - this node is a cross-community bridge._
- **Why does `Base` connect `Studiare Product Cards` to `Community 128`, `Community 129`, `Community 130`, `Studiare Picture & Media`, `Community 132`, `Community 138`, `Community 139`, `Community 140`, `Community 143`, `Community 144`, `Community 146`, `Community 148`, `Community 150`, `Studiare Contact Form`, `Community 153`, `Studiare Builder Parts`, `Community 31`, `Community 160`, `Community 166`, `Community 39`, `Community 167`, `Community 168`, `Community 169`, `Community 170`, `Community 44`, `Community 171`, `Community 46`, `Community 172`, `Community 173`, `Community 49`, `Community 178`, `Community 174`, `Community 175`, `Community 183`, `Community 59`, `Community 197`, `Community 75`, `Community 78`, `Community 95`, `Community 97`, `Community 105`, `Community 106`, `Community 108`, `Community 113`, `Community 114`, `Community 115`, `Community 116`, `Community 123`?**
  _High betweenness centrality (0.135) - this node is a cross-community bridge._
- **Why does `ElementorControls` connect `Lenz Elementor Controls` to `Lenz Utils & Components`, `Lenz Reservation Data`, `Community 54`, `Community 55`, `Community 56`, `Community 61`, `Community 62`, `Community 63`, `Community 64`, `Community 65`, `Community 70`, `Community 80`, `Community 81`, `Community 82`, `Community 83`, `Community 84`, `Community 85`, `Community 86`, `Community 87`, `Community 88`, `Community 89`, `Community 100`, `Community 101`, `Community 108`, `Community 110`?**
  _High betweenness centrality (0.050) - this node is a cross-community bridge._
- **Are the 423 inferred relationships involving `self` (e.g. with `.dismiss()` and `.get()`) actually correct?**
  _`self` has 423 INFERRED edges - model-reasoned connections that need verification._
- **Are the 30 inferred relationships involving `Utils` (e.g. with `.check_nonce()` and `.check_requires()`) actually correct?**
  _`Utils` has 30 INFERRED edges - model-reasoned connections that need verification._
- **Are the 110 inferred relationships involving `El` (e.g. with `.academy()` and `.academy_hero()`) actually correct?**
  _`El` has 110 INFERRED edges - model-reasoned connections that need verification._
- **Are the 70 inferred relationships involving `ElementorControls` (e.g. with `.general_style_controls()` and `.text_style_controls()`) actually correct?**
  _`ElementorControls` has 70 INFERRED edges - model-reasoned connections that need verification._