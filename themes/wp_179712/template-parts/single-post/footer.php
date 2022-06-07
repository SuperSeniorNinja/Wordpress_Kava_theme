s<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Kava
 */

?>

<footer class="entry-footer">
	<div class="entry-meta"><?php
		kava_post_tags ( array(
			'prefix'    => __( 'Tags:', 'kava' ),
			'delimiter' => ''
		) );
	?></div>
	<div class="post-single__footer">
<!-- 	    <p class="heading-5"><?php esc_html_e( 'Share this Post:', 'wp_179712' ); ?></p> -->
<!--         <?php echo do_shortcode('[INSERT_ELEMENTOR id="112"]'); ?> -->
<!--	    --><?php //do_action('cherry_socialize_display_sharing', null, array(), array() ); ?>
	</div>
</footer><!-- .entry-footer -->