<?php
/**
 * Plugin Name: IronFit Core
 * Description: Core functionality for IronFit theme
 * Version:     1.0.0
 * Author:      IronFit Team
 * Author URI:  https://ironfit.coach
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ironfit-core
 * Domain Path: /languages
 *
 * ================================================================
 *                         INDEX
 * ================================================================
 * 1. Dashboard Menu
 * 2. Hero Slider CPT + Meta Boxes
 * 3. Services CPT + Meta Boxes
 * 4. Testimonials CPT + Meta Boxes
 * 5. Pricing Plans CPT + Meta Boxes
 * 6. Bookings CPT + AJAX Handler
 * 6. Team Members CPT + Meta Boxes
 * ================================================================
 */

/* ======================================================
   1. Security & Constants
====================================================== */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'IFC_VERSION', '1.0.0' );
define( 'IFC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IFC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

function ifc_add_admin_menu() {
    add_menu_page(
        'IronFit Core',
        'IronFit Core',
        'manage_options',
        'ironfit-core',
        'ifc_core_page',
        'dashicons-heart',
        59
    );
}
add_action( 'admin_menu', 'ifc_add_admin_menu', 9 );

function ifc_enqueue_dashboard_assets( $hook ) {
    if ( 'toplevel_page_ironfit-core' === $hook ) {
        wp_enqueue_style( 'ifc-dashboard-css', IFC_PLUGIN_URL . 'assets/css/admin-dashboard.css', array(), IFC_VERSION );
    }
}
add_action( 'admin_enqueue_scripts', 'ifc_enqueue_dashboard_assets' );

function ifc_core_page() {
    $sections = array(
        'hero_slides' => array(
            'title' => __( 'Hero Slides', 'ironfit-core' ),
            'url'   => admin_url( 'edit.php?post_type=ironfit_hero_slide' ),
            'icon'  => 'dashicons-slides',
        ),
        'services' => array(
            'title' => __( 'Services', 'ironfit-core' ),
            'url'   => admin_url( 'edit.php?post_type=ironfit_service' ),
            'icon'  => 'dashicons-admin-site',
        ),
        'testimonials' => array(
            'title' => __( 'Testimonials', 'ironfit-core' ),
            'url'   => admin_url( 'edit.php?post_type=ironfit_testimonial' ),
            'icon'  => 'dashicons-star-filled',
        ),
        'pricing' => array(
            'title' => __( 'Pricing Plans', 'ironfit-core' ),
            'url'   => admin_url( 'edit.php?post_type=ironfit_pricing' ),
            'icon'  => 'dashicons-money-alt',
        ),
        'bookings' => array(
            'title' => __( 'Bookings', 'ironfit-core' ),
            'url'   => admin_url( 'edit.php?post_type=ironfit_booking' ),
            'icon'  => 'dashicons-calendar-alt',
        ),
        'settings' => array(
            'title' => __( 'Site Settings', 'ironfit-core' ),
            'url'   => admin_url( 'admin.php?page=ifc-settings' ),
            'icon'  => 'dashicons-admin-generic',
        ),
    );
    ?>
<div class="wrap ifc-dashboard">
    <h1><?php esc_html_e( 'IronFit Core', 'ironfit-core' ); ?></h1>
    <p class="ifc-dashboard-description">
        <?php esc_html_e( 'Welcome to the IronFit Core plugin. Use the links below to manage your fitness content.', 'ironfit-core' ); ?>
    </p>

    <div class="ifc-dashboard-grid">
        <?php foreach ( $sections as $section ) : ?>
        <div class="ifc-dashboard-card">
            <div class="dashicons <?php echo esc_attr( $section['icon'] ); ?>"></div>
            <h2><?php echo esc_html( $section['title'] ); ?></h2>
            <a href="<?php echo esc_url( $section['url'] ); ?>"
                class="button button-primary"><?php esc_html_e( 'Manage', 'ironfit-core' ); ?></a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
}

/* ======================================================
   2. Hero Slider CPT + Meta Boxes
====================================================== */

function ifc_register_hero_slide_cpt() {
    register_post_type( 'ironfit_hero_slide', array(
        'labels' => array(
            'name'          => __( 'Hero Slides', 'ironfit-core' ),
            'singular_name' => __( 'Hero Slide', 'ironfit-core' ),
            'add_new_item'  => __( 'Add New Hero Slide', 'ironfit-core' ),
            'edit_item'     => __( 'Edit Hero Slide', 'ironfit-core' ),
            'all_items'     => __( 'Hero Slides', 'ironfit-core' ),
        ),
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'ironfit-core',
        'menu_icon'     => 'dashicons-slides',
        'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => false,
    ) );
}
add_action( 'init', 'ifc_register_hero_slide_cpt' );

function ifc_add_hero_slide_meta_box() {
    add_meta_box(
        'ifc_hero_slide_meta',
        __( 'Hero Slide Settings', 'ironfit-core' ),
        'ifc_render_hero_slide_meta_box',
        'ironfit_hero_slide',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'ifc_add_hero_slide_meta_box' );

function ifc_render_hero_slide_meta_box( $post ) {
    $subtitle    = get_post_meta( $post->ID, 'ifc_slide_subtitle', true );
    $description = get_post_meta( $post->ID, 'ifc_slide_description', true );
    $btn_text    = get_post_meta( $post->ID, 'ifc_slide_btn_text', true );
    $btn_url     = get_post_meta( $post->ID, 'ifc_slide_btn_url', true );
    $btn2_text   = get_post_meta( $post->ID, 'ifc_slide_btn2_text', true );
    $btn2_url    = get_post_meta( $post->ID, 'ifc_slide_btn2_url', true );
    wp_nonce_field( 'ifc_save_hero_slide', 'ifc_hero_slide_nonce' );
    ?>
<table class="form-table">
    <tr>
        <th><label for="ifc_slide_subtitle"><?php esc_html_e( 'Subtitle / Badge', 'ironfit-core' ); ?></label></th>
        <td><input type="text" id="ifc_slide_subtitle" name="ifc_slide_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g., TRANSFORM YOUR BODY', 'ironfit-core' ); ?>"></td>
    </tr>
    <tr>
        <th><label for="ifc_slide_description"><?php esc_html_e( 'Description', 'ironfit-core' ); ?></label></th>
        <td><textarea id="ifc_slide_description" name="ifc_slide_description" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Short description for this slide...', 'ironfit-core' ); ?>"><?php echo esc_textarea( $description ); ?></textarea></td>
    </tr>
    <tr>
        <th><label for="ifc_slide_btn_text"><?php esc_html_e( 'Primary Button Text', 'ironfit-core' ); ?></label></th>
        <td><input type="text" id="ifc_slide_btn_text" name="ifc_slide_btn_text" value="<?php echo esc_attr( $btn_text ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Start Today', 'ironfit-core' ); ?>"></td>
    </tr>
    <tr>
        <th><label for="ifc_slide_btn_url"><?php esc_html_e( 'Primary Button URL', 'ironfit-core' ); ?></label></th>
        <td><input type="url" id="ifc_slide_btn_url" name="ifc_slide_btn_url" value="<?php echo esc_url( $btn_url ); ?>" class="regular-text" placeholder="<?php esc_attr_e( '#contact', 'ironfit-core' ); ?>"></td>
    </tr>
    <tr>
        <th><label for="ifc_slide_btn2_text"><?php esc_html_e( 'Secondary Button Text', 'ironfit-core' ); ?></label></th>
        <td><input type="text" id="ifc_slide_btn2_text" name="ifc_slide_btn2_text" value="<?php echo esc_attr( $btn2_text ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Learn More', 'ironfit-core' ); ?>"></td>
    </tr>
    <tr>
        <th><label for="ifc_slide_btn2_url"><?php esc_html_e( 'Secondary Button URL', 'ironfit-core' ); ?></label></th>
        <td><input type="url" id="ifc_slide_btn2_url" name="ifc_slide_btn2_url" value="<?php echo esc_url( $btn2_url ); ?>" class="regular-text" placeholder="<?php esc_attr_e( '#about', 'ironfit-core' ); ?>"></td>
    </tr>
</table>
<p class="description"><?php esc_html_e( 'Upload a featured image to use as the slide background.', 'ironfit-core' ); ?></p>
<?php
}

function ifc_save_hero_slide_meta( $post_id ) {
    if ( ! isset( $_POST['ifc_hero_slide_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ifc_hero_slide_nonce'] ) ), 'ifc_save_hero_slide' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( 'ironfit_hero_slide' !== get_post_type( $post_id ) ) return;

    $fields = array(
        'ifc_slide_subtitle'    => 'sanitize_text_field',
        'ifc_slide_description' => 'sanitize_textarea_field',
        'ifc_slide_btn_text'    => 'sanitize_text_field',
        'ifc_slide_btn_url'     => 'esc_url_raw',
        'ifc_slide_btn2_text'   => 'sanitize_text_field',
        'ifc_slide_btn2_url'    => 'esc_url_raw',
    );
    foreach ( $fields as $key => $sanitize ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, call_user_func( $sanitize, wp_unslash( $_POST[ $key ] ) ) );
        }
    }
}
add_action( 'save_post_ironfit_hero_slide', 'ifc_save_hero_slide_meta' );

