<?php
/**
 * Product Gallery Anchor template
 */

$images_size         = $settings['image_size'];
$enable_gallery      = filter_var( $settings['enable_gallery'], FILTER_VALIDATE_BOOLEAN );
$gallery_trigger     = isset( $settings['gallery_trigger_type'] ) ? $settings['gallery_trigger_type'] : '';
$zoom                = 'yes' === $settings['enable_zoom'] ? 'jet-woo-product-gallery__image--with-zoom' : '';
$gallery             = '[jet-woo-product-gallery]';
$video_thumbnail_url = $this->__get_video_thumbnail_url();
$video_type          = jet_woo_gallery_video_integration()->get_video_type( $settings );
$video               = $this->__get_video_html();
$first_place_video   = 'content' === $settings['video_display_in'] ? filter_var( $settings['first_place_video'], FILTER_VALIDATE_BOOLEAN ) : false;

if ( $with_featured_image || ( ! $with_featured_image && $first_place_video ) ) {
	$anchor_nav_controller_ids = [ $this->get_unique_controller_id() ];
} else {
	$anchor_nav_controller_ids = [];
}
?>

<div class="jet-woo-product-gallery-anchor-nav" data-featured-image="<?php echo $with_featured_image; ?>">
	<div class="jet-woo-product-gallery-anchor-nav-items">
		<?php
		if ( 'content' === $settings['video_display_in'] && $this->product_has_video() && $first_place_video ) {
			include $this->__get_global_template( 'video' );
		}

		if ( $with_featured_image ) {
			if ( has_post_thumbnail( $post_id ) ) {
				include $this->__get_global_template( 'image' );
			} else {
				if ( $this->product_has_video() && $first_place_video ) {
					$anchor_nav_controller_id = $this->get_unique_controller_id();

					array_push( $anchor_nav_controller_ids, $anchor_nav_controller_id );
				}

				printf(
					'<div class="jet-woo-product-gallery__image-item featured no-image" id="%s"><div class="jet-woo-product-gallery__image image-with-placeholder"><img src="%s" alt="%s" class="%s" /></div></div>',
					$first_place_video ? $anchor_nav_controller_ids[1] : $anchor_nav_controller_ids[0],
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

		if ( 'content' === $settings['video_display_in'] && $this->product_has_video() && ! $first_place_video ) {
			include $this->__get_global_template( 'video' );
			array_push( $anchor_nav_controller_ids, $anchor_nav_controller_id );
		}
		?>
	</div>
	<?php include $this->__get_global_template( 'controller' ); ?>
</div>