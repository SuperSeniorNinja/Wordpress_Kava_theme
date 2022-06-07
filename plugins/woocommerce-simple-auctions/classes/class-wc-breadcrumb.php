<?php
/**
 * WC_Auctions_Breadcrumb class.
 *
 * @version 1.2.34
 */

defined( 'ABSPATH' ) || exit;

/**
 * Breadcrumb class.
 */
class WC_Auctions_Breadcrumb extends WC_Breadcrumb {


	/**
	 * Prepend the shop page to shop breadcrumbs.
	 */
	protected function prepend_shop_page() {

		$permalinks   = wc_get_permalink_structure();
		$shop_page_id = wc_get_page_id( 'shop' );
		$shop_page    = get_post( $shop_page_id );

		$auction_page_id = wc_get_page_id( 'auction' );
		$auction_page    = get_post( $auction_page_id );
		$product = wc_get_product( get_the_ID() );

		// If permalinks contain the shop page in the URI prepend the breadcrumb with shop.
		if ( $shop_page_id && $shop_page && isset( $permalinks['product_base'] ) && strstr( $permalinks['product_base'], '/' . $shop_page->post_name ) && intval( get_option( 'page_on_front' ) ) !== $shop_page_id ) {
			if ( $auction_page_id  && $product &&  $product->get_type() === 'auction' ){
				$this->add_crumb( get_the_title( $auction_page ), get_permalink( $auction_page ) );
			} else {
				$this->add_crumb( get_the_title( $shop_page ), get_permalink( $shop_page ) );
			}
		}
	}

	/**
	 * Single post trail.
	 *
	 * @param int    $post_id   Post ID.
	 * @param string $permalink Post permalink.
	 */
	protected function add_crumbs_single( $post_id = 0, $permalink = '' ) {
		if ( ! $post_id ) {
			global $post;
		} else {
			$post = get_post( $post_id ); // WPCS: override ok.
		}
		if ( ! $permalink ) {
			$permalink = get_permalink( $post );
		}

		if ( 'product' === get_post_type( $post ) ) {
			$this->prepend_shop_page();

			$terms = wc_get_product_terms(
				$post->ID,
				'product_cat',
				apply_filters(
					'woocommerce_breadcrumb_product_terms_args',
					array(
						'orderby' => 'parent',
						'order'   => 'DESC',
					)
				)
			);

			if ( $terms ) {
				$product = wc_get_product( $post->ID );
				$auction_page_id = wc_get_page_id( 'auction' );
				if ( ! ($product &&  $product->get_type() === 'auction' && $auction_page_id  ) ){
					$main_term = apply_filters( 'woocommerce_breadcrumb_main_term', $terms[0], $terms );
					$this->term_ancestors( $main_term->term_id, 'product_cat' );
					$this->add_crumb( $main_term->name, get_term_link( $main_term ) );
				}
			}
		} elseif ( 'post' !== get_post_type( $post ) ) {
			$post_type = get_post_type_object( get_post_type( $post ) );

			if ( ! empty( $post_type->has_archive ) ) {
				$this->add_crumb( $post_type->labels->singular_name, get_post_type_archive_link( get_post_type( $post ) ) );
			}
		} else {
			$cat = current( get_the_category( $post ) );
			if ( $cat ) {
				$this->term_ancestors( $cat->term_id, 'category' );
				$this->add_crumb( $cat->name, get_term_link( $cat ) );
			}
		}

		$this->add_crumb( get_the_title( $post ), $permalink );
	}

}