/* ======================================================
   3. Services CPT + Meta Boxes
====================================================== */

function ifc_register_service_cpt() {
    register_post_type( 'ironfit_service', array(
        'labels' => array(
            'name'          => __( 'Services', 'ironfit-core' ),
            'singular_name' => __( 'Service', 'ironfit-core' ),
            'add_new_item'  => __( 'Add New Service', 'ironfit-core' ),
            'edit_item'     => __( 'Edit Service', 'ironfit-core' ),
            'all_items'     => __( 'Services', 'ironfit-core' ),
        ),
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'ironfit-core',
        'menu_icon'     => 'dashicons-admin-site',
        'supports'      => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => false,
    ) );
}
add_action( 'init', 'ifc_register_service_cpt' );

function ifc_add_service_meta_box() {
    add_meta_box(
        'ifc_service_meta',
        __( 'Service Details', 'ironfit-core' ),
        'ifc_render_service_meta_box',
        'ironfit_service',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'ifc_add_service_meta_box' );

function ifc_render_service_meta_box( $post ) {
    $icon = get_post_meta( $post->ID, 'ifc_service_icon', true );
    wp_nonce_field( 'ifc_save_service', 'ifc_service_nonce' );
    ?>
<table class="form-table">
    <tr>
        <th><label for="ifc_service_icon"><?php esc_html_e( 'Icon (emoji or Font Awesome class)', 'ironfit-core' ); ?></label></th>
        <td><input type="text" id="ifc_service_icon" name="ifc_service_icon" value="<?php echo esc_attr( $icon ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g., 🏋️ or fa-dumbbell', 'ironfit-core' ); ?>"></td>
    </tr>
</table>
<p class="description"><?php esc_html_e( 'Use an emoji or a Font Awesome class. The service title and description come from the post title and content.', 'ironfit-core' ); ?></p>
<?php
}

function ifc_save_service_meta( $post_id ) {
    if ( ! isset( $_POST['ifc_service_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ifc_service_nonce'] ) ), 'ifc_save_service' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( 'ironfit_service' !== get_post_type( $post_id ) ) return;

    if ( isset( $_POST['ifc_service_icon'] ) ) {
        update_post_meta( $post_id, 'ifc_service_icon', sanitize_text_field( wp_unslash( $_POST['ifc_service_icon'] ) ) );
    }
}
add_action( 'save_post_ironfit_service', 'ifc_save_service_meta' );

/* ======================================================
   4. Testimonials CPT + Meta Boxes
====================================================== */

function ifc_register_testimonial_cpt() {
    register_post_type( 'ironfit_testimonial', array(
        'labels' => array(
            'name'          => __( 'Testimonials', 'ironfit-core' ),
            'singular_name' => __( 'Testimonial', 'ironfit-core' ),
            'add_new_item'  => __( 'Add New Testimonial', 'ironfit-core' ),
            'edit_item'     => __( 'Edit Testimonial', 'ironfit-core' ),
            'all_items'     => __( 'Testimonials', 'ironfit-core' ),
        ),
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'ironfit-core',
        'menu_icon'     => 'dashicons-star-filled',
        'supports'      => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => false,
    ) );
}
add_action( 'init', 'ifc_register_testimonial_cpt' );

function ifc_add_testimonial_meta_boxes() {
    add_meta_box( 'ifc_testimonial_quote', __( 'Quote', 'ironfit-core' ), 'ifc_testimonial_quote_callback', 'ironfit_testimonial', 'normal', 'high' );
    add_meta_box( 'ifc_testimonial_details', __( 'Details', 'ironfit-core' ), 'ifc_testimonial_details_callback', 'ironfit_testimonial', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'ifc_add_testimonial_meta_boxes' );

function ifc_testimonial_quote_callback( $post ) {
    wp_nonce_field( 'ifc_testimonial_meta', 'ifc_testimonial_nonce' );
    $quote = get_post_meta( $post->ID, 'ifc_testimonial_quote', true );
    echo '<textarea name="ifc_testimonial_quote" rows="4" class="large-text">' . esc_textarea( $quote ) . '</textarea>';
}

function ifc_testimonial_details_callback( $post ) {
    $role   = get_post_meta( $post->ID, 'ifc_testimonial_role', true );
    $rating = get_post_meta( $post->ID, 'ifc_testimonial_rating', true );
    $result = get_post_meta( $post->ID, 'ifc_testimonial_result', true );
    ?>
<p>
    <label><strong><?php esc_html_e( 'Role / Title', 'ironfit-core' ); ?></strong></label><br>
    <input type="text" name="ifc_testimonial_role" value="<?php echo esc_attr( $role ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g., Lost 18 lbs', 'ironfit-core' ); ?>">
</p>
<p>
    <label><strong><?php esc_html_e( 'Rating (1-5)', 'ironfit-core' ); ?></strong></label><br>
    <input type="number" name="ifc_testimonial_rating" value="<?php echo esc_attr( $rating ?: 5 ); ?>" min="1" max="5" class="small-text">
</p>
<p>
    <label><strong><?php esc_html_e( 'Result Label', 'ironfit-core' ); ?></strong></label><br>
    <input type="text" name="ifc_testimonial_result" value="<?php echo esc_attr( $result ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g., 4 months', 'ironfit-core' ); ?>">
</p>
<?php
}

function ifc_save_testimonial_meta( $post_id ) {
    if ( ! isset( $_POST['ifc_testimonial_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ifc_testimonial_nonce'] ) ), 'ifc_testimonial_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( 'ironfit_testimonial' !== get_post_type( $post_id ) ) return;

    $fields = array(
        'ifc_testimonial_role'   => 'sanitize_text_field',
        'ifc_testimonial_quote'  => 'sanitize_textarea_field',
        'ifc_testimonial_rating' => 'intval',
        'ifc_testimonial_result' => 'sanitize_text_field',
    );
    foreach ( $fields as $key => $sanitize ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, call_user_func( $sanitize, wp_unslash( $_POST[ $key ] ) ) );
        }
    }
}
add_action( 'save_post_ironfit_testimonial', 'ifc_save_testimonial_meta' );

