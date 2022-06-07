<?php
require_once(get_stylesheet_directory() . '/nanoverse/constants.php');
require_once(get_stylesheet_directory() . '/nanoverse/utils.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');


/**
 * Child functions and definitions.
 */
add_filter('kava-theme/assets-depends/styles', 'kava_child_styles_depends');

/**
 * Enqueue styles.
 */
function kava_child_styles_depends($deps) {

    $parent_handle = 'kava-parent-theme-style';

    wp_register_style(
        $parent_handle,
        get_template_directory_uri() . '/style.css',
        array(),
        kava_theme()->version()
    );

    $deps[] = $parent_handle;

    return $deps;
}

/**
 * Disable magic button for your clients
 *
 * Un-comment next line to disble magic button output for you clients.
 */
//add_action( 'jet-theme-core/register-config', 'kava_child_disable_magic_button' );

function kava_child_disable_magic_button($config_manager) {
    $config_manager->register_config(array(
        'library_button' => false,
    ));
}

/**
 * Disable unnecessary theme modules
 *
 * Un-comment next line and return unnecessary modules from returning modules array.
 */
//add_filter( 'kava-theme/allowed-modules', 'kava_child_allowed_modules' );

function kava_child_allowed_modules($modules) {

    return array(
        'blog-layouts' => array(),
        'crocoblock' => array(),
        'woo' => array(
            'woo-breadcrumbs' => array(),
            'woo-page-title' => array(),
        ),
    );

}

/**
 * Registering a new structure
 *
 * To change structure and apropriate documnet type parameters go to
 * structures/archive.php and document-types/archive.php
 *
 * To print apropriate location add next code where you need it:
 * if ( function_exists( 'jet_theme_core' ) ) {
 *     jet_theme_core()->locations->do_location( 'kava_child_archive' );
 * }
 * Where 'kava_child_archive' - apropritate location name (from example).
 *
 * Un-comment next line to register new structure.
 */
//add_action( 'jet-theme-core/structures/register', 'kava_child_structures' );

function kava_child_structures($structures_manager) {

    require get_theme_file_path('structures/archive.php');
    require get_theme_file_path('structures/404.php');

    $structures_manager->register_structure('Kava_Child_Structure_Archive');
    $structures_manager->register_structure('Kava_Child_Structure_404');

}

get_template_part('inc/classes/class-tgm-plugin-activation');

