<?php
/**
 * The Template for displaying all single posts.
 *
 * @package dokan
 * @package dokan - 2014 1.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$store_user = dokan()->vendor->get(get_query_var('author'));
$isNanoverseStore = isNanoverseStorePage();

$store_info = $store_user->get_shop_info();
$map_location = $store_user->get_location();
$layout = get_theme_mod('store_layout', 'left');

get_header('shop');

if (function_exists('yoast_breadcrumb')) {
    yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
}
?>
<?php do_action('woocommerce_before_main_content'); ?>

<div class="dokan-store-wrap layout-<?php echo esc_attr($layout); ?>">

    <?php if (!$isNanoverseStore) { ?>
        <?php if ('left' === $layout) { ?>
            <?php dokan_get_template_part('store', 'sidebar', array('store_user' => $store_user, 'store_info' => $store_info, 'map_location' => $map_location)); ?>
        <?php } ?>
    <?php } ?>

    <div id="dokan-primary" class="dokan-single-store">
        <div id="dokan-content" class="store-page-wrap woocommerce" role="main">

            <?php dokan_get_template_part('store-header'); ?>

            <?php do_action('dokan_store_profile_frame_after', $store_user->data, $store_info); ?>

            <?php if (have_posts()) { ?>

                <div class="seller-items">

                    <?php if ($isNanoverseStore) { ?>
                        <div class="nanoverse-info-left-column">
                            <div class="nanoverse-contact-embassy">
                                <div class="nanoverse-contact-embassy-image">
                                    <img class="nanoverse-contact-image"
                                         src="<?php echo get_site_url(); ?>/wp-content/uploads/2022/01/27ARISTOTLE-articleLarge.jpeg">
                                </div>
                                <div class="nanoverse-contact-embassy-description">
                                    Odyssea Republic’ nanoverse is the ark of civilisations. A place build by futurists
                                    to host the allow of human greatness. A city-nation of hundreds languages and of
                                    many faces ruled by truth, logic and compassion.Discover the biggest university in
                                    the world. Explore writers, philosophers, and artists most personal thoughts and
                                    works. Contribute to the rebirth of our collective humanity.
                                </div>
                                <div class="nanoverse-contact-embassy-button">
                                    <a href=""
                                       class="elementor-button-link elementor-button elementor-size-sm login-button"
                                       role="button">
                                        <span class="elementor-button-content-wrapper">
                                            <span class="elementor-button-text">Contact our embassy</span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="nanoverse-metrics">
                                <div class="nanoverse-metrics-key">
                                    Key Metrics
                                </div>
                                <div>
                                    <div class="nanoverse-metrics-key-item-container">
                                        <div class="nanoverse-metrics-key-dot"></div>
                                        <div>5000,000 Citizenships to acquire</div>
                                    </div>
                                    <div class="nanoverse-metrics-key-item-container">
                                        <div class="nanoverse-metrics-key-dot"></div>
                                        <div>900 new Residents this week</div>
                                    </div>
                                    <div class="nanoverse-metrics-key-item-container">
                                        <div class="nanoverse-metrics-key-dot"></div>
                                        <div>2,000 New Business this month</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php woocommerce_product_loop_start(); ?>

                    <?php while (have_posts()) : the_post(); ?>
                        <?php if ($isNanoverseStore) { ?>
                            <?php global $product; ?>
                            <?php $image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'single-post-thumbnail'); ?>


                            <div class="nanoverse-product-cart">
                                <?php
                                $backgroundColor = get_post_meta($product->get_id(), '_nanoverse_background_color_card', true);
                                if ($backgroundColor) { ?>
                                    <div style="background-color: <?php echo get_post_meta($product->get_id(), '_nanoverse_background_color_card', true); ?>"
                                         class="nanoverse-product-image-container">
                                        <img class="nanoverse-product-image" src="<?php echo $image[0]; ?>"
                                             data-id="<?php echo $product->get_id(); ?>">
                                    </div>
                                <?php } else { ?>
                                    <div class="nanoverse-product-image-container">
                                        <img class="nanoverse-product-image" src="<?php echo $image[0]; ?>"
                                             data-id="<?php echo $product->get_id(); ?>">
                                    </div>
                                <?php } ?>

                                <div class="nanoverse-text-section">
                                    <div><?= $product->get_title() ?></div>
                                    <div class="nanoverse-product-description"><?= $product->get_short_description() ?></div>
                                    <div class="nanoverse-product-price">
                                        <div>Price: <?= $product->get_price_html() ?></div>
                                        <a href="?add-to-cart=<?php echo $product->get_id(); ?>" data-quantity="1"
                                           class="button product_type_simple ajax_add_to_cart"
                                           data-product_id="<?php echo $product->get_id(); ?>" data-product_sku="" rel="nofollow">
                                            <i class="fas fa-shopping-cart"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <?php wc_get_template_part('content', 'product'); ?>
                        <?php } ?>
                    <?php endwhile; // end of the loop. ?>

                    <?php woocommerce_product_loop_end(); ?>

                </div>

                <?php dokan_content_nav('nav-below'); ?>

            <?php } else { ?>

                <p class="dokan-info"><?php esc_html_e('No products were found of this vendor!', 'dokan-lite'); ?></p>

            <?php } ?>
        </div>

    </div><!-- .dokan-single-store -->
    <?php if (!$isNanoverseStore) { ?>
        <?php if ('right' === $layout) { ?>
            <?php dokan_get_template_part('store', 'sidebar', array('store_user' => $store_user, 'store_info' => $store_info, 'map_location' => $map_location)); ?>
        <?php } ?>
    <?php } ?>

</div><!-- .dokan-store-wrap -->

<?php do_action('woocommerce_after_main_content'); ?>

<?php get_footer('shop'); ?>