/* ======================================================
   5. Pricing Plans CPT + Meta Boxes
====================================================== */

function ifc_register_pricing_cpt() {
    register_post_type( 'ironfit_pricing', array(
        'labels' => array(
            'name'          => __( 'Pricing Plans', 'ironfit-core' ),
            'singular_name' => __( 'Pricing Plan', 'ironfit-core' ),
            'add_new_item'  => __( 'Add New Pricing Plan', 'ironfit-core' ),
            'edit_item'     => __( 'Edit Pricing Plan', 'ironfit-core' ),
            'all_items'     => __( 'Pricing Plans', 'ironfit-core' ),
        ),
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'ironfit-core',
        'menu_icon'     => 'dashicons-money-alt',
        'supports'      => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => false,
    ) );
}
add_action( 'init', 'ifc_register_pricing_cpt' );

function ifc_add_pricing_meta_box() {
    add_meta_box( 'ifc_pricing_meta', __( 'Pricing Details', 'ironfit-core' ), 'ifc_render_pricing_meta_box', 'ironfit_pricing', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'ifc_add_pricing_meta_box' );

function ifc_render_pricing_meta_box( $post ) {
    $price    = get_post_meta( $post->ID, 'ifc_pricing_price', true );
    $period   = get_post_meta( $post->ID, 'ifc_pricing_period', true );
    $features = get_post_meta( $post->ID, 'ifc_pricing_features', true );
    $popular  = get_post_meta( $post->ID, 'ifc_pricing_popular', true );
    $btn_text = get_post_meta( $post->ID, 'ifc_pricing_btn_text', true );
    wp_nonce_field( 'ifc_save_pricing', 'ifc_pricing_nonce' );
    ?>
<table class="form-table">
    <tr>
        <th><label for="ifc_pricing_price"><?php esc_html_e( 'Price', 'ironfit-core' ); ?></label></th>
        <td><input type="text" id="ifc_pricing_price" name="ifc_pricing_price" value="<?php echo esc_attr( $price ); ?>" class="regular-text" placeholder="<?php esc_attr_e( '$79', 'ironfit-core' ); ?>"></td>
    </tr>
    <tr>
        <th><label for="ifc_pricing_period"><?php esc_html_e( 'Period', 'ironfit-core' ); ?></label></th>
        <td><input type="text" id="ifc_pricing_period" name="ifc_pricing_period" value="<?php echo esc_attr( $period ); ?>" class="regular-text" placeholder="<?php esc_attr_e( '/mo', 'ironfit-core' ); ?>"></td>
    </tr>
    <tr>
        <th><label for="ifc_pricing_features"><?php esc_html_e( 'Features (one per line)', 'ironfit-core' ); ?></label></th>
        <td><textarea id="ifc_pricing_features" name="ifc_pricing_features" rows="6" class="large-text" placeholder="<?php esc_attr_e( "2x 1-on-1 sessions / week\nBasic nutrition guide\nProgress tracking", 'ironfit-core' ); ?>"><?php echo esc_textarea( $features ); ?></textarea></td>
    </tr>
    <tr>
        <th><?php esc_html_e( 'Mark as Popular', 'ironfit-core' ); ?></th>
        <td><label><input type="checkbox" name="ifc_pricing_popular" value="1" <?php checked( $popular, 1 ); ?>> <?php esc_html_e( 'Highlight this plan', 'ironfit-core' ); ?></label></td>
    </tr>
    <tr>
        <th><label for="ifc_pricing_btn_text"><?php esc_html_e( 'Button Text', 'ironfit-core' ); ?></label></th>
        <td><input type="text" id="ifc_pricing_btn_text" name="ifc_pricing_btn_text" value="<?php echo esc_attr( $btn_text ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Get Started', 'ironfit-core' ); ?>"></td>
    </tr>
</table>
<?php
}

function ifc_save_pricing_meta( $post_id ) {
    if ( ! isset( $_POST['ifc_pricing_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ifc_pricing_nonce'] ) ), 'ifc_save_pricing' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( 'ironfit_pricing' !== get_post_type( $post_id ) ) return;

    $fields = array(
        'ifc_pricing_price'    => 'sanitize_text_field',
        'ifc_pricing_period'   => 'sanitize_text_field',
        'ifc_pricing_features' => 'sanitize_textarea_field',
        'ifc_pricing_btn_text' => 'sanitize_text_field',
    );
    foreach ( $fields as $key => $sanitize ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, call_user_func( $sanitize, wp_unslash( $_POST[ $key ] ) ) );
        }
    }

    $popular = isset( $_POST['ifc_pricing_popular'] ) ? 1 : 0;
    update_post_meta( $post_id, 'ifc_pricing_popular', $popular );
}
add_action( 'save_post_ironfit_pricing', 'ifc_save_pricing_meta' );