add_action('tgmpa_register', 'wp_179712_register_required_plugins');
function wp_179712_register_required_plugins() {

    $plugins = array(
        array(
            'name' => esc_html__('Elementor', 'wp_179712'),
            'slug' => 'elementor',
            'required' => true
        ),
        // array(
        // 	'name'         => esc_html__( 'AnyWhere Elementor', 'wp_179712' ),
        // 	'slug'         => 'anywhere-elementor',
        // 	'required'     => true
        // ),
        array(
            'name' => esc_html__('Ultimate Member', 'wp_179712'),
            'slug' => 'ultimate-member',
            'required' => true,
        ),
        array(
            'name' => esc_html__('Contact Form 7', 'wp_179712'),
            'slug' => 'contact-form-7',
            'required' => true,
        ),
        array(
            'name' => esc_html__('WooCommerce', 'wp_179712'),
            'slug' => 'woocommerce',
            'required' => true,
        ),
        array(
            'name' => esc_html__('Jet Elements For Elementor', 'wp_179712'),
            'slug' => 'jet-elements',
            'source' => get_stylesheet_directory() . '/plugins/jet-elements.zip',
            'required' => true,
        ),
        array(
            'name' => esc_html__('Jet Tricks', 'wp_179712'),
            'slug' => 'jet-tricks',
            'source' => get_stylesheet_directory() . '/plugins/jet-tricks.zip',
            'required' => true,
        ),
        array(
            'name' => esc_html__('Jet Blog For Elementor', 'wp_179712'),
            'slug' => 'jet-blog',
            'source' => get_stylesheet_directory() . '/plugins/jet-blog.zip',
            'required' => true,
        ),
        array(
            'name' => esc_html__('Jet Tabs For Elementor', 'wp_179712'),
            'slug' => 'jet-tabs',
            'source' => get_stylesheet_directory() . '/plugins/jet-tabs.zip',
            'required' => true,
        ),
        array(
            'name' => esc_html__('Jet Popup For Elementor', 'wp_179712'),
            'slug' => 'jet-popup',
            'source' => get_stylesheet_directory() . '/plugins/jet-popup.zip',
            'required' => true,
        ),
        array(
            'name' => esc_html__('Jet Blocks For Elementor', 'wp_179712'),
            'slug' => 'jet-blocks',
            'source' => get_stylesheet_directory() . '/plugins/jet-blocks.zip',
            'required' => true,
        ),
        array(
            'name' => esc_html__('Jet Theme Core', 'wp_179712'),
            'slug' => 'jet-theme-core',
            'source' => get_stylesheet_directory() . '/plugins/jet-theme-core.zip',
            'required' => true,
        ),
        array(
            'name' => esc_html__('Jet WooBuilder For Elementor', 'wp_williams_jewelers'),
            'slug' => 'jet-woo-builder',
            'source' => get_stylesheet_directory() . '/plugins/jet-woo-builder.zip',
            'required' => true,
        ),
        array(
            'name' => esc_html__('Jet WooProduct Gallery For Elementor', 'wp_williams_jewelers'),
            'slug' => 'jet-woo-product-gallery',
            'source' => get_stylesheet_directory() . '/plugins/jet-woo-product-gallery.zip',
            'required' => true,
        ),
        array(
            'name' => esc_html__('Jet Compare & Wishlist  For Elementor', 'wp_williams_jewelers'),
            'slug' => 'jet-compare-wishlist',
            'source' => get_stylesheet_directory() . '/plugins/jet-compare-wishlist.zip',
            'required' => true,
        ),
        array(
            'name' => esc_html__('Jet SmartFilters  For Elementor', 'wp_williams_jewelers'),
            'slug' => 'jet-smart-filters',
            'source' => get_stylesheet_directory() . '/plugins/jet-smart-filters.zip',
            'required' => true,
        ),
    );

    $config = array(
        'id' => 'wp_179712',
        'default_path' => '',
        'menu' => 'tgmpa-install-plugins',
        'has_notices' => true,
        'dismissable' => true,
        'dismiss_msg' => '',
        'is_automatic' => true,
        'message' => '',
    );

    tgmpa($plugins, $config);
}

locate_template('inc/theme-filters.php', true);

add_action('wp_enqueue_scripts', 'wp_179712_enqueue_style_script', 99);

function wp_179712_enqueue_style_script() {

    wp_enqueue_style('main-css', get_stylesheet_directory_uri() . '/assets/css/main.css');
    wp_enqueue_style('custom-css', get_stylesheet_directory_uri() . '/assets/css/custom.css');

    wp_register_script('main', get_stylesheet_directory_uri() . '/assets/js/main.js', array('jquery'), true);
    
    wp_enqueue_script('main');
    
}

add_action('customize_register', function ($customizer) {
    $customizer->add_section(
        'example_section_one',
        array(
            'title' => 'Preloader section',
            'description' => '',
            'priority' => 11,
        )
    );
    $customizer->add_setting(
        'img-upload'
    );
    $customizer->add_control(
        new WP_Customize_Image_Control(
            $customizer,
            'img-upload',
            array(
                'label' => 'Image Downloads',
                'section' => 'example_section_one',
                'settings' => 'img-upload'
            )
        )
    );
});

/**
 * Odyssea store customization
 */

// Load styles & js
// 99 for priority of styles
add_action('wp_enqueue_scripts', 'my_enqueue_theme_js', 99);
function my_enqueue_theme_js() {
    wp_enqueue_script('wc-checkout-customized',
        get_stylesheet_directory_uri() . '/assets/wocommerce/checkout.js',
        array('jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n'),
        '5.9.0.'
    );

    wp_enqueue_style('react-styles',
        get_stylesheet_directory_uri() . '/assets/react/index.css',
        [],
        time()
    );

    wp_enqueue_script(
        'react-scripts',
        get_stylesheet_directory_uri() . '/assets/react/index.js',
        ['wp-element'],
        time(), // Change this to null for production
        true
    );

    wp_register_script('custom', get_stylesheet_directory_uri() . '/assets/js/custom.js', array('jquery'), true);
    wp_enqueue_script('custom');
    
    wp_enqueue_script('backend-data', get_stylesheet_directory_uri() . '/assets/not-delete-localize-script.js');
    wp_localize_script('backend-data', 'backendData', [
        'nonce' => wp_create_nonce('wp_rest'),
    ]);

    // Add Nanoverse specific css
    if (isNanoverseStorePage() || is_checkout()) {
        wp_enqueue_style('nanoverse-vendor-styles',
            get_stylesheet_directory_uri() . '/assets/css/nanoverse.css',
            [],
            time()
        );
    }
}

