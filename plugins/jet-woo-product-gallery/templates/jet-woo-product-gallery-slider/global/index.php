<?php
/**
 * Product Gallery Slider template
 */

$images_size         = $settings['image_size'];
$enable_gallery      = filter_var( $settings['enable_gallery'], FILTER_VALIDATE_BOOLEAN );
$gallery_trigger     = isset( $settings['gallery_trigger_type'] ) ? $settings['gallery_trigger_type'] : '';
$zoom                = filter_var( $settings['enable_zoom'], FILTER_VALIDATE_BOOLEAN ) ? 'jet-woo-product-gallery__image--with-zoom' : '';
$equal_slides_height = filter_var( $settings['slider_equal_slides_height'], FILTER_VALIDATE_BOOLEAN );
$gallery             = '[jet-woo-product-gallery]';
$dir                 = is_rtl() ? 'rtl' : 'ltr';

$slider_navigation       = filter_var( $settings['slider_show_nav'], FILTER_VALIDATE_BOOLEAN );
$thumb_slider_navigation = filter_var( $settings['slider_show_thumb_nav'], FILTER_VALIDATE_BOOLEAN );

$video_type          = jet_woo_gallery_video_integration()->get_video_type( $settings );
$video_thumbnail_url = $this->__get_video_thumbnail_url();
$video               = $this->__get_video_html();
$first_place_video   = filter_var( $settings['first_place_video'], FILTER_VALIDATE_BOOLEAN );

$thumbnail_column_classes = [
	jet_woo_product_gallery_tools()->col_classes( [
		'desk' => $settings['pagination_thumbnails_columns'],
		'tab'  => $settings['pagination_thumbnails_columns_tablet'],
		'mob'  => $settings['pagination_thumbnails_columns_mobile'],
	] ),
];

$slider_direction = 'jet-woo-swiper-horizontal';

if ( 'yes' === $settings['slider_show_pagination'] && 'vertical' === $settings['slider_pagination_direction'] ) {
	$pagination_position = 'jet-woo-swiper-v-pos-' . $settings['slider_pagination_v_position'];
	$slider_direction    = 'jet-woo-swiper-vertical';
} else {
	$pagination_position = 'jet-woo-swiper-h-pos-' . $settings['slider_pagination_h_position'];
}

$this->set_render_attribute(
	'swiper_slider_container',
	'class',
	[
		'jet-woo-product-gallery-slider',
		'swiper-container',
	]
);

$this->set_render_attribute(
	'gallery_swiper_slider_wrapper',
	'class',
	[
		'jet-woo-swiper',
		$slider_direction,
		$pagination_position,
	]
);
?>
<div <?php $this->print_render_attribute_string( 'gallery_swiper_slider_wrapper' ); ?> data-featured-image="<?php echo $with_featured_image; ?>">
	<div class="swiper-gallery-top">
		<div <?php $this->print_render_attribute_string( 'swiper_slider_container' ); ?> <?php echo $this->get_slider_data_settings(); ?> dir="<?php echo $dir; ?>">
			<div class="swiper-wrapper">

				<?php
				if ( 'content' === $settings['video_display_in'] && $first_place_video ) {
					include $this->__get_global_template( 'video' );
				}

				if ( $with_featured_image ) {
					if ( has_post_thumbnail( $post_id ) ) {
						include $this->__get_global_template( 'image' );
					} else {
						printf(
							'<div class="jet-woo-product-gallery__image-item featured no-image swiper-slide"><div class="jet-woo-product-gallery__image image-with-placeholder"><img src="%s" alt="%s" class="%s" /></div></div>',
							$this->__get_featured_image_placeholder(),
							__( 'Placeholder', 'jet-woo-product-gallery' ),
							'wp-post-image'
						);
					}
				}

				if ( $attachment_ids ) {
					foreach ( $attachment_ids as $attachment_id ) {
						include $this->__get_global_template( 'thumbnails' );
					}
				}

				if ( 'content' === $settings['video_display_in'] && ! $first_place_video ) {
					include $this->__get_global_template( 'video' );
				}
				?>

			</div>

			<?php
			if ( $slider_navigation ) {
				echo $this->get_slider_navigation( 'slider_nav_arrow_prev', 'slider_nav_arrow_next' );
			}

			if ( 'yes' === $settings['slider_show_pagination'] && 'thumbnails' !== $settings['slider_pagination_type'] ) {
				echo '<div class="swiper-pagination"></div>';
			}
			?>

		</div>
	</div>

	<?php
	if ( count( $attachment_ids ) > 1 || $with_featured_image && $attachment_ids ) {
		if ( 'yes' === $settings['slider_show_pagination'] && 'thumbnails' === $settings['slider_pagination_type'] && ( ! empty( $attachment_ids ) || $this->product_has_video() ) ) {
			include $this->__get_global_template( 'thumbnails-pagination' );
		}
	}

	if ( 'popup' === $settings['video_display_in'] ) {
		include $this->__get_global_template( 'popup-video' );
	}
	?>

</div>