/* ======================================================
   6. Bookings CPT + AJAX Handler
====================================================== */

function ifc_register_booking_cpt() {
    register_post_type( 'ironfit_booking', array(
        'labels' => array(
            'name'          => __( 'Bookings', 'ironfit-core' ),
            'singular_name' => __( 'Booking', 'ironfit-core' ),
            'add_new_item'  => __( 'Add New Booking', 'ironfit-core' ),
            'edit_item'     => __( 'Edit Booking', 'ironfit-core' ),
            'all_items'     => __( 'Bookings', 'ironfit-core' ),
            'view_items'    => __( 'View Bookings', 'ironfit-core' ),
        ),
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'ironfit-core',
        'menu_icon'     => 'dashicons-calendar-alt',
        'supports'      => array( 'title' ),
        'show_in_rest'  => false,
        'has_archive'   => false,
        'rewrite'       => false,
    ) );
}
add_action( 'init', 'ifc_register_booking_cpt' );

function ifc_add_booking_meta_box() {
    add_meta_box( 'ifc_booking_meta', __( 'Booking Details', 'ironfit-core' ), 'ifc_render_booking_meta_box', 'ironfit_booking', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'ifc_add_booking_meta_box' );

function ifc_render_booking_meta_box( $post ) {
    $email  = get_post_meta( $post->ID, 'ifc_booking_email', true );
    $phone  = get_post_meta( $post->ID, 'ifc_booking_phone', true );
    $goal   = get_post_meta( $post->ID, 'ifc_booking_goal', true );
    $message = get_post_meta( $post->ID, 'ifc_booking_message', true );
    $status = get_post_meta( $post->ID, 'ifc_booking_status', true ) ?: 'new';
    wp_nonce_field( 'ifc_save_booking', 'ifc_booking_nonce' );
    ?>
<table class="form-table">
    <tr>
        <th><?php esc_html_e( 'Name', 'ironfit-core' ); ?></th>
        <td><strong><?php echo esc_html( $post->post_title ); ?></strong></td>
    </tr>
    <tr>
        <th><label for="ifc_booking_email"><?php esc_html_e( 'Email', 'ironfit-core' ); ?></label></th>
        <td><input type="email" id="ifc_booking_email" name="ifc_booking_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text"></td>
    </tr>
    <tr>
        <th><label for="ifc_booking_phone"><?php esc_html_e( 'Phone', 'ironfit-core' ); ?></label></th>
        <td><input type="text" id="ifc_booking_phone" name="ifc_booking_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text"></td>
    </tr>
    <tr>
        <th><label for="ifc_booking_goal"><?php esc_html_e( 'Goal', 'ironfit-core' ); ?></label></th>
        <td>
            <select id="ifc_booking_goal" name="ifc_booking_goal">
                <option value="" <?php selected( $goal, '' ); ?>><?php esc_html_e( '— Select —', 'ironfit-core' ); ?></option>
                <option value="weight-loss" <?php selected( $goal, 'weight-loss' ); ?>><?php esc_html_e( 'Weight Loss', 'ironfit-core' ); ?></option>
                <option value="muscle-gain" <?php selected( $goal, 'muscle-gain' ); ?>><?php esc_html_e( 'Muscle Gain', 'ironfit-core' ); ?></option>
                <option value="strength" <?php selected( $goal, 'strength' ); ?>><?php esc_html_e( 'Strength & Performance', 'ironfit-core' ); ?></option>
                <option value="mobility" <?php selected( $goal, 'mobility' ); ?>><?php esc_html_e( 'Mobility & Recovery', 'ironfit-core' ); ?></option>
                <option value="general" <?php selected( $goal, 'general' ); ?>><?php esc_html_e( 'General Fitness', 'ironfit-core' ); ?></option>
            </select>
        </td>
    </tr>
    <tr>
        <th><label for="ifc_booking_message"><?php esc_html_e( 'Message', 'ironfit-core' ); ?></label></th>
        <td><textarea id="ifc_booking_message" name="ifc_booking_message" rows="4" class="large-text"><?php echo esc_textarea( $message ); ?></textarea></td>
    </tr>
    <tr>
        <th><label for="ifc_booking_status"><?php esc_html_e( 'Status', 'ironfit-core' ); ?></label></th>
        <td>
            <select id="ifc_booking_status" name="ifc_booking_status">
                <option value="new" <?php selected( $status, 'new' ); ?>><?php esc_html_e( 'New', 'ironfit-core' ); ?></option>
                <option value="contacted" <?php selected( $status, 'contacted' ); ?>><?php esc_html_e( 'Contacted', 'ironfit-core' ); ?></option>
                <option value="booked" <?php selected( $status, 'booked' ); ?>><?php esc_html_e( 'Booked', 'ironfit-core' ); ?></option>
                <option value="closed" <?php selected( $status, 'closed' ); ?>><?php esc_html_e( 'Closed', 'ironfit-core' ); ?></option>
            </select>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e( 'Submitted', 'ironfit-core' ); ?></th>
        <td><?php echo esc_html( get_the_date( 'M j, Y g:i A', $post->ID ) ); ?></td>
    </tr>
</table>
<?php
}

function ifc_save_booking_meta( $post_id ) {
    if ( ! isset( $_POST['ifc_booking_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ifc_booking_nonce'] ) ), 'ifc_save_booking' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( 'ironfit_booking' !== get_post_type( $post_id ) ) return;

    $fields = array(
        'ifc_booking_email'   => 'sanitize_email',
        'ifc_booking_phone'   => 'sanitize_text_field',
        'ifc_booking_goal'    => 'sanitize_text_field',
        'ifc_booking_message' => 'sanitize_textarea_field',
        'ifc_booking_status'  => 'sanitize_text_field',
    );
    foreach ( $fields as $key => $sanitize ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, call_user_func( $sanitize, wp_unslash( $_POST[ $key ] ) ) );
        }
    }
}
add_action( 'save_post_ironfit_booking', 'ifc_save_booking_meta' );

