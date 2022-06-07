<?php
/**
 * Product Gallery Grid popup video template
 */

if ( ! $this->product_has_video() ) {
	return null;
}

$this->add_render_attribute( 'video_popup_wrapper', 'class', 'jet-woo-product-video' );

$vertical_aspect_ratio = '';

if ( 'self_hosted' !== $video_type ) {
	if ( '9-16' === $settings['aspect_ratio'] || '2-3' === $settings['aspect_ratio'] ) {
		$vertical_aspect_ratio = 'jet-woo-vertical-aspect-ratio--' . $settings['aspect_ratio'];
	}

	$this->add_render_attribute(
		'video_popup_wrapper',
		'class',
		array(
			'jet-woo-product-video-aspect-ratio',
			'jet-woo-product-video-aspect-ratio--' . $settings['aspect_ratio'],
		)
	);
}

$this->add_render_attribute( 'video_popup_overlay', 'class', 'jet-woo-product-video__popup-overlay' );

if ( '' !== $video_thumbnail_url ) {
	$this->add_render_attribute( 'video_popup_overlay', 'style', 'background-image: url(' . $video_thumbnail_url . ');' );
}


$this->add_render_attribute( 'popup_button', 'class', 'jet-woo-product-video__popup-button' );
$this->add_render_attribute( 'popup_button', 'role', 'button' );

$popup_button_html = '<div ' . $this->get_render_attribute_string( 'popup_button' ) . '>';
$popup_button_html .= sprintf(
	'<span class="jet-woo-product-video__popup-button-icon jet-product-gallery-icon" aria-hidden="true">%s</span>',
	$this->__render_icon( 'popup-button-icon', '%s', '', false )
);
$popup_button_html .= sprintf(
	'<span class="elementor-screen-only">%s</span>',
	esc_html__( 'Open popup with video', 'jet-woo-product-gallery' )
);
$popup_button_html .= '</div>';
?>

<div class="jet-woo-product-video__popup-wrapper">
	<?php echo $popup_button_html ?>
	<div class="jet-woo-product-video__popup-content">
		<div class="jet-woo-product-video__popup-overlay"></div>
		<div class="jet-woo-product-video__popup <?php echo $vertical_aspect_ratio; ?>">
			<div <?php $this->print_render_attribute_string( 'video_popup_wrapper' ); ?>><?php echo $video ?></div>
		</div>
	</div>
</div>