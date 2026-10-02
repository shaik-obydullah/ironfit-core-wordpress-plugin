<?php
/**
 * Plugin Name: Obydullah Ironfit Core
 * Plugin URI: https://obydullah.com/project/ironfit-core-wordpress-plugin
 * Description: Core functionality for the IronFit theme
 * Version: 1.0.0
 * Author: Shaik Obydullah
 * Author URI: https://obydullah.com
 * Text Domain: obydullah-ironfit-core
 * Requires at least: 6.2
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * ================================================================
 *                         INDEX
 * ================================================================
 * 1. Dashboard Menu
 * 2. Hero Slider CPT + Meta Boxes
 * 3. Services CPT + Meta Boxes
 * 4. Testimonials CPT + Meta Boxes
 * 5. Pricing Plans CPT + Meta Boxes
 * 6. Bookings Custom Table + Admin List + AJAX Handler
 * 7. Site Settings (Contact + About)
 * ================================================================
 */

/* ======================================================
   1. Security & Constants
====================================================== */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The plugin uses short array syntax, null coalescing, and first-class
 * callable invocation, all of which require PHP 8.0 or later.
 */
if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
    add_action( 'admin_notices', 'oifc_php_version_notice' );
    return;
}

function oifc_php_version_notice() {
    printf(
        '<div class="notice notice-error"><p>%s</p></div>',
        esc_html__(
            'Obydullah Ironfit Core requires PHP 8.0 or later. Please update your PHP version or contact your host.',
            'obydullah-ironfit-core'
        )
    );
}

define( 'OIFC_VERSION', '1.0.0' );
define( 'OIFC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OIFC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/* ======================================================
   1a. Bookings Table Schema
   ====================================================== */

function oifc_bookings_table() {
    global $wpdb;
    return $wpdb->prefix . 'oifc_bookings';
}

function oifc_create_tables() {
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table   = oifc_bookings_table();
    $collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        name varchar(191) NOT NULL DEFAULT '',
        email varchar(191) NOT NULL DEFAULT '',
        mobile varchar(50) NOT NULL DEFAULT '',
        goal varchar(50) NOT NULL DEFAULT '',
        message text NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'new',
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY status (status),
        KEY created_at (created_at)
    ) {$collate};";

    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'oifc_create_tables' );

function oifc_add_admin_menu() {
    add_menu_page(
        'Obydullah Ironfit Core',
        'Obydullah Ironfit Core',
        'manage_options',
        'obydullah-ironfit-core',
        'oifc_core_page',
        'dashicons-heart',
        65
    );
}
add_action( 'admin_menu', 'oifc_add_admin_menu', 9 );

function oifc_enqueue_dashboard_assets( $hook ) {
    if ( 'toplevel_page_obydullah-ironfit-core' === $hook ) {
        wp_enqueue_style( 'oifc-dashboard-css', OIFC_PLUGIN_URL . 'assets/css/oifc-admin.css', [], OIFC_VERSION );
    }
}
add_action( 'admin_enqueue_scripts', 'oifc_enqueue_dashboard_assets' );

function oifc_core_page() {
    $sections = [
        'hero_slides' => [
            'title' => __( 'Hero Slides', 'obydullah-ironfit-core' ),
            'url'   => admin_url( 'edit.php?post_type=oifc_hero_slide' ),
            'icon'  => 'dashicons-slides',
        ],
        'services' => [
            'title' => __( 'Services', 'obydullah-ironfit-core' ),
            'url'   => admin_url( 'edit.php?post_type=oifc_service' ),
            'icon'  => 'dashicons-admin-site',
        ],
        'testimonials' => [
            'title' => __( 'Testimonials', 'obydullah-ironfit-core' ),
            'url'   => admin_url( 'edit.php?post_type=oifc_testimonial' ),
            'icon'  => 'dashicons-star-filled',
        ],
        'pricing' => [
            'title' => __( 'Pricing Plans', 'obydullah-ironfit-core' ),
            'url'   => admin_url( 'edit.php?post_type=oifc_pricing' ),
            'icon'  => 'dashicons-money-alt',
        ],
        'bookings' => [
            'title' => __( 'Bookings', 'obydullah-ironfit-core' ),
            'url'   => admin_url( 'admin.php?page=oifc-bookings' ),
            'icon'  => 'dashicons-calendar-alt',
        ],
        'settings' => [
            'title' => __( 'Site Settings', 'obydullah-ironfit-core' ),
            'url'   => admin_url( 'admin.php?page=oifc-settings' ),
            'icon'  => 'dashicons-admin-generic',
        ],
    ];
    ?>
<div class="wrap oifc-dashboard">
    <h1><?php esc_html_e( 'Obydullah Ironfit Core', 'obydullah-ironfit-core' ); ?></h1>
    <p class="oifc-dashboard-description">
        <?php esc_html_e( 'Welcome to the Obydullah Ironfit Core plugin. Use the links below to manage your fitness content.', 'obydullah-ironfit-core' ); ?>
    </p>

    <div class="oifc-dashboard-grid">
        <?php foreach ( $sections as $section ) : ?>
        <div class="oifc-dashboard-card">
            <div class="dashicons <?php echo esc_attr( $section['icon'] ); ?>"></div>
            <h2><?php echo esc_html( $section['title'] ); ?></h2>
            <a href="<?php echo esc_url( $section['url'] ); ?>"
                class="button button-primary"><?php esc_html_e( 'Manage', 'obydullah-ironfit-core' ); ?></a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
}

/* ======================================================
   2. Hero Slider CPT + Meta Boxes
====================================================== */