/* --- AJAX Handler --- */

function ifc_handle_booking_form() {
    check_ajax_referer( 'ifc_booking_nonce', 'nonce' );

    $name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
    $email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
    $goal    = isset( $_POST['goal'] ) ? sanitize_text_field( wp_unslash( $_POST['goal'] ) ) : '';
    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

    if ( empty( $name ) || empty( $email ) ) {
        wp_send_json_error( array( 'message' => __( 'Name and email are required.', 'ironfit-core' ) ) );
    }

    $post_id = wp_insert_post( array(
        'post_type'   => 'ironfit_booking',
        'post_title'  => $name,
        'post_status' => 'publish',
    ) );

    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( array( 'message' => __( 'Failed to submit. Please try again.', 'ironfit-core' ) ) );
    }

    update_post_meta( $post_id, 'ifc_booking_email', $email );
    update_post_meta( $post_id, 'ifc_booking_phone', $phone );
    update_post_meta( $post_id, 'ifc_booking_goal', $goal );
    update_post_meta( $post_id, 'ifc_booking_message', $message );
    update_post_meta( $post_id, 'ifc_booking_status', 'new' );

    wp_send_json_success( array( 'message' => __( 'Thank you! We\'ll get back to you within 24 hours.', 'ironfit-core' ) ) );
}
add_action( 'wp_ajax_ifc_submit_booking', 'ifc_handle_booking_form' );
add_action( 'wp_ajax_nopriv_ifc_submit_booking', 'ifc_handle_booking_form' );

