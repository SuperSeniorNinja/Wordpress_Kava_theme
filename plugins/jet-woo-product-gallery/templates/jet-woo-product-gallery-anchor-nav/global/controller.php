<?php
/**
 * Product Gallery Anchor Nav Controller template
 */
?>
<ul class="jet-woo-product-gallery-anchor-nav-controller">
	<?php
	foreach ( $anchor_nav_controller_ids as $anchor_nav_controller_id ) {
		printf(
			'<li class="controller-item"><a href="#%s" data-index="%s"><span class="controller-item__bullet"></span></a></li>',
			$anchor_nav_controller_id,
			$anchor_nav_controller_id
		);
	}

	if ( 'popup' === $settings['video_display_in'] && $this->product_has_video() ) {
		echo '<li class="controller-item">';
		include $this->__get_global_template( 'popup-video' );
		echo '</li>';
	}
	?>
</ul>