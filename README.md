# IronFit Core

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-7.0-21759B?logo=wordpress&logoColor=white)
![License](https://img.shields.io/badge/License-GPLv2-or-blue)
![Version](https://img.shields.io/badge/Version-1.0.0-informational)

A single-file procedural WordPress plugin providing custom post types, meta boxes, an admin dashboard, AJAX booking handler, and site-wide settings for the IronFit fitness theme.

## Features

- 5 custom post types: Hero Slides, Services, Testimonials, Pricing Plans, Bookings
- Meta boxes with nonce verification and field-level sanitization
- Custom admin dashboard with card grid navigation
- AJAX booking form handler with nonce validation
- Site settings (Contact, Social Links, About, Stats & Rating) stored in `wp_options`
- Admin-only CPTs — content is not publicly queryable
- `show_in_rest` enabled on content CPTs for Gutenberg compatibility

## Requirements

- WordPress 5.0+
- PHP 8.0+

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Configure settings under the **IronFit Core** menu

## Usage

### Custom Post Types

| CPT Slug              | Label         | Purpose                | Meta Fields                                                   |
| --------------------- | ------------- | ---------------------- | ------------------------------------------------------------- |
| `ironfit_hero_slide`  | Hero Slides   | Homepage hero carousel | subtitle, description, btn_text, btn_url, btn2_text, btn2_url |
| `ironfit_service`     | Services      | Service offerings grid | icon (emoji)                                                  |
| `ironfit_testimonial` | Testimonials  | Client reviews         | quote, role, rating (1-5), result                             |
| `ironfit_pricing`     | Pricing Plans | Pricing cards          | price, period, features, popular, btn_text                    |
| `ironfit_booking`     | Bookings      | Form submissions       | email, phone, goal, message, status                           |

All meta keys use the `ifc_` prefix to avoid conflicts. CPTs register under the **IronFit Core** top-level menu for automatic sub-navigation.

### Booking Form

The frontend booking form submits via `fetch()` to `admin-ajax.php`:

1. Frontend JS creates `FormData`, appends `action` and `nonce`
2. `wp_ajax_ifc_submit_booking` / `wp_ajax_nopriv_ifc_submit_booking` hooks fire
3. `check_ajax_referer()` validates the nonce
4. Validates required fields (name, email)
5. `wp_insert_post()` creates a booking CPT entry
6. `update_post_meta()` saves all fields
7. Returns a JSON success/error response

The nonce is generated via `wp_create_nonce('ifc_booking_nonce')` and localized with `wp_localize_script()`.

### Site Settings

Settings are stored in `wp_options` via `update_option()` / `get_option()`, so they persist across theme switches.

Available option keys:

- Contact: `ifc_contact_email`, `ifc_contact_phone`, `ifc_contact_location`
- Social: `ifc_social_instagram`, `ifc_social_youtube`, `ifc_social_tiktok`, `ifc_social_linkedin`
- About: `ifc_about_text1`, `ifc_about_text2`, `ifc_about_coach_name`, `ifc_about_coach_title`, `ifc_about_certs`, `ifc_about_specialties`
- Stats: `ifc_about_stat{1..3}_number`, `ifc_about_stat{1..3}_label`, `ifc_about_rating`, `ifc_about_rating_source`

## Security

- All form submissions verified with WordPress nonces
- `current_user_can('edit_post', $post_id)` check on every meta save
- `DOING_AUTOSAVE` check prevents meta overwrite during autosave
- AJAX handler uses `check_ajax_referer()` — calls `wp_die()` on failure
- All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`
- All input sanitized before database storage

## File Structure

```
ironfit-core/
├── ironfit-core.php           # All plugin logic (single file)
├── assets/
│   └── css/
│       └── admin-dashboard.css # Dashboard card grid styles
└── README.md
```

## Changelog

### 1.0.0

- Initial release
- 5 CPTs: Hero Slides, Services, Testimonials, Pricing, Bookings
- Admin dashboard with card grid
- AJAX booking form handler
- Site settings (Contact + About)
- Meta boxes with nonce verification and sanitization

## License

GPL v2 or later