add_action('rest_api_init', function () {
    register_rest_route('api/v1', 'get-dokan-last-visited-stores', array('methods' => 'GET', 'callback' => 'get_dokan_last_visited_stores'));
});


function get_dokan_last_visited_stores() {
    $last_visited_duplicated = get_user_meta(get_current_user_id(), 'dokan_last_visited_stores', true);
    if ($last_visited_duplicated) {
        $last_visited_duplicated = array_reverse($last_visited_duplicated);
        $last_visited_without_duplicated = [];
        foreach ($last_visited_duplicated as $key => $vendor) {
            $last_visited_without_duplicated[] = format_vendor($vendor);
        }
        return $last_visited_without_duplicated;
    }
    return [];
}

function format_vendor($sellerId) {
    $vendor = dokan()->vendor->get($sellerId);
    $store_banner_id = $vendor->get_banner_id();
    return [
        'store_name' => $vendor->get_shop_name(),
        'shop_url' => $vendor->get_shop_url(),
        'store_rating' => $vendor->get_rating(),
        'is_store_featured' => $vendor->is_featured(),
        'store_phone' => $vendor->get_phone(),
        'store_info' => dokan_get_store_info($sellerId),
        'store_address' => dokan_get_seller_short_address($sellerId),
        'store_banner_url' => $store_banner_id ? wp_get_attachment_image_src($store_banner_id, 'full') : DOKAN_PLUGIN_ASSEST . '/images/default-store-banner.png',
        'banner' => $vendor->get_banner()
    ];
}

add_action('template_redirect', 'record_user_dokan_access');

function record_user_dokan_access() {
    $actual_page = home_url($_SERVER['REQUEST_URI']);

    if (substr($actual_page, 0, strlen(home_url('/store'))) === home_url('/store')) {
        $sellers = dokan_get_sellers();
        foreach ($sellers['users'] as $seller) {
            $vendor = dokan()->vendor->get($seller->ID);
            if ($vendor->get_shop_url() == $actual_page) {
                $last_stores = get_user_meta(get_current_user_id(), 'dokan_last_visited_stores', true);
                if (!$last_stores) {
                    $last_stores = [];
                }
                $exists = array_search($vendor->data->ID, $last_stores);
                if ($exists !== false) {
                    array_splice($last_stores, $exists, 1);
                    $last_stores[] = $vendor->data->ID;
                } else {
                    $last_stores[] = $vendor->data->ID;
                    if (count($last_stores) > 3) {
                        $last_stores = array_splice($last_stores, -3);
                    }
                }
                update_user_meta(get_current_user_id(), 'dokan_last_visited_stores', $last_stores);
            }
        }
    }
}

/**
 * Add custom color for Nanoverse products in admin.
 */
add_action('woocommerce_product_options_general_product_data', function () {
    if (isProductFromNanoverse(get_the_id())) {
        woocommerce_wp_text_input([
            'id' => '_nanoverse_background_color_card',
            'label' => __('Background color card in hex format', 'txtdomain'),
            'value' => get_post_meta(get_the_ID(), '_nanoverse_background_color_card', true),
        ]);
    }
});

/**
 * Save custom color for Nanoverse product.
 */
add_action('woocommerce_process_product_meta', function ($post_id) {
    $product = wc_get_product($post_id);
    $num_package = isset($_POST['_nanoverse_background_color_card']) ? $_POST['_nanoverse_background_color_card'] : '';
    $product->update_meta_data('_nanoverse_background_color_card', sanitize_text_field($num_package));
    $product->save();
});

/**
 * Needed for non-ajax billing form submit
 */
