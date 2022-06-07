<?php
/**
 * Template part for default Header layout.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Kava
 */
?>

<?php get_template_part( 'template-parts/top-panel' ); ?>
<?php do_action( 'kava-theme/header/before' ); ?>
<div class="rd-navbar-wrap">
    <nav class="rd-navbar" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed" data-sm-device-layout="rd-navbar-fixed" data-md-layout="rd-navbar-fixed" data-md-device-layout="rd-navbar-fixed" data-lg-device-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-static" data-xl-device-layout="rd-navbar-static" data-xl-layout="rd-navbar-static" data-xxl-device-layout="rd-navbar-static" data-xxl-layout="rd-navbar-static" data-stick-up-clone="false" data-md-stick-up-offset="113px" data-lg-stick-up-offset="138px" data-xl-stick-up-offset="138px" data-md-stick-up="true" data-lg-stick-up="true" data-xl-stick-up="true">
        <div class="rd-navbar-panel">
            <button class="rd-navbar-toggle" data-rd-navbar-toggle=".rd-navbar-nav-wrap"><span></span></button>
        </div>
        <div class="rd-navbar-main-outer">
            <div class="rd-navbar-main">
                <div class="rd-navbar-nav-wrap">
                    <?php get_rd_menu(); ?>
                    <div class="rd-navbar-brand">
                        <?php kava_header_logo(); ?>
                    </div>
                </div>
                <div class="rd-navbar-content__toggle rd-navbar-static--hidden" data-rd-navbar-toggle=".rd-navbar-content"><span></span></div>
                <div class="rd-navbar-content">
                    <?php get_header_link(); ?>
                </div>
            </div>
           
        </div>
    </nav>
</div>
<?php do_action( 'kava-theme/header/after' ); ?>