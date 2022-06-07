<?php
/**
 * Compare & Wishlist template functions class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Functions' ) ) {

	/**
	 * Define Jet_CW_Functions class
	 */
	class Jet_CW_Functions {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Return product title
		 *
		 * @param $product
		 *
		 * @return mixed|void
		 */
		public function get_title( $product ) {

			if ( ! $product ) {
				return;
			}

			$url = get_permalink( $product->get_id() );

			$title = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $url ),
				$product->get_title()
			);

			return apply_filters( 'jet-cw/template-functions/title', $title );

		}

		/**
		 * Return remove button compare
		 *
		 * @param $product
		 * @param $settings
		 *
		 * @return mixed|void
		 */
		public function get_compare_remove_button( $product, $settings ) {

			if ( ! $product ) {
				return;
			}

			$button_text = isset( $settings['compare_table_data_remove_text'] ) ? esc_html( $settings['compare_table_data_remove_text'] ) : '';
			$button_icon = isset( $settings['selected_compare_table_data_remove_icon'] ) ? '<span class="icon jet-cw-icon">' . htmlspecialchars_decode( $settings['selected_compare_table_data_remove_icon'] ) . '</span>' : '';

			$button = sprintf(
				'<button class="jet-cw-remove-button jet-compare-item-remove-button" data-product-id="%s">%s<span class="text">%s</span></button>',
				$product->get_id(),
				$button_icon,
				$button_text
			);

			return apply_filters( 'jet-cw/template-functions/compare-remove', $button );

		}

		/**
		 * Return remove button wishlist
		 *
		 * @param $product
		 * @param $settings
		 *
		 * @return mixed|void
		 */
		public function get_wishlist_remove_button( $product, $settings ) {

			if ( ! $product ) {
				return;
			}

			$button_text = isset( $settings['remove_button_text'] ) ? $settings['remove_button_text'] : '';
			$button_icon = isset( $settings['wishlist_remove_icon'] ) ? '<span class="icon jet-cw-icon">' . htmlspecialchars_decode( $settings['wishlist_remove_icon'] ) . '</span>' : '';

			$button = sprintf(
				'<button class="jet-cw-remove-button jet-wishlist-item-remove-button" data-product-id="%s">%s<span class="text">%s</span></button>',
				$product->get_id(),
				$button_icon,
				$button_text
			);

			return apply_filters( 'jet-cw/template-functions/wishlist-remove', $button );

		}

		/**
		 * Return product thumbnail
		 *
		 * @param $product
		 * @param $settings
		 *
		 * @return string
		 */
		public function get_thumbnail( $product, $settings ) {

			if ( ! $product ) {
				return;
			}

			$size           = isset( $settings['cw_thumbnail_size_size'] ) ? $settings['cw_thumbnail_size_size'] : 'thumbnail_size';
			$enable_overlay = isset( $settings['enable_image_overlay'] ) ? filter_var( $settings['enable_image_overlay'], FILTER_VALIDATE_BOOLEAN ) : false;

			$thumbnail_id = get_post_thumbnail_id( $product->get_id() );

			if ( empty( $thumbnail_id ) ) {
				$thumbnail = wc_placeholder_img( $size );
			} else {
				$thumbnail = wp_get_attachment_image( $thumbnail_id, $size, false );
			}

			$thumbnail_overlay = '';

			if ( $enable_overlay ) {
				$thumbnail_overlay = '<div class="jet-wishlist-product-img-overlay"></div>';
			}

			$thumbnail = sprintf( '<div class="jet-cw-thumbnail">%s %s</div>', $thumbnail, $thumbnail_overlay );

			return apply_filters( 'jet-cw/template-functions/thumbnail', $thumbnail );

		}

		/**
		 * Return product stock status
		 *
		 * @param $product
		 *
		 * @return string
		 */
		public function get_stock_status( $product ) {

			if ( ! $product ) {
				return;
			}

			$stock_status = wc_get_stock_html( $product );

			if ( ! empty( $stock_status ) ) {
				$stock_status = sprintf(
					'<span class="jet-cw-stock-status">%s</span>',
					$stock_status
				);

				return apply_filters( 'jet-cw/template-functions/stock-status', $stock_status );
			}

		}

		/**
		 * Return product sku
		 *
		 * @param $product
		 *
		 * @return mixed|void
		 */
		public function get_sku( $product ) {

			if ( ! $product ) {
				return;
			}

			if ( $product->get_sku() ) {
				$sku = sprintf(
					'<span class="jet-cw-sku">%s</span>',
					$product->get_sku()
				);

				return apply_filters( 'jet-cw/template-functions/sku', $sku );
			}

		}

		/**
		 * Return product dimension
		 *
		 * @param $product
		 *
		 * @return mixed|void
		 */
		public function get_dimensions( $product ) {

			if ( ! $product ) {
				return;
			}

			if ( $product->has_dimensions() ) {
				$dimensions = sprintf(
					'<span class="jet-cw-dimensions">%s</span>',
					wc_format_dimensions( $product->get_dimensions( false ) )
				);

				return apply_filters( 'jet-cw/template-functions/dimension', $dimensions );
			}

		}

		/**
		 * Return product weight
		 *
		 * @param $product
		 *
		 * @return mixed|void
		 */
		public function get_weight( $product ) {

			if ( ! $product ) {
				return;
			}

			if ( $product->get_weight() ) {
				$weight = sprintf(
					'<span class="jet-cw-weight">%s %s</span>',
					$product->get_weight(),
					get_option( 'woocommerce_weight_unit' )
				);

				return apply_filters( 'jet-cw/template-functions/weight', $weight );
			}

		}

		/**
		 * Return product rating
		 *
		 * @param $product
		 * @param $settings
		 *
		 * @return bool|mixed|void
		 */
		public function get_rating( $product, $settings ) {

			if ( ! $product ) {
				return;
			}

			$icon = empty( $settings['cw_rating_icon'] ) ? 'jetcomparewishlist-icon-rating-1' : $settings['cw_rating_icon'];

			if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) {
				return false;
			}

			$rating = $product->get_average_rating();

			if ( $rating > 0 ) {
				$rating_html = '<span class="jet-cw-rating-stars">';

				for ( $i = 1; $i <= 5; $i++ ) {
					$is_active_class = ( $i <= $rating ) ? 'active' : '';
					$rating_html     .= sprintf( '<span class="product-rating__icon %s %s"></span>', $icon, $is_active_class );
				}

				$rating_html .= '</span>';

				return apply_filters( 'jet-cw/template-functions/rating', $rating_html );
			} else {
				return false;
			}

		}

		/**
		 * Return product price
		 *
		 * @param $product
		 *
		 * @return mixed|void
		 */
		public function get_price( $product ) {

			if ( ! $product ) {
				return;
			}

			$price_html = $product->get_price_html();

			$price = sprintf(
				'<span class="jet-cw-price">%s</span>',
				$price_html
			);

			return apply_filters( 'jet-cw/template-functions/price', $price );

		}

		/**
		 * Return product excerpt
		 *
		 * @param $product
		 *
		 * @return mixed|void
		 */
		public function get_excerpt( $product ) {

			if ( ! $product ) {
				return;
			}

			if ( ! $product->get_short_description() ) {
				return;
			}

			$excerpt = sprintf(
				'<span class="jet-cw-excerpt">%s</span>',
				get_the_excerpt( $product->get_id() )
			);

			return apply_filters( 'jet-cw/template-functions/excerpt', $excerpt );

		}

		/**
		 * Return product description
		 *
		 * @param $product
		 *
		 * @return mixed|void
		 */
		public function get_description( $product ) {

			if ( ! $product ) {
				return;
			}

			if ( ! $product->get_description() ) {
				return;
			}

			$description = sprintf(
				'<span class="jet-cw-description">%s</span>',
				$product->get_description()
			);

			return apply_filters( 'jet-cw/template-functions/description', $description );

		}

		/**
		 * Return product add to cart button
		 *
		 * @param $product
		 *
		 * @return string
		 */
		public function get_add_to_cart_button( $product ) {

			$args    = array();
			$classes = array();

			if ( $product ) {
				$defaults = apply_filters(
					'jet-cw/template-functions/add-to-cart-settings',
					array(
						'quantity'   => 1,
						'class'      => implode( ' ', array_filter( array(
							'button',
							$classes,
							'product_type_' . $product->get_type(),
							$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
							$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
						) ) ),
						'attributes' => array(
							'data-product_id'  => $product->get_id(),
							'data-product_sku' => $product->get_sku(),
							'aria-label'       => $product->add_to_cart_description(),
							'rel'              => 'nofollow',
						),
					)
				);

				$args = wp_parse_args( $args, $defaults );

				$html = apply_filters( 'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
					sprintf( '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
						esc_url( $product->add_to_cart_url() ),
						esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
						esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
						isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
						esc_html( $product->add_to_cart_text() )
					),
					$product, $args );

				$html = '<div class="jet-cw-add-to-cart">' . $html . '</div>';

				return $html;
			}

			return null;

		}

		/**
		 * Return product categories
		 *
		 * @param $product
		 *
		 * @return mixed|void
		 */
		public function get_categories( $product ) {

			if ( ! $product ) {
				return;
			}

			$separator = '<span class="separator">&#44;&nbsp;</span></li><li>';
			$before    = '<ul><li>';
			$after     = '</li></ul>';

			$categories_list = get_the_term_list( $product->get_id(), 'product_cat', $before, $separator, $after );

			if ( ! $categories_list ) {
				return;
			}

			$categories = sprintf(
				'<span class="jet-cw-categories">%s</span>',
				$categories_list
			);

			return apply_filters( 'jet-cw/template-functions/categories', $categories );

		}

		/**
		 * Return product meta fields
		 *
		 * @param $product
		 * @param $settings
		 *
		 * @return string
		 */
		public function get_custom_field( $product, $settings ) {

			if ( ! $product ) {
				return;
			}

			$field_key = ! empty( $settings['compare_table_custom_field'] ) ? $settings['compare_table_custom_field'] : false;

			if ( ! $field_key ) {
				return '';
			}

			$field_value = get_post_meta( $product->get_id(), $field_key, true );

			if ( empty( $field_value ) ) {
				$field_value = ! empty( $settings['compare_table_custom_field_fallback'] ) ? $settings['compare_table_custom_field_fallback'] : $field_value;
			}

			$custom_field = apply_filters( 'jet-cw/template-functions/compare-custom-field/' . $field_key, $field_value );

			return sprintf( '<span class="jet-cw-custom-field">%s</span>', $custom_field );

		}

		/**
		 * Return product tags
		 *
		 * @param $product
		 *
		 * @return mixed|void
		 */
		public function get_tags( $product ) {

			if ( ! $product ) {
				return;
			}

			$separator = '<span class="separator">&#44;&nbsp;</span></li><li>';
			$before    = '<ul><li>';
			$after     = '</li></ul>';

			$tags_list = get_the_term_list( $product->get_id(), 'product_tag', $before, $separator, $after );

			if ( ! $tags_list ) {
				return;
			}

			$categories = sprintf(
				'<span class="jet-cw-tags">%s</span>',
				$tags_list
			);

			return apply_filters( 'jet-cw/template-functions/tags', $categories );

		}

		/**
		 * Trim text
		 *
		 * @return string
		 */
		public function trim_text( $text = '', $length = -1, $trimmed_type = 'word', $after = '' ) {

			if ( '' === $text ) {
				return $text;
			}

			if ( 0 === $length || '' === $length ) {
				return '';
			}

			if ( -1 !== intval( $length ) ) {
				if ( 'word' === $trimmed_type ) {
					$text = wp_trim_words( $text, $length, $after );
				} else {
					$text = wp_html_excerpt( $text, $length, $after );
				}
			}

			return $text;

		}

		/**
		 * Return visible product attributes
		 *
		 * @param $products
		 *
		 * @return array
		 */
		public function get_visible_products_attributes( $products ) {

			if ( ! $products ) {
				return;
			}

			$visible_attributes = array();

			foreach ( $products as $product ) {
				$has_attributes = $product->has_attributes();

				if ( $has_attributes ) {
					$attributes = $product->get_attributes();

					foreach ( $attributes as $key => $attribute ) {
						if ( $attribute->is_taxonomy() ) {
							$visible_attributes[ $key ] = wc_attribute_label( $key, $product );
						} else {
							$visible_attributes[ $key ] = $attribute->get_name();
						}
					}
				}
			}

			return $visible_attributes;

		}

		/**
		 * Returns the instance.
		 *
		 * @return object
		 * @since  1.0.0
		 */
		public static function get_instance( $shortcodes = array() ) {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self( $shortcodes );
			}

			return self::$instance;

		}

	}

}

/**
 * Returns instance of Jet_CW_Functions
 *
 * @return object
 */
function jet_cw_functions() {
	return Jet_CW_Functions::get_instance();
}
