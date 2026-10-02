=== Obydullah Ironfit Core ===
Contributors: obydullah
Tags: fitness, gym, booking, testimonials, custom post type
Text Domain: obydullah-ironfit-core
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Content management for a fitness or personal training site: hero slides, services, testimonials, pricing plans, and booking requests.

== Description ==

Obydullah Ironfit Core holds the dynamic content for a fitness or personal training website. You manage hero slides, services, testimonials, and pricing plans in wp-admin, and booking requests arrive from your front-end form into a dedicated admin list.

The content lives in the plugin, not in a theme, so it survives a theme change. Settings are stored in `wp_options` rather than `theme_mod()` for the same reason.

= What it does =

* **Hero slides** — a private post type for the homepage carousel. Each slide carries a subtitle or badge, description, and two buttons with their own text and URL.
* **Services** — a private post type for your service offerings. Title and description come from the post itself, plus an icon field that accepts an emoji or a Font Awesome class.
* **Testimonials** — a private post type for client reviews. Each entry has a quote, a role or result label, a 1 to 5 star rating, and a result caption.
* **Pricing plans** — a private post type for pricing cards, with price, billing period, a newline-separated feature list, a custom button label, and a flag to highlight one plan as the popular choice.
* **Bookings** — front-end form submissions stored in a custom database table. The admin list supports search by name or email, per-status filters with counts, pagination, inline status changes, and deletion.
* **Admin dashboard** — a single card grid under one top-level menu, linking to each content type and to settings.
* **Site settings** — contact details, social links, two about paragraphs, coach name and title, certifications, specialties, three stat blocks, and an aggregate rating. All stored in `wp_options`.

= Design choices =

* **Content outranks posts.** All four content types are private custom post types, so they inherit WordPress revisions, media handling, scheduling, and the block editor. Featured images work as expected.
* **Bookings use a custom table.** Form submissions are high-volume write-once data with no need for revisions or taxonomy, so they go to a `{prefix}oifc_bookings` table instead of the posts table. This keeps the posts table clean and booking queries fast as submissions accumulate.
* **No external services.** The plugin loads no remote fonts, scripts, or styles, and makes no outbound HTTP requests. It sends no usage or telemetry data.
* **Prefixed everything.** Functions, hooks, meta keys, options, nonces, and the custom table all use the `oifc_` prefix, so the plugin cannot collide with your theme or another plugin.
* **Admin-only content.** Every post type registers with `public => false`. Nothing added through this plugin is publicly queryable, and the booking table is never exposed to the front end.

== Installation ==

1. Go to **Plugins → Add New**, search for "Obydullah Ironfit Core", or upload the plugin zip.
2. Click **Install Now**, then **Activate**.
3. Activation creates the `{prefix}oifc_bookings` database table.
4. Open **Obydullah Ironfit Core** and add content using the dashboard cards.
5. Set your contact and about details under **Obydullah Ironfit Core → Site Settings**.

= Using the booking form =

The booking form is a `fetch()` request to `admin-ajax.php`. Your front-end form needs to append two fields:

* `action` with the value `oifc_submit_booking`
* `nonce` from `wp_create_nonce( 'oifc_booking_nonce' )`, passed to your script with `wp_localize_script()`

The handler accepts `name`, `email`, `phone`, `goal`, and `message`. Name and email are required; everything else is optional. The response is a JSON object — on success the message is in the `data` property of the payload, and on failure the payload is an error with a `data` string explaining what went wrong.

= For theme developers =

Content is read with the standard WordPress APIs. Query the post types directly, or read meta with the prefixed keys:

* `oifc_hero_slide` — `oifc_slide_subtitle`, `oifc_slide_description`, `oifc_slide_btn_text`, `oifc_slide_btn_url`, `oifc_slide_btn2_text`, `oifc_slide_btn2_url`
* `oifc_service` — `oifc_service_icon`
* `oifc_testimonial` — `oifc_testimonial_quote`, `oifc_testimonial_role`, `oifc_testimonial_rating`, `oifc_testimonial_result`
* `oifc_pricing` — `oifc_pricing_price`, `oifc_pricing_period`, `oifc_pricing_features`, `oifc_pricing_popular`, `oifc_pricing_btn_text`

Settings are read with `get_option()` using keys such as `oifc_contact_email`, `oifc_social_instagram`, `oifc_about_coach_name`, and `oifc_about_stat1_number`.

Bookings are read from the table with `$wpdb`. Always use `prepare()`:

`$rows = $wpdb->get_results( $wpdb->prepare( "SELECT id, name, email, status FROM {$table} WHERE status = %s ORDER BY created_at DESC LIMIT %d", $status, 20 ) );`

== Frequently Asked Questions ==

= Where is my data stored? =

In your own WordPress database on your own hosting. Content types use custom post types and their meta, settings use `wp_options`, and bookings use a custom table prefixed with your WordPress table prefix. Nothing is sent anywhere else.

= Why are bookings in a table instead of a post type? =

Form submissions are write-once records that never need revisions, categories, or scheduled publishing. Storing them in a custom table keeps the posts table from filling with thousands of low-value entries and keeps booking queries fast regardless of how many submissions you have.

= Can I import the bookings I already have? =

Not from inside the plugin. The table is created fresh on activation. If you are moving from a site where bookings were stored as a custom post type, you will need to copy those rows into the new table yourself, or re-enter them.

= Does the plugin send anything to an external service? =

No. It loads no remote fonts, scripts, or styles, makes no outbound HTTP requests, and collects no usage or telemetry data.

= Will deleting the plugin delete my data? =

No. Removing the plugin leaves your content, settings, and the bookings table in place. Delete the `{prefix}oifc_bookings` table and the `oifc_` options yourself if you want a clean slate.

= Do I need a particular theme? =

No. The plugin is theme-independent and works with any theme. It provides no front-end output on its own — your theme renders the content.

= Can I translate the admin labels? =

Yes. All strings use the `obydullah-ironfit-core` text domain. A translation file in the plugin's `languages` folder is picked up automatically.

== Changelog ==

= 1.0.0 =

* Initial release.
* Four private custom post types: Hero Slides, Services, Testimonials, and Pricing Plans.
* Bookings stored in a custom `{prefix}oifc_bookings` table, created with `dbDelta()` on activation.
* Bookings admin list with search, per-status filters with counts, pagination, inline status updates, and deletion.
* AJAX booking form handler with nonce verification and field-level sanitization.
* Admin dashboard with a card grid linking to each content type and to settings.
* Site settings for contact details, social links, about content, stats, and rating, stored in `wp_options`.
* Meta boxes with nonce verification, capability checks, and field-level sanitization.
