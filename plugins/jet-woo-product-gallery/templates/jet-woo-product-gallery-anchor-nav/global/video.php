<?php
/**
 * Product Gallery Anchor video template
 */

if ( ! $this->product_has_video() ) {
	return null;
}

$this->add_render_attribute( 'video_wrapper', 'class', 'jet-woo-product-video' );

if ( 'self_hosted' !== $video_type ) {
	$this->add_render_attribute(
		'video_wrapper',
		'class',
		array(
			'jet-woo-product-video-aspect-ratio',
			'jet-woo-product-video-aspect-ratio--' . $settings['aspect_ratio'],
		)
	);
}

$this->add_render_attribute( 'video_overlay', 'class', 'jet-woo-product-video__overlay' );

if ( '' !== $video_thumbnail_url ) {
	$this->add_render_attribute( 'video_overlay', 'style', 'background-image: url(' . $video_thumbnail_url . ');' );
}

$play_button_html = '';

if ( filter_var( $settings['show_play_button'], FILTER_VALIDATE_BOOLEAN ) ) {
	$this->add_render_attribute( 'play_button', 'class', 'jet-woo-product-video__play-button' );
	$this->add_render_attribute( 'play_button', 'role', 'button' );

	$play_button_html = '<div ' . $this->get_render_attribute_string( 'play_button' ) . '>';

	switch ( $settings['play_button_type'] ) {
		case 'icon' :
			$play_button_html .= sprintf(
				'<span class="jet-woo-product-video__play-button-icon jet-product-gallery-icon">%s</span>',
				$this->__render_icon( 'play_button_icon', '%s', '', false )
			);
			break;
		case 'image':
			$play_button_html .= jet_woo_product_gallery_tools()->get_image_by_url(
				$settings['play_button_image']['url'],
				array(
					'class' => 'jet-woo-product-video__play-button-image',
					'alt'   => esc_html__( 'Play Video', 'jet-woo-product-gallery' ),
				)
			);
	}

	$play_button_html .= sprintf(
		'<span class="elementor-screen-only">%s</span>',
		esc_html__( 'Play Video', 'jet-woo-product-gallery' )
	);
	$play_button_html .= '</div>';
}

if ( $this->product_has_video() && $first_place_video ) {
	$anchor_nav_controller_id = $anchor_nav_controller_ids[0];
} else {
	$anchor_nav_controller_id = $this->get_unique_controller_id();
}
?>

<div class="jet-woo-product-gallery__image-item" id="<?php echo $anchor_nav_controller_id; ?>">
	<div class="jet-woo-product-gallery__image jet-woo-product-gallery--with-video">
		<div <?php $this->print_render_attribute_string( 'video_wrapper' ); ?>><?php echo $video ?></div>
		<div <?php $this->print_render_attribute_string( 'video_overlay' ); ?>><?php echo $play_button_html ?></div>
	</div>
</div>