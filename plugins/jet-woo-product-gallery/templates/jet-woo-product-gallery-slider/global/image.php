<?php
/**
 * Product Gallery Slider main image template
 */

$props = wc_get_product_attachment_props( get_post_thumbnail_id( $post_id ), $post );
$image = get_the_post_thumbnail( $post_id, $images_size, array(
	'title'                   => $props['title'],
	'alt'                     => $props['alt'],
	'data-caption'            => $props['caption'],
	'data-src'                => $props['src'],
	'data-large_image'        => $props['full_src'],
	'data-large_image_width'  => $props['full_src_w'],
	'data-large_image_height' => $props['full_src_h'],
	'class'                   => 'wp-post-image',
) );

$trigger_class = $enable_gallery && 'image' === $gallery_trigger ? 'jet-woo-product-gallery__trigger' : '';

$this->set_render_attribute( 'image_link', 'class', 'jet-woo-product-gallery__image-link ' . $trigger_class );
$this->add_render_attribute( 'image_link', 'href', esc_url( $props['url'] ) );
$this->add_render_attribute( 'image_link', 'itemprop', 'image' );
$this->add_render_attribute( 'image_link', 'title', esc_attr( $props['caption'] ) );
$this->add_render_attribute( 'image_link', 'rel', 'prettyPhoto' . $gallery );
?>

<div class="jet-woo-product-gallery__image-item featured swiper-slide">
	<div class="jet-woo-product-gallery__image <?php echo $zoom ?>">
		<a <?php $this->print_render_attribute_string( 'image_link' ); ?>>
			<?php echo $image; ?>
		</a>
		<?php if ( $enable_gallery && 'button' === $gallery_trigger ) {
			jet_woo_product_gallery_functions()->get_gallery_trigger_button( $this->__render_icon( 'gallery_button_icon', '%s', '', false ) );
		} ?>
	</div>
</div>