function oifc_register_hero_slide_cpt() {
    register_post_type( 'oifc_hero_slide', [
        'labels' => [
            'name'          => __( 'Hero Slides', 'obydullah-ironfit-core' ),
            'singular_name' => __( 'Hero Slide', 'obydullah-ironfit-core' ),
            'add_new_item'  => __( 'Add New Hero Slide', 'obydullah-ironfit-core' ),
            'edit_item'     => __( 'Edit Hero Slide', 'obydullah-ironfit-core' ),
            'all_items'     => __( 'Hero Slides', 'obydullah-ironfit-core' ),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'obydullah-ironfit-core',
        'menu_icon'     => 'dashicons-slides',
        'supports'      => [ 'title', 'thumbnail', 'page-attributes' ],
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => false,
    ] );
}
add_action( 'init', 'oifc_register_hero_slide_cpt' );

function oifc_add_hero_slide_meta_box() {
    add_meta_box(
        'oifc_hero_slide_meta',
        __( 'Hero Slide Settings', 'obydullah-ironfit-core' ),
        'oifc_render_hero_slide_meta_box',
        'oifc_hero_slide',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'oifc_add_hero_slide_meta_box' );

function oifc_render_hero_slide_meta_box( $post ) {
    $subtitle    = get_post_meta( $post->ID, 'oifc_slide_subtitle', true );
    $description = get_post_meta( $post->ID, 'oifc_slide_description', true );
    $btn_text    = get_post_meta( $post->ID, 'oifc_slide_btn_text', true );
    $btn_url     = get_post_meta( $post->ID, 'oifc_slide_btn_url', true );
    $btn2_text   = get_post_meta( $post->ID, 'oifc_slide_btn2_text', true );
    $btn2_url    = get_post_meta( $post->ID, 'oifc_slide_btn2_url', true );
    wp_nonce_field( 'oifc_save_hero_slide_meta', 'oifc_hero_slide_nonce' );
    ?>
<table class="form-table">
    <tr>
        <th><label
                for="oifc_slide_subtitle"><?php esc_html_e( 'Subtitle / Badge', 'obydullah-ironfit-core' ); ?></label>
        </th>
        <td><input type="text" id="oifc_slide_subtitle" name="oifc_slide_subtitle"
                value="<?php echo esc_attr( $subtitle ); ?>" class="widefat"
                placeholder="<?php esc_attr_e( 'e.g., TRANSFORM YOUR BODY', 'obydullah-ironfit-core' ); ?>"></td>
    </tr>
    <tr>
        <th><label for="oifc_slide_description"><?php esc_html_e( 'Description', 'obydullah-ironfit-core' ); ?></label>
        </th>
        <td><textarea id="oifc_slide_description" name="oifc_slide_description" rows="3" class="large-text"
                placeholder="<?php esc_attr_e( 'Short description for this slide...', 'obydullah-ironfit-core' ); ?>"><?php echo esc_textarea( $description ); ?></textarea>
        </td>
    </tr>
    <tr>
        <th><label
                for="oifc_slide_btn_text"><?php esc_html_e( 'Primary Button Text', 'obydullah-ironfit-core' ); ?></label>
        </th>
        <td><input type="text" id="oifc_slide_btn_text" name="oifc_slide_btn_text"
                value="<?php echo esc_attr( $btn_text ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( 'Start Today', 'obydullah-ironfit-core' ); ?>"></td>
    </tr>
    <tr>
        <th><label
                for="oifc_slide_btn_url"><?php esc_html_e( 'Primary Button URL', 'obydullah-ironfit-core' ); ?></label>
        </th>
        <td><input type="url" id="oifc_slide_btn_url" name="oifc_slide_btn_url"
                value="<?php echo esc_url( $btn_url ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( '#contact', 'obydullah-ironfit-core' ); ?>"></td>
    </tr>
    <tr>
        <th><label
                for="oifc_slide_btn2_text"><?php esc_html_e( 'Secondary Button Text', 'obydullah-ironfit-core' ); ?></label>
        </th>
        <td><input type="text" id="oifc_slide_btn2_text" name="oifc_slide_btn2_text"
                value="<?php echo esc_attr( $btn2_text ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( 'Learn More', 'obydullah-ironfit-core' ); ?>"></td>
    </tr>
    <tr>
        <th><label
                for="oifc_slide_btn2_url"><?php esc_html_e( 'Secondary Button URL', 'obydullah-ironfit-core' ); ?></label>
        </th>
        <td><input type="url" id="oifc_slide_btn2_url" name="oifc_slide_btn2_url"
                value="<?php echo esc_url( $btn2_url ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( '#about', 'obydullah-ironfit-core' ); ?>"></td>
    </tr>
</table>
<p class="description">
    <?php esc_html_e( 'Upload a featured image to use as the slide background.', 'obydullah-ironfit-core' ); ?></p>
<?php
}

function oifc_save_hero_slide_meta( $post_id ) {
    // Security & Execution Guards
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oifc_hero_slide_nonce'] ?? '' ) ), 'oifc_save_hero_slide_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( 'oifc_hero_slide' !== get_post_type( $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Sanitize & Save
    $fields = [
        'oifc_slide_subtitle'    => 'sanitize_text_field',
        'oifc_slide_description' => 'sanitize_textarea_field',
        'oifc_slide_btn_text'    => 'sanitize_text_field',
        'oifc_slide_btn_url'     => 'esc_url_raw',
        'oifc_slide_btn2_text'   => 'sanitize_text_field',
        'oifc_slide_btn2_url'    => 'esc_url_raw',
    ];
    foreach ( $fields as $key => $sanitize ) {
        if ( isset( $_POST[ $key ] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $key is limited to the fixed $fields map above, and its value goes straight into that entry's sanitizer callback.
            update_post_meta( $post_id, $key, $sanitize( wp_unslash( $_POST[ $key ] ) ) );
        }
    }
}
add_action( 'save_post_oifc_hero_slide', 'oifc_save_hero_slide_meta' );

/* ======================================================
   3. Services CPT + Meta Boxes
====================================================== */

function oifc_register_service_cpt() {
    register_post_type( 'oifc_service', [
        'labels' => [
            'name'          => __( 'Services', 'obydullah-ironfit-core' ),
            'singular_name' => __( 'Service', 'obydullah-ironfit-core' ),
            'add_new_item'  => __( 'Add New Service', 'obydullah-ironfit-core' ),
            'edit_item'     => __( 'Edit Service', 'obydullah-ironfit-core' ),
            'all_items'     => __( 'Services', 'obydullah-ironfit-core' ),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'obydullah-ironfit-core',
        'menu_icon'     => 'dashicons-admin-site',
        'supports'      => [ 'title', 'editor', 'thumbnail' ],
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => false,
    ] );
}
add_action( 'init', 'oifc_register_service_cpt' );

function oifc_add_service_meta_box() {
    add_meta_box(
        'oifc_service_meta',
        __( 'Service Details', 'obydullah-ironfit-core' ),
        'oifc_render_service_meta_box',
        'oifc_service',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'oifc_add_service_meta_box' );

function oifc_render_service_meta_box( $post ) {
    $icon = get_post_meta( $post->ID, 'oifc_service_icon', true );
    wp_nonce_field( 'oifc_save_service_meta', 'oifc_service_nonce' );
    ?>
<table class="form-table">
    <tr>
        <th><label
                for="oifc_service_icon"><?php esc_html_e( 'Icon (emoji or Font Awesome class)', 'obydullah-ironfit-core' ); ?></label>
        </th>
        <td><input type="text" id="oifc_service_icon" name="oifc_service_icon" value="<?php echo esc_attr( $icon ); ?>"
                class="regular-text"
                placeholder="<?php esc_attr_e( 'e.g., 🏋️ or fa-dumbbell', 'obydullah-ironfit-core' ); ?>"></td>
    </tr>
</table>
<p class="description">
    <?php esc_html_e( 'Use an emoji or a Font Awesome class. The service title and description come from the post title and content.', 'obydullah-ironfit-core' ); ?>
</p>
<?php
}

function oifc_save_service_meta( $post_id ) {
    // Security & Execution Guards
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oifc_service_nonce'] ?? '' ) ), 'oifc_save_service_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( 'oifc_service' !== get_post_type( $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Sanitize & Save
    if ( isset( $_POST['oifc_service_icon'] ) ) {
        update_post_meta( $post_id, 'oifc_service_icon', sanitize_text_field( wp_unslash( $_POST['oifc_service_icon'] ) ) );
    }
}
add_action( 'save_post_oifc_service', 'oifc_save_service_meta' );

/* ======================================================
   4. Testimonials CPT + Meta Boxes
====================================================== */

function oifc_register_testimonial_cpt() {
    register_post_type( 'oifc_testimonial', [
        'labels' => [
            'name'          => __( 'Testimonials', 'obydullah-ironfit-core' ),
            'singular_name' => __( 'Testimonial', 'obydullah-ironfit-core' ),
            'add_new_item'  => __( 'Add New Testimonial', 'obydullah-ironfit-core' ),
            'edit_item'     => __( 'Edit Testimonial', 'obydullah-ironfit-core' ),
            'all_items'     => __( 'Testimonials', 'obydullah-ironfit-core' ),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'obydullah-ironfit-core',
        'menu_icon'     => 'dashicons-star-filled',
        'supports'      => [ 'title', 'editor', 'thumbnail' ],
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => false,
    ] );
}
add_action( 'init', 'oifc_register_testimonial_cpt' );

function oifc_add_testimonial_meta_boxes() {
    add_meta_box( 'oifc_testimonial_quote', __( 'Quote', 'obydullah-ironfit-core' ), 'oifc_testimonial_quote_callback', 'oifc_testimonial', 'normal', 'high' );
    add_meta_box( 'oifc_testimonial_details', __( 'Details', 'obydullah-ironfit-core' ), 'oifc_testimonial_details_callback', 'oifc_testimonial', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'oifc_add_testimonial_meta_boxes' );

function oifc_testimonial_quote_callback( $post ) {
    wp_nonce_field( 'oifc_save_testimonial_meta', 'oifc_testimonial_nonce' );
    $quote = get_post_meta( $post->ID, 'oifc_testimonial_quote', true );
    echo '<textarea name="oifc_testimonial_quote" rows="4" class="large-text">' . esc_textarea( $quote ) . '</textarea>';
}

function oifc_testimonial_details_callback( $post ) {
    $role   = get_post_meta( $post->ID, 'oifc_testimonial_role', true );
    $rating = get_post_meta( $post->ID, 'oifc_testimonial_rating', true );
    $result = get_post_meta( $post->ID, 'oifc_testimonial_result', true );
    ?>
<p>
    <label><strong><?php esc_html_e( 'Role / Title', 'obydullah-ironfit-core' ); ?></strong></label><br>
    <input type="text" name="oifc_testimonial_role" value="<?php echo esc_attr( $role ); ?>" class="widefat"
        placeholder="<?php esc_attr_e( 'e.g., Lost 18 lbs', 'obydullah-ironfit-core' ); ?>">
</p>
<p>
    <label><strong><?php esc_html_e( 'Rating (1-5)', 'obydullah-ironfit-core' ); ?></strong></label><br>
    <input type="number" name="oifc_testimonial_rating" value="<?php echo esc_attr( $rating ?: 5 ); ?>" min="1" max="5"
        class="small-text">
</p>
<p>
    <label><strong><?php esc_html_e( 'Result Label', 'obydullah-ironfit-core' ); ?></strong></label><br>
    <input type="text" name="oifc_testimonial_result" value="<?php echo esc_attr( $result ); ?>" class="widefat"
        placeholder="<?php esc_attr_e( 'e.g., 4 months', 'obydullah-ironfit-core' ); ?>">
</p>
<?php
}

function oifc_save_testimonial_meta( $post_id ) {
    // Security & Execution Guards
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oifc_testimonial_nonce'] ?? '' ) ), 'oifc_save_testimonial_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( 'oifc_testimonial' !== get_post_type( $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Sanitize & Save
    $fields = [
        'oifc_testimonial_role'   => 'sanitize_text_field',
        'oifc_testimonial_quote'  => 'sanitize_textarea_field',
        'oifc_testimonial_rating' => 'intval',
        'oifc_testimonial_result' => 'sanitize_text_field',
    ];
    foreach ( $fields as $key => $sanitize ) {
        if ( isset( $_POST[ $key ] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $key is limited to the fixed $fields map above, and its value goes straight into that entry's sanitizer callback.
            update_post_meta( $post_id, $key, $sanitize( wp_unslash( $_POST[ $key ] ) ) );
        }
    }
}
add_action( 'save_post_oifc_testimonial', 'oifc_save_testimonial_meta' );

/* ======================================================
   5. Pricing Plans CPT + Meta Boxes
====================================================== */

function oifc_register_pricing_cpt() {
    register_post_type( 'oifc_pricing', [
        'labels' => [
            'name'          => __( 'Pricing Plans', 'obydullah-ironfit-core' ),
            'singular_name' => __( 'Pricing Plan', 'obydullah-ironfit-core' ),
            'add_new_item'  => __( 'Add New Pricing Plan', 'obydullah-ironfit-core' ),
            'edit_item'     => __( 'Edit Pricing Plan', 'obydullah-ironfit-core' ),
            'all_items'     => __( 'Pricing Plans', 'obydullah-ironfit-core' ),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'obydullah-ironfit-core',
        'menu_icon'     => 'dashicons-money-alt',
        'supports'      => [ 'title', 'editor', 'thumbnail' ],
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => false,
    ] );
}
add_action( 'init', 'oifc_register_pricing_cpt' );

function oifc_add_pricing_meta_box() {
    add_meta_box( 'oifc_pricing_meta', __( 'Pricing Details', 'obydullah-ironfit-core' ), 'oifc_render_pricing_meta_box', 'oifc_pricing', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'oifc_add_pricing_meta_box' );

function oifc_render_pricing_meta_box( $post ) {
    $price    = get_post_meta( $post->ID, 'oifc_pricing_price', true );
    $period   = get_post_meta( $post->ID, 'oifc_pricing_period', true );
    $features = get_post_meta( $post->ID, 'oifc_pricing_features', true );
    $popular  = get_post_meta( $post->ID, 'oifc_pricing_popular', true );
    $btn_text = get_post_meta( $post->ID, 'oifc_pricing_btn_text', true );
    wp_nonce_field( 'oifc_save_pricing_meta', 'oifc_pricing_nonce' );
    ?>
<table class="form-table">
    <tr>
        <th><label for="oifc_pricing_price"><?php esc_html_e( 'Price', 'obydullah-ironfit-core' ); ?></label></th>
        <td><input type="text" id="oifc_pricing_price" name="oifc_pricing_price"
                value="<?php echo esc_attr( $price ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( '$79', 'obydullah-ironfit-core' ); ?>"></td>
    </tr>
    <tr>
        <th><label for="oifc_pricing_period"><?php esc_html_e( 'Period', 'obydullah-ironfit-core' ); ?></label></th>
        <td><input type="text" id="oifc_pricing_period" name="oifc_pricing_period"
                value="<?php echo esc_attr( $period ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( '/mo', 'obydullah-ironfit-core' ); ?>"></td>
    </tr>
    <tr>
        <th><label
                for="oifc_pricing_features"><?php esc_html_e( 'Features (one per line)', 'obydullah-ironfit-core' ); ?></label>
        </th>
        <td><textarea id="oifc_pricing_features" name="oifc_pricing_features" rows="6" class="large-text"
                placeholder="<?php esc_attr_e( "2x 1-on-1 sessions / week\nBasic nutrition guide\nProgress tracking", 'obydullah-ironfit-core' ); ?>"><?php echo esc_textarea( $features ); ?></textarea>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e( 'Mark as Popular', 'obydullah-ironfit-core' ); ?></th>
        <td><label><input type="checkbox" name="oifc_pricing_popular" value="1" <?php checked( $popular, 1 ); ?>>
                <?php esc_html_e( 'Highlight this plan', 'obydullah-ironfit-core' ); ?></label></td>
    </tr>
    <tr>
        <th><label for="oifc_pricing_btn_text"><?php esc_html_e( 'Button Text', 'obydullah-ironfit-core' ); ?></label>
        </th>
        <td><input type="text" id="oifc_pricing_btn_text" name="oifc_pricing_btn_text"
                value="<?php echo esc_attr( $btn_text ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( 'Get Started', 'obydullah-ironfit-core' ); ?>"></td>
    </tr>
</table>
<?php
}

function oifc_save_pricing_meta( $post_id ) {
    // Security & Execution Guards
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oifc_pricing_nonce'] ?? '' ) ), 'oifc_save_pricing_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( 'oifc_pricing' !== get_post_type( $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Sanitize & Save
    $fields = [
        'oifc_pricing_price'    => 'sanitize_text_field',
        'oifc_pricing_period'   => 'sanitize_text_field',
        'oifc_pricing_features' => 'sanitize_textarea_field',
        'oifc_pricing_btn_text' => 'sanitize_text_field',
    ];
    foreach ( $fields as $key => $sanitize ) {
        if ( isset( $_POST[ $key ] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $key is limited to the fixed $fields map above, and its value goes straight into that entry's sanitizer callback.
            update_post_meta( $post_id, $key, $sanitize( wp_unslash( $_POST[ $key ] ) ) );
        }
    }

    $popular = isset( $_POST['oifc_pricing_popular'] ) ? 1 : 0;
    update_post_meta( $post_id, 'oifc_pricing_popular', $popular );
}
add_action( 'save_post_oifc_pricing', 'oifc_save_pricing_meta' );

/* ======================================================
   6. Bookings Table + Admin List + AJAX Handler
   ====================================================== */

/*
 * The bookings data layer talks to its own table, so $wpdb cannot be avoided
 * here. Every read is wrapped in $wpdb->prepare() with %i/%s/%d placeholders
 * and every write goes through insert()/update()/delete() with a format list.
 * The results are admin-only and change on every submission, so there is
 * nothing worth caching between requests.
 */
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
function oifc_get_bookings( $args = [] ) {
    global $wpdb;

    $table = oifc_bookings_table();

    $per_page = absint( $args['per_page'] ?? 20 );
    $offset   = absint( $args['offset'] ?? 0 );
    $status   = sanitize_key( $args['status'] ?? '' );
    $search   = sanitize_text_field( $args['search'] ?? '' );

    /*
     * An empty search turns into '%%', which matches every row because name
     * and email are NOT NULL, so only the status filter changes the shape of
     * the query. Keeping both statements as literals means the table name
     * travels through the %i identifier placeholder and every value through
     * a %s or %d placeholder.
     */
    $like = '%' . $wpdb->esc_like( $search ) . '%';

    if ( $status ) {
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT id, name, email, mobile, goal, message, status, created_at
                 FROM %i
                 WHERE status = %s AND ( name LIKE %s OR email LIKE %s )
                 ORDER BY created_at DESC
                 LIMIT %d OFFSET %d',
                $table,
                $status,
                $like,
                $like,
                $per_page,
                $offset
            )
        );
    }

    return $wpdb->get_results(
        $wpdb->prepare(
            'SELECT id, name, email, mobile, goal, message, status, created_at
             FROM %i
             WHERE ( name LIKE %s OR email LIKE %s )
             ORDER BY created_at DESC
             LIMIT %d OFFSET %d',
            $table,
            $like,
            $like,
            $per_page,
            $offset
        )
    );
}

function oifc_get_booking( $booking_id ) {
    global $wpdb;

    $table = oifc_bookings_table();

    return $wpdb->get_row(
        $wpdb->prepare(
            'SELECT id, name, email, mobile, goal, message, status, created_at
             FROM %i
             WHERE id = %d',
            $table,
            absint( $booking_id )
        )
    );
}

function oifc_count_bookings( $status = '' ) {
    global $wpdb;

    $table  = oifc_bookings_table();
    $status = sanitize_key( $status );

    if ( $status ) {
        return (int) $wpdb->get_var(
            $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE status = %s', $table, $status )
        );
    }

    return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $table ) );
}

function oifc_insert_booking( $name, $email, $mobile, $goal, $message ) {
    global $wpdb;

    $result = $wpdb->insert(
        oifc_bookings_table(),
        [
            'name'       => $name,
            'email'      => $email,
            'mobile'     => $mobile,
            'goal'       => $goal,
            'message'    => $message,
            'status'     => 'new',
            'created_at' => current_time('mysql'),
        ],
        [ '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
    );

    return $result ? (int) $wpdb->insert_id : 0;
}

/**
 * Editable booking columns and their sanitizers.
 * Anything not in this map is ignored, so a caller can never
 * write to `id` or `created_at` by passing them through.
 */
function oifc_booking_editable_fields() {
    return [
        'name'    => 'sanitize_text_field',
        'email'   => 'sanitize_email',
        'mobile'  => 'sanitize_text_field',
        'goal'    => 'sanitize_key',
        'message' => 'sanitize_textarea_field',
        'status'  => 'sanitize_key',
    ];
}

function oifc_update_booking( $booking_id, $fields ) {
    global $wpdb;

    $booking_id = absint( $booking_id );
    if ( ! $booking_id ) {
        return false;
    }

    $schema  = oifc_booking_editable_fields();
    $data    = [];
    $formats = [];

    foreach ( $fields as $column => $value ) {
        if ( ! isset( $schema[ $column ] ) ) {
            continue;
        }

        $data[ $column ] = $schema[ $column ]( $value );
        $formats[]       = '%s';
    }

    if ( ! $data ) {
        return false;
    }

    if ( isset( $data['status'] ) && ! array_key_exists( $data['status'], oifc_booking_statuses() ) ) {
        return false;
    }

    $result = $wpdb->update(
        oifc_bookings_table(),
        $data,
        [ 'id' => $booking_id ],
        $formats,
        [ '%d' ]
    );

    return false !== $result;
}

function oifc_delete_booking( $booking_id ) {
    global $wpdb;

    $booking_id = absint( $booking_id );
    if ( ! $booking_id ) {
        return false;
    }

    $result = $wpdb->delete(
        oifc_bookings_table(),
        [ 'id' => $booking_id ],
        [ '%d' ]
    );

    return false !== $result;
}
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

function oifc_booking_goals() {
    return [
        ''             => __( '— Select —', 'obydullah-ironfit-core' ),
        'weight-loss'  => __( 'Weight Loss', 'obydullah-ironfit-core' ),
        'muscle-gain'  => __( 'Muscle Gain', 'obydullah-ironfit-core' ),
        'strength'     => __( 'Strength & Performance', 'obydullah-ironfit-core' ),
        'mobility'     => __( 'Mobility & Recovery', 'obydullah-ironfit-core' ),
        'general'      => __( 'General Fitness', 'obydullah-ironfit-core' ),
    ];
}

function oifc_booking_statuses() {
    return [
        'new'       => __( 'New', 'obydullah-ironfit-core' ),
        'contacted' => __( 'Contacted', 'obydullah-ironfit-core' ),
        'booked'    => __( 'Booked', 'obydullah-ironfit-core' ),
        'closed'    => __( 'Closed', 'obydullah-ironfit-core' ),
    ];
}

function oifc_booking_status_labels() {
    return [
        'new'       => 'oifc-status-new',
        'contacted' => 'oifc-status-contacted',
        'booked'    => 'oifc-status-booked',
        'closed'    => 'oifc-status-closed',
    ];
}

/* --- Admin List Page --- */

function oifc_bookings_menu() {
    add_submenu_page(
        'obydullah-ironfit-core',
        __( 'Bookings', 'obydullah-ironfit-core' ),
        __( 'Bookings', 'obydullah-ironfit-core' ),
        'manage_options',
        'oifc-bookings',
        'oifc_render_bookings_page'
    );
}
add_action( 'admin_menu', 'oifc_bookings_menu' );

function oifc_handle_booking_admin_action() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to manage bookings.', 'obydullah-ironfit-core' ) );
    }

    // Verify nonce - sanitize the input first
    $nonce = sanitize_text_field(wp_unslash($_POST['oifc_bookings_nonce'] ?? ''));
    if ( ! wp_verify_nonce( $nonce, 'oifc_save_booking_admin' ) ) {
        wp_die( esc_html__( 'Security check failed.', 'obydullah-ironfit-core' ) );
    }

    $action = sanitize_key( wp_unslash( $_POST['booking_action'] ?? '' ) );
    $id     = absint( $_POST['booking_id'] ?? 0 );

    if ( ! $id ) {
        wp_die( esc_html__( 'Invalid booking.', 'obydullah-ironfit-core' ) );
    }

    if ( 'update_status' === $action ) {
        $new_status = sanitize_key( wp_unslash( $_POST['booking_status'] ?? '' ) );
        oifc_update_booking( $id, [ 'status' => $new_status ] );
    } elseif ( 'delete' === $action ) {
        oifc_delete_booking( $id );
    }

    wp_safe_redirect( admin_url( 'admin.php?page=oifc-bookings' ) );
    exit;
}
add_action( 'admin_post_oifc_booking_action', 'oifc_handle_booking_admin_action' );

function oifc_render_bookings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    /*
     * These are read-only list filters coming from GET links and the search
     * box. Nothing is written, so no nonce is involved; the capability check
     * above is what guards this screen.
     */
    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    $status   = sanitize_key( wp_unslash( $_GET['status'] ?? '' ) );
    $search   = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
    $paged    = max( 1, absint( $_GET['paged'] ?? 1 ) );
    // phpcs:enable WordPress.Security.NonceVerification.Recommended
    $per_page = 20;

    $total   = oifc_count_bookings( $status );
    $offset  = ( $paged - 1 ) * $per_page;
    $total_pages = (int) ceil( $total / $per_page );
    $bookings = oifc_get_bookings( [
        'per_page' => $per_page,
        'offset'   => $offset,
        'status'   => $status,
        'search'   => $search,
    ] );

    $statuses = oifc_booking_statuses();
    $goals    = oifc_booking_goals();
    $labels   = oifc_booking_status_labels();
    ?>
<div class="wrap oifc-dashboard">
    <h1><?php esc_html_e( 'Bookings', 'obydullah-ironfit-core' ); ?></h1>

    <form method="get">
        <input type="hidden" name="page" value="oifc-bookings">
        <p class="search-box">
            <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>"
                placeholder="<?php esc_attr_e( 'Search name or email', 'obydullah-ironfit-core' ); ?>">
            <?php submit_button( __( 'Search Bookings', 'obydullah-ironfit-core' ), '', 'submit', false ); ?>
        </p>
    </form>

    <ul class="subsubsub">
        <li>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=oifc-bookings' ) ); ?>"
                class="<?php echo $status ? '' : 'current'; ?>">
                <?php
                /* translators: %s: total number of bookings. */
                echo esc_html( sprintf( __( 'All (%s)', 'obydullah-ironfit-core' ), number_format_i18n( $total ) ) );
                ?>
            </a>
        </li>
        <?php foreach ( $statuses as $key => $label ) : ?>
        <li>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=oifc-bookings&status=' . $key ) ); ?>"
                class="<?php echo ( $status === $key ) ? 'current' : ''; ?>">
                <?php
                    /* translators: 1: booking status label, 2: number of bookings with that status. */
                    echo esc_html( sprintf( __( '%1$s (%2$s)', 'obydullah-ironfit-core' ), $label, number_format_i18n( oifc_count_bookings( $key ) ) ) );
                    ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Name', 'obydullah-ironfit-core' ); ?></th>
                <th><?php esc_html_e( 'Email', 'obydullah-ironfit-core' ); ?></th>
                <th><?php esc_html_e( 'Phone', 'obydullah-ironfit-core' ); ?></th>
                <th><?php esc_html_e( 'Goal', 'obydullah-ironfit-core' ); ?></th>
                <th><?php esc_html_e( 'Message', 'obydullah-ironfit-core' ); ?></th>
                <th><?php esc_html_e( 'Status', 'obydullah-ironfit-core' ); ?></th>
                <th><?php esc_html_e( 'Submitted', 'obydullah-ironfit-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! $bookings ) : ?>
            <tr>
                <td colspan="7"><?php esc_html_e( 'No bookings found.', 'obydullah-ironfit-core' ); ?></td>
            </tr>
            <?php endif; ?>

            <?php foreach ( $bookings as $booking ) : ?>
            <?php $class = $labels[ $booking->status ] ?? ''; ?>
            <tr>
                <td><strong><?php echo esc_html( $booking->name ); ?></strong></td>
                <td><a
                        href="mailto:<?php echo esc_attr( $booking->email ); ?>"><?php echo esc_html( $booking->email ); ?></a>
                </td>
                <td><?php echo esc_html( $booking->mobile ); ?></td>
                <td><?php echo esc_html( $goals[ $booking->goal ] ?? $booking->goal ); ?></td>
                <td><?php echo esc_html( wp_trim_words( $booking->message, 12 ) ); ?></td>
                <td><span
                        class="oifc-status <?php echo esc_attr( $class ); ?>"><?php echo esc_html( $statuses[ $booking->status ] ?? $booking->status ); ?></span>
                </td>
                <td><?php echo esc_html( mysql2date( 'M j, Y g:i A', $booking->created_at ) ); ?></td>
            </tr>
            <tr>
                <td colspan="7">
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                        class="oifc-booking-row-actions">
                        <?php wp_nonce_field( 'oifc_save_booking_admin', 'oifc_bookings_nonce' ); ?>
                        <input type="hidden" name="action" value="oifc_booking_action">
                        <input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>">
                        <input type="hidden" name="booking_action" value="update_status">
                        <label class="screen-reader-text" for="status-<?php echo esc_attr( $booking->id ); ?>">
                            <?php esc_html_e( 'Status', 'obydullah-ironfit-core' ); ?>
                        </label>
                        <select name="booking_status" id="status-<?php echo esc_attr( $booking->id ); ?>">
                            <?php foreach ( $statuses as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>"
                                <?php selected( $booking->status, $key ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php submit_button( __( 'Update', 'obydullah-ironfit-core' ), 'primary', 'submit', false ); ?>
                    </form>

                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                        class="oifc-booking-row-actions">
                        <?php wp_nonce_field( 'oifc_save_booking_admin', 'oifc_bookings_nonce' ); ?>
                        <input type="hidden" name="action" value="oifc_booking_action">
                        <input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>">
                        <input type="hidden" name="booking_action" value="delete">
                        <?php submit_button( __( 'Delete', 'obydullah-ironfit-core' ), 'delete', 'submit', false ); ?>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ( $total_pages > 1 ) : ?>
    <div class="tablenav">
        <div class="tablenav-pages">
            <span class="button disabled"><?php echo esc_html( sprintf( '%d / %d', $paged, $total_pages ) ); ?></span>
            <?php if ( $paged > 1 ) : ?>
            <a class="prev-page button"
                href="<?php echo esc_url( admin_url( 'admin.php?page=oifc-bookings&paged=' . ( $paged - 1 ) . '&status=' . rawurlencode( $status ) ) ); ?>">&laquo;</a>
            <?php endif; ?>
            <?php if ( $paged < $total_pages ) : ?>
            <a class="next-page button"
                href="<?php echo esc_url( admin_url( 'admin.php?page=oifc-bookings&paged=' . ( $paged + 1 ) . '&status=' . rawurlencode( $status ) ) ); ?>">&raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php
}

/* --- AJAX Handler --- */

function oifc_handle_booking_form() {
    // Verify nonce - sanitize the input first
    $nonce = sanitize_text_field(wp_unslash($_POST['nonce'] ?? ''));
    if ( ! wp_verify_nonce( $nonce, 'oifc_booking_nonce' ) ) {
        wp_send_json_error( __( 'Security verification failed', 'obydullah-ironfit-core' ) );
    }

    $name    = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $email   = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $mobile  = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $goal    = sanitize_text_field(wp_unslash($_POST['goal'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));

    if ( empty( $name ) || empty( $email ) ) {
        wp_send_json_error( __( 'Name and email are required.', 'obydullah-ironfit-core' ) );
    }

    $booking_id = oifc_insert_booking( $name, $email, $mobile, $goal, $message );

    if ( ! $booking_id ) {
        wp_send_json_error( __( 'Failed to submit. Please try again.', 'obydullah-ironfit-core' ) );
    }

    wp_send_json_success( __( 'Thank you! We\'ll get back to you within 24 hours.', 'obydullah-ironfit-core' ) );
}
add_action( 'wp_ajax_oifc_submit_booking', 'oifc_handle_booking_form' );
add_action( 'wp_ajax_nopriv_oifc_submit_booking', 'oifc_handle_booking_form' );

/* ======================================================
   6a. Front-End Booking Form (assets + shortcode)
======================================================= */

/**
 * Registers the booking form script and hands it the AJAX config.
 *
 * Hooked to init rather than wp_enqueue_scripts so the handle exists before
 * any plugin, theme, widget or block can call the shortcode — the shortcode
 * then only has to enqueue. The script is registered, never enqueued here, so
 * a page without a booking form pays nothing. Themes that print their own
 * markup instead of using the shortcode can call
 * wp_enqueue_script( 'oifc-booking-form' ) to get the same behaviour.
 */
function oifc_register_booking_form_assets() {
    wp_register_script(
        'oifc-booking-form',
        OIFC_PLUGIN_URL . 'assets/js/oifc-booking-form.js',
        [],
        OIFC_VERSION,
        true
    );

    wp_localize_script(
        'oifc-booking-form',
        'oifcBooking',
        [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'oifc_booking_nonce' ),
            'i18n'    => [
                'submitting'   => __( 'Submitting…', 'obydullah-ironfit-core' ),
                'genericError' => __( 'Something went wrong. Please try again.', 'obydullah-ironfit-core' ),
                'networkError' => __( 'Could not reach the server. Please try again.', 'obydullah-ironfit-core' ),
            ],
        ]
    );
}
add_action( 'init', 'oifc_register_booking_form_assets' );

/**
 * Renders the booking form.
 *
 * The field names match what oifc_handle_booking_form() reads, so the markup
 * doubles as the contract for anyone writing their own form. Output is
 * deliberately unstyled beyond what a theme provides; the class names below
 * are the intended styling hooks.
 *
 * @param array $atts Shortcode attributes.
 */
function oifc_booking_form_shortcode( $atts ) {
    $atts = shortcode_atts(
        [
            'title'  => '',
            'button' => __( 'Book a Session', 'obydullah-ironfit-core' ),
        ],
        $atts,
        'oifc_booking_form'
    );

    // Registering happens on init, so the handle is always ready here no matter
    // where the shortcode is rendered. The script prints in the footer.
    wp_enqueue_script( 'oifc-booking-form' );

    ob_start();
    ?>
    <div class="oifc-booking">
        <?php if ( $atts['title'] ) : ?>
            <h2 class="oifc-booking-form__title"><?php echo esc_html( $atts['title'] ); ?></h2>
        <?php endif; ?>

        <?php // The oifc-booking-form class is what oifc-booking-form.js binds to. ?>
        <form class="oifc-booking-form" method="post"
            action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
            <input type="hidden" name="action" value="oifc_submit_booking">
            <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'oifc_booking_nonce' ) ); ?>">

            <p>
                <label for="oifc-booking-name"><?php esc_html_e( 'Name', 'obydullah-ironfit-core' ); ?> <span aria-hidden="true">*</span></label>
                <input type="text" id="oifc-booking-name" name="name" required>
            </p>
            <p>
                <label for="oifc-booking-email"><?php esc_html_e( 'Email', 'obydullah-ironfit-core' ); ?> <span aria-hidden="true">*</span></label>
                <input type="email" id="oifc-booking-email" name="email" required>
            </p>
            <p>
                <label for="oifc-booking-phone"><?php esc_html_e( 'Phone', 'obydullah-ironfit-core' ); ?></label>
                <input type="tel" id="oifc-booking-phone" name="phone">
            </p>
            <p>
                <label for="oifc-booking-goal"><?php esc_html_e( 'Goal', 'obydullah-ironfit-core' ); ?></label>
                <select id="oifc-booking-goal" name="goal">
                    <?php foreach ( oifc_booking_goals() as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="oifc-booking-message"><?php esc_html_e( 'Message', 'obydullah-ironfit-core' ); ?></label>
                <textarea id="oifc-booking-message" name="message" rows="5"></textarea>
            </p>
            <p>
                <button type="submit"><?php echo esc_html( $atts['button'] ); ?></button>
            </p>
            <p class="oifc-booking-form__status" role="status" aria-live="polite"></p>
        </form>

        <noscript>
            <p class="oifc-booking-form__noscript">
                <?php
                esc_html_e( 'This form needs JavaScript enabled.', 'obydullah-ironfit-core' );

                $contact_email = get_option( 'oifc_contact_email', '' );

                if ( $contact_email ) {
                    ?>
                    <a href="<?php echo esc_url( 'mailto:' . $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a>
                    <?php
                } else {
                    esc_html_e( 'Please contact us directly and we will get back to you.', 'obydullah-ironfit-core' );
                }
                ?>
            </p>
        </noscript>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'oifc_booking_form', 'oifc_booking_form_shortcode' );

/* ======================================================
   7. Site Settings (Contact + About)
====================================================== */

function oifc_settings_page() {
    add_submenu_page(
        'obydullah-ironfit-core',
        __( 'Site Settings', 'obydullah-ironfit-core' ),
        __( 'Site Settings', 'obydullah-ironfit-core' ),
        'manage_options',
        'oifc-settings',
        'oifc_render_settings_page'
    );
}
add_action( 'admin_menu', 'oifc_settings_page' );

function oifc_render_settings_page() {
    if ( isset( $_POST['oifc_settings_submit'] ) && check_admin_referer( 'oifc_save_settings' ) ) {
        $text_fields = [
            'oifc_contact_email', 'oifc_contact_phone', 'oifc_contact_location',
            'oifc_social_instagram', 'oifc_social_youtube', 'oifc_social_tiktok', 'oifc_social_linkedin',
            'oifc_about_text1', 'oifc_about_text2', 'oifc_about_coach_name', 'oifc_about_coach_title',
            'oifc_about_stat1_number', 'oifc_about_stat1_label',
            'oifc_about_stat2_number', 'oifc_about_stat2_label',
            'oifc_about_stat3_number', 'oifc_about_stat3_label',
            'oifc_about_rating', 'oifc_about_rating_source',
        ];
        foreach ( $text_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_option( $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }
        $textarea_fields = [ 'oifc_about_certs', 'oifc_about_specialties' ];
        foreach ( $textarea_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_option( $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'obydullah-ironfit-core' ) . '</p></div>';
    }

    /*
     * Returns the raw stored value; every call site escapes it for its own
     * context with esc_attr() or esc_textarea().
     */
    $setting = function ( $key, $default = '' ) {
        return (string) get_option( $key, $default );
    };
    ?>
<div class="wrap">
    <h1><?php esc_html_e( 'Site Settings', 'obydullah-ironfit-core' ); ?></h1>
    <form method="post">
        <?php wp_nonce_field( 'oifc_save_settings' ); ?>

        <h2 class="title"><?php esc_html_e( 'Contact Information', 'obydullah-ironfit-core' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><label
                        for="oifc_contact_email"><?php esc_html_e( 'Email Address', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="email" id="oifc_contact_email" name="oifc_contact_email"
                        value="<?php echo esc_attr( $setting( 'oifc_contact_email', 'alex@ironfit.coach' ) ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label
                        for="oifc_contact_phone"><?php esc_html_e( 'Phone Number', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="text" id="oifc_contact_phone" name="oifc_contact_phone"
                        value="<?php echo esc_attr( $setting( 'oifc_contact_phone', '+1 (555) 123-4567' ) ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label
                        for="oifc_contact_location"><?php esc_html_e( 'Location', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="text" id="oifc_contact_location" name="oifc_contact_location"
                        value="<?php echo esc_attr( $setting( 'oifc_contact_location', 'Online & In-Person (NYC)' ) ); ?>"
                        class="regular-text"></td>
            </tr>
        </table>

        <h2 class="title"><?php esc_html_e( 'Social Links', 'obydullah-ironfit-core' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><label
                        for="oifc_social_instagram"><?php esc_html_e( 'Instagram URL', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="url" id="oifc_social_instagram" name="oifc_social_instagram"
                        value="<?php echo esc_attr( $setting( 'oifc_social_instagram', '#' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="oifc_social_youtube"><?php esc_html_e( 'YouTube URL', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="url" id="oifc_social_youtube" name="oifc_social_youtube"
                        value="<?php echo esc_attr( $setting( 'oifc_social_youtube', '#' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="oifc_social_tiktok"><?php esc_html_e( 'TikTok URL', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="url" id="oifc_social_tiktok" name="oifc_social_tiktok"
                        value="<?php echo esc_attr( $setting( 'oifc_social_tiktok', '#' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="oifc_social_linkedin"><?php esc_html_e( 'LinkedIn URL', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="url" id="oifc_social_linkedin" name="oifc_social_linkedin"
                        value="<?php echo esc_attr( $setting( 'oifc_social_linkedin', '#' ) ); ?>" class="regular-text"></td>
            </tr>
        </table>

        <h2 class="title"><?php esc_html_e( 'About Section', 'obydullah-ironfit-core' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><label
                        for="oifc_about_text1"><?php esc_html_e( 'About Paragraph 1', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><textarea id="oifc_about_text1" name="oifc_about_text1" rows="3"
                        class="large-text"><?php echo esc_textarea( $setting( 'oifc_about_text1', "I'm Alex — a certified personal trainer with 8+ years of experience helping everyday people achieve extraordinary transformations. My approach blends science-backed programming with real-world accountability." ) ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label
                        for="oifc_about_text2"><?php esc_html_e( 'About Paragraph 2', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><textarea id="oifc_about_text2" name="oifc_about_text2" rows="3"
                        class="large-text"><?php echo esc_textarea( $setting( 'oifc_about_text2', "Whether you're a beginner or a seasoned athlete, I meet you where you are and take you where you want to go. No judgment. Just progress." ) ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label
                        for="oifc_about_coach_name"><?php esc_html_e( 'Coach Name', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="text" id="oifc_about_coach_name" name="oifc_about_coach_name"
                        value="<?php echo esc_attr( $setting( 'oifc_about_coach_name', 'Alex Rivera' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="oifc_about_coach_title"><?php esc_html_e( 'Coach Title', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="text" id="oifc_about_coach_title" name="oifc_about_coach_title"
                        value="<?php echo esc_attr( $setting( 'oifc_about_coach_title', 'Certified Personal Trainer' ) ); ?>"
                        class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="oifc_about_certs"><?php esc_html_e( 'Certifications (one per line)', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><textarea id="oifc_about_certs" name="oifc_about_certs" rows="4"
                        class="large-text"><?php echo esc_textarea( $setting( 'oifc_about_certs', "Certified Personal Trainer\nNSCA — CPT\nNutrition Specialist\nPrecision Nutrition Level 1\nBehavioral Coach\nMindset & habit formation\n200+ Transformations\nReal people, real results" ) ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label
                        for="oifc_about_specialties"><?php esc_html_e( 'Specialties (one per line)', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><textarea id="oifc_about_specialties" name="oifc_about_specialties" rows="3"
                        class="large-text"><?php echo esc_textarea( $setting( 'oifc_about_specialties', "Goal-Oriented\nEvery session has purpose\nData-Driven\nTrack & optimize your progress\nHolistic\nMindset + nutrition + movement\nAccountability\nI show up. You show up." ) ); ?></textarea>
                </td>
            </tr>
        </table>

        <h2 class="title"><?php esc_html_e( 'Stats & Rating', 'obydullah-ironfit-core' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><label
                        for="oifc_about_stat1_number"><?php esc_html_e( 'Stat 1 Number', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="text" id="oifc_about_stat1_number" name="oifc_about_stat1_number"
                        value="<?php echo esc_attr( $setting( 'oifc_about_stat1_number', '500+' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="oifc_about_stat1_label"><?php esc_html_e( 'Stat 1 Label', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="text" id="oifc_about_stat1_label" name="oifc_about_stat1_label"
                        value="<?php echo esc_attr( $setting( 'oifc_about_stat1_label', 'Clients' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="oifc_about_stat2_number"><?php esc_html_e( 'Stat 2 Number', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="text" id="oifc_about_stat2_number" name="oifc_about_stat2_number"
                        value="<?php echo esc_attr( $setting( 'oifc_about_stat2_number', '97%' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="oifc_about_stat2_label"><?php esc_html_e( 'Stat 2 Label', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="text" id="oifc_about_stat2_label" name="oifc_about_stat2_label"
                        value="<?php echo esc_attr( $setting( 'oifc_about_stat2_label', 'Success Rate' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="oifc_about_stat3_number"><?php esc_html_e( 'Stat 3 Number', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="text" id="oifc_about_stat3_number" name="oifc_about_stat3_number"
                        value="<?php echo esc_attr( $setting( 'oifc_about_stat3_number', '8yr' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="oifc_about_stat3_label"><?php esc_html_e( 'Stat 3 Label', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="text" id="oifc_about_stat3_label" name="oifc_about_stat3_label"
                        value="<?php echo esc_attr( $setting( 'oifc_about_stat3_label', 'Experience' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="oifc_about_rating"><?php esc_html_e( 'Rating', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="text" id="oifc_about_rating" name="oifc_about_rating"
                        value="<?php echo esc_attr( $setting( 'oifc_about_rating', '4.9 / 5.0' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="oifc_about_rating_source"><?php esc_html_e( 'Rating Source', 'obydullah-ironfit-core' ); ?></label>
                </th>
                <td><input type="text" id="oifc_about_rating_source" name="oifc_about_rating_source"
                        value="<?php echo esc_attr( $setting( 'oifc_about_rating_source', 'from 200+ reviews' ) ); ?>"
                        class="regular-text"></td>
            </tr>
        </table>

        <?php submit_button( __( 'Save Settings', 'obydullah-ironfit-core' ), 'primary', 'oifc_settings_submit' ); ?>
    </form>
</div>
<?php
}