/* ======================================================
   7. Site Settings (Contact + About)
====================================================== */

function ifc_settings_page() {
    add_submenu_page(
        'ironfit-core',
        __( 'Site Settings', 'ironfit-core' ),
        __( 'Site Settings', 'ironfit-core' ),
        'manage_options',
        'ifc-settings',
        'ifc_render_settings_page'
    );
}
add_action( 'admin_menu', 'ifc_settings_page' );

function ifc_render_settings_page() {
    if ( isset( $_POST['ifc_settings_submit'] ) && check_admin_referer( 'ifc_save_settings' ) ) {
        $text_fields = array(
            'ifc_contact_email', 'ifc_contact_phone', 'ifc_contact_location',
            'ifc_social_instagram', 'ifc_social_youtube', 'ifc_social_tiktok', 'ifc_social_linkedin',
            'ifc_about_text1', 'ifc_about_text2', 'ifc_about_coach_name', 'ifc_about_coach_title',
            'ifc_about_stat1_number', 'ifc_about_stat1_label',
            'ifc_about_stat2_number', 'ifc_about_stat2_label',
            'ifc_about_stat3_number', 'ifc_about_stat3_label',
            'ifc_about_rating', 'ifc_about_rating_source',
        );
        foreach ( $text_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_option( $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }
        $textarea_fields = array( 'ifc_about_certs', 'ifc_about_specialties' );
        foreach ( $textarea_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_option( $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'ironfit-core' ) . '</p></div>';
    }

    $v = function ( $key, $default = '' ) {
        return esc_attr( get_option( $key, $default ) );
    };
    $vt = function ( $key, $default = '' ) {
        return esc_textarea( get_option( $key, $default ) );
    };
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Site Settings', 'ironfit-core' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'ifc_save_settings' ); ?>

            <h2 class="title"><?php esc_html_e( 'Contact Information', 'ironfit-core' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label for="ifc_contact_email"><?php esc_html_e( 'Email Address', 'ironfit-core' ); ?></label></th>
                    <td><input type="email" id="ifc_contact_email" name="ifc_contact_email" value="<?php echo $v( 'ifc_contact_email', 'alex@ironfit.coach' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_contact_phone"><?php esc_html_e( 'Phone Number', 'ironfit-core' ); ?></label></th>
                    <td><input type="text" id="ifc_contact_phone" name="ifc_contact_phone" value="<?php echo $v( 'ifc_contact_phone', '+1 (555) 123-4567' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_contact_location"><?php esc_html_e( 'Location', 'ironfit-core' ); ?></label></th>
                    <td><input type="text" id="ifc_contact_location" name="ifc_contact_location" value="<?php echo $v( 'ifc_contact_location', 'Online & In-Person (NYC)' ); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e( 'Social Links', 'ironfit-core' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label for="ifc_social_instagram"><?php esc_html_e( 'Instagram URL', 'ironfit-core' ); ?></label></th>
                    <td><input type="url" id="ifc_social_instagram" name="ifc_social_instagram" value="<?php echo $v( 'ifc_social_instagram', '#' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_social_youtube"><?php esc_html_e( 'YouTube URL', 'ironfit-core' ); ?></label></th>
                    <td><input type="url" id="ifc_social_youtube" name="ifc_social_youtube" value="<?php echo $v( 'ifc_social_youtube', '#' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_social_tiktok"><?php esc_html_e( 'TikTok URL', 'ironfit-core' ); ?></label></th>
                    <td><input type="url" id="ifc_social_tiktok" name="ifc_social_tiktok" value="<?php echo $v( 'ifc_social_tiktok', '#' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_social_linkedin"><?php esc_html_e( 'LinkedIn URL', 'ironfit-core' ); ?></label></th>
                    <td><input type="url" id="ifc_social_linkedin" name="ifc_social_linkedin" value="<?php echo $v( 'ifc_social_linkedin', '#' ); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e( 'About Section', 'ironfit-core' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label for="ifc_about_text1"><?php esc_html_e( 'About Paragraph 1', 'ironfit-core' ); ?></label></th>
                    <td><textarea id="ifc_about_text1" name="ifc_about_text1" rows="3" class="large-text"><?php echo $vt( 'ifc_about_text1', "I'm Alex — a certified personal trainer with 8+ years of experience helping everyday people achieve extraordinary transformations. My approach blends science-backed programming with real-world accountability." ); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="ifc_about_text2"><?php esc_html_e( 'About Paragraph 2', 'ironfit-core' ); ?></label></th>
                    <td><textarea id="ifc_about_text2" name="ifc_about_text2" rows="3" class="large-text"><?php echo $vt( 'ifc_about_text2', "Whether you're a beginner or a seasoned athlete, I meet you where you are and take you where you want to go. No judgment. Just progress." ); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="ifc_about_coach_name"><?php esc_html_e( 'Coach Name', 'ironfit-core' ); ?></label></th>
                    <td><input type="text" id="ifc_about_coach_name" name="ifc_about_coach_name" value="<?php echo $v( 'ifc_about_coach_name', 'Alex Rivera' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_about_coach_title"><?php esc_html_e( 'Coach Title', 'ironfit-core' ); ?></label></th>
                    <td><input type="text" id="ifc_about_coach_title" name="ifc_about_coach_title" value="<?php echo $v( 'ifc_about_coach_title', 'Certified Personal Trainer' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_about_certs"><?php esc_html_e( 'Certifications (one per line)', 'ironfit-core' ); ?></label></th>
                    <td><textarea id="ifc_about_certs" name="ifc_about_certs" rows="4" class="large-text"><?php echo $vt( 'ifc_about_certs', "Certified Personal Trainer\nNSCA — CPT\nNutrition Specialist\nPrecision Nutrition Level 1\nBehavioral Coach\nMindset & habit formation\n200+ Transformations\nReal people, real results" ); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="ifc_about_specialties"><?php esc_html_e( 'Specialties (one per line)', 'ironfit-core' ); ?></label></th>
                    <td><textarea id="ifc_about_specialties" name="ifc_about_specialties" rows="3" class="large-text"><?php echo $vt( 'ifc_about_specialties', "Goal-Oriented\nEvery session has purpose\nData-Driven\nTrack & optimize your progress\nHolistic\nMindset + nutrition + movement\nAccountability\nI show up. You show up." ); ?></textarea></td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e( 'Stats & Rating', 'ironfit-core' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label for="ifc_about_stat1_number"><?php esc_html_e( 'Stat 1 Number', 'ironfit-core' ); ?></label></th>
                    <td><input type="text" id="ifc_about_stat1_number" name="ifc_about_stat1_number" value="<?php echo $v( 'ifc_about_stat1_number', '500+' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_about_stat1_label"><?php esc_html_e( 'Stat 1 Label', 'ironfit-core' ); ?></label></th>
                    <td><input type="text" id="ifc_about_stat1_label" name="ifc_about_stat1_label" value="<?php echo $v( 'ifc_about_stat1_label', 'Clients' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_about_stat2_number"><?php esc_html_e( 'Stat 2 Number', 'ironfit-core' ); ?></label></th>
                    <td><input type="text" id="ifc_about_stat2_number" name="ifc_about_stat2_number" value="<?php echo $v( 'ifc_about_stat2_number', '97%' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_about_stat2_label"><?php esc_html_e( 'Stat 2 Label', 'ironfit-core' ); ?></label></th>
                    <td><input type="text" id="ifc_about_stat2_label" name="ifc_about_stat2_label" value="<?php echo $v( 'ifc_about_stat2_label', 'Success Rate' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_about_stat3_number"><?php esc_html_e( 'Stat 3 Number', 'ironfit-core' ); ?></label></th>
                    <td><input type="text" id="ifc_about_stat3_number" name="ifc_about_stat3_number" value="<?php echo $v( 'ifc_about_stat3_number', '8yr' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_about_stat3_label"><?php esc_html_e( 'Stat 3 Label', 'ironfit-core' ); ?></label></th>
                    <td><input type="text" id="ifc_about_stat3_label" name="ifc_about_stat3_label" value="<?php echo $v( 'ifc_about_stat3_label', 'Experience' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_about_rating"><?php esc_html_e( 'Rating', 'ironfit-core' ); ?></label></th>
                    <td><input type="text" id="ifc_about_rating" name="ifc_about_rating" value="<?php echo $v( 'ifc_about_rating', '4.9 / 5.0' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ifc_about_rating_source"><?php esc_html_e( 'Rating Source', 'ironfit-core' ); ?></label></th>
                    <td><input type="text" id="ifc_about_rating_source" name="ifc_about_rating_source" value="<?php echo $v( 'ifc_about_rating_source', 'from 200+ reviews' ); ?>" class="regular-text"></td>
                </tr>
            </table>

            <?php submit_button( __( 'Save Settings', 'ironfit-core' ), 'primary', 'ifc_settings_submit' ); ?>
        </form>
    </div>
    <?php
}