add_action('wp_print_scripts', function () {
    wp_dequeue_script('wc-checkout');
    wp_deregister_script('wc-checkout');
});


add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    if (cartContainsProductFromNanoverse()) {

        uploadPicturesFromCheckout($order_id);

        uploadNanoverseFile($order_id, 'passport');
        uploadNanoverseFile($order_id, 'executive_summary');
        uploadNanoverseFile($order_id, 'executive_pitch_deck');

        foreach (NANOVERSE_CHECKOUT_NON_FILE_FIELDS as $checkoutItems) {
            update_post_meta($order_id, NANOVERSE_PREFIX . $checkoutItems, $_POST[NANOVERSE_PREFIX . $checkoutItems]);
        }

        if (!empty($_POST[NANOVERSE_PREFIX . 'wallet_address'])) {
            foreach (array('ETH', 'BSC', 'CELO', 'MATIC', 'ONE') as $chain) {
                update_post_meta($order_id, 'recipient_blockchain_address_' . $chain, sanitize_text_field($_POST[NANOVERSE_PREFIX . 'wallet_address']));
            }
        }

    }
});

function uploadPicturesFromCheckout($order_id) {
    $uploads_url = '/odyssea/citizenship/order/' . $order_id . '/pictures';
    $citizenship_dir_path = NANOVERSE_BASE_UPLOAD_URL_CITIZENSHIP . $uploads_url;

    createDir($citizenship_dir_path);

    $upload_dir = wp_upload_dir();
    $index = 0;
    foreach ($_FILES[NANOVERSE_PREFIX . 'picture_uploads']['name'] as $key => $file) {
        uploadFile($_FILES[NANOVERSE_PREFIX . 'picture_uploads']['name'][$key], $_FILES[NANOVERSE_PREFIX . 'picture_uploads']['tmp_name'][$key], $citizenship_dir_path);
        update_post_meta($order_id, NANOVERSE_PREFIX . 'picture_uploads_' . $index, $upload_dir['baseurl'] . $uploads_url . '/' . $_FILES[NANOVERSE_PREFIX . 'picture_uploads']['name'][$key]);
        $index++;
    }

    update_post_meta($order_id, NANOVERSE_PREFIX . 'picture_uploads_count', $index);
}

function uploadNanoverseFile($order_id, $input_name) {
    if ($_FILES[NANOVERSE_PREFIX . $input_name]) {
        $uploads_url = '/odyssea/citizenship/order/' . $order_id . '/' . $input_name;
        $citizenship_dir_path = NANOVERSE_BASE_UPLOAD_URL_CITIZENSHIP . $uploads_url;
        createDir($citizenship_dir_path);
        uploadFile($_FILES[NANOVERSE_PREFIX . $input_name]['name'], $_FILES[NANOVERSE_PREFIX . $input_name]['tmp_name'], $citizenship_dir_path);
        $upload_dir = wp_upload_dir();
        update_post_meta($order_id, NANOVERSE_PREFIX . $input_name, $upload_dir['baseurl'] . $uploads_url . '/' . $_FILES[NANOVERSE_PREFIX . $input_name]['name']);
    }
}

add_action('woocommerce_checkout_process', function () {
    if ($_FILES && $_FILES[NANOVERSE_PREFIX . 'picture_uploads']) {
        if (sizeof($_FILES[NANOVERSE_PREFIX . 'picture_uploads']['name']) !== 5) {
            wc_add_notice(__('You must upload exactly 5 pictures.'), 'error');
        }
    }
});

add_filter('woocommerce_billing_fields', function ($billing_fields) {
    // Only on checkout page
    if (!is_checkout()) return $billing_fields;
    if (!cartContainsProductFromNanoverse()) return $billing_fields;

    $billing_fields['billing_phone']['required'] = false;
    $billing_fields['billing_email']['required'] = false;
    $billing_fields['billing_postcode']['required'] = false;
    $billing_fields['billing_city']['required'] = false;
    $billing_fields['billing_address_1']['required'] = false;
    $billing_fields['billing_country']['required'] = false;
    $billing_fields['billing_last_name']['required'] = false;
    $billing_fields['billing_first_name']['required'] = false;
    return $billing_fields;
}, 20, 1);
