<?php
/**
 * Template part for top panel in header.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Kava
 */

// Don't show top panel if all elements are disabled.
if (!kava_is_top_panel_visible()) {
    return;
} ?>

<div class="top-panel container">
    <a href="<?= get_site_url() ?>" class="header-image">
        <img class="header-image" src="<?php echo wp_get_attachment_url(1238); ?>">
    </a>
    <div class="header-menu">
        <div class="header-menu-item"><a href="<?= get_site_url() ?>/browse/">Browse</a></div>
        <div class="header-menu-item"> NFTs <i class="fas fa-chevron-down"></i>
            <div class="dropdown-content">
                <a href="<?= get_site_url() ?>/view-my-nfts">View my NFTs</a>
                <a href="<?= get_site_url() ?>/create-an-item/">Create An Item</a>
            </div>
        </div>
        <div class="header-menu-item">Categories <i class="fas fa-chevron-down"></i>
            <div class="dropdown-content">
                <a href="<?= get_site_url() ?>/product-category/art">Art</a>
                <a href="<?= get_site_url() ?>/product-category/watches">Watches</a>
                <a href="<?= get_site_url() ?>/product-category/wine">Wine</a>
                <a href="<?= get_site_url() ?>/product-category/supercars">Supercars</a>
                <a href="<?= get_site_url() ?>/product-category/securities">Securities</a>
                <a href="<?= get_site_url() ?>/product-category/collectibles">Collectibles</a>
            </div>
        </div>
        <div id="vendor-search"></div>
    </div>
    <div class="header-user-wallet">
        <div id="nanoverse-wallet-modal"></div>
        <div class="header-avatar-container"><?php get_template_part('template-parts/user-avatar'); ?></div>
		<div class="header-cart">
			<i class="fa fa-shopping-basket" aria-hidden="true"></i>
			<div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div>
		</div>
    </div>
    
    <div class="header-cart-mobile">
        <?php get_template_part('template-parts/user-avatar'); ?>
        <div class="header-mobile-cart">
            <i class="fa fa-shopping-basket" aria-hidden="true"></i>

            <div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div>
        </div>
        <div id="header-menu-bar-react"></div>
    </div>

</div>