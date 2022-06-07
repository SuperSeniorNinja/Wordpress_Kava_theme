<?php
/**
 * The template for displaying the default footer layout.
 *
 * @package Kava
 */
?>

<div <?php kava_footer_class("text-center"); ?>>
	<div class="row row-20">
        <div class="col-md-6 text-md-left">
            <?php kava_footer_copyright(); ?>
        </div>
        <div class="col-md-6 text-md-right">
        	<?php kava_social_list( 'footer' ); ?>
        </div>
    </div>
</div><!-- .container -->