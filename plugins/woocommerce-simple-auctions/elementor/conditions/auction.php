<?php
namespace ElementorPro\Modules\Wsa_Woocommerce\Conditions;

use ElementorPro\Modules\ThemeBuilder as ThemeBuilder;
use ElementorPro\Modules\ThemeBuilder\Conditions\Post;
use ElementorPro\Modules\QueryControl\Module as QueryModule;
use ElementorPro\Modules\ThemeBuilder\Conditions\In_Taxonomy;
use ElementorPro\Modules\ThemeBuilder\Conditions\In_Sub_Term;
use ElementorPro\Modules\ThemeBuilder\Conditions\Post_Type_By_Author;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Auction extends Post {


	public static function get_type() {
		return 'singular';
	}

	public static function get_priority() {
		return 40;
	}

	public function __construct( $data ) {
		$this->post_type = 'product';
		$taxonomies = get_object_taxonomies( 'product', 'objects' );
		$this->post_taxonomies = wp_filter_object_list( $taxonomies, [
			'public' => true,
			'show_in_nav_menus' => true,
		] );

		parent::__construct( $data );
	}

	public function get_name() {
		return 'auction';
	}

	public function get_label() {
		return esc_html__( 'Auctions', 'elementor-pro' );
	}

	public function get_all_label() {
		/* translators: %s: Post type label. */
		return esc_html__( 'All Auctions', 'elementor-pro' );
	}

	public function check( $args ) {
		if ( isset( $args['id'] ) ) {
			$id = (int) $args['id'];
			if ( $id ) {
				return wsa_is_auction() && get_queried_object_id() === $id;
			}
		}

		return wsa_is_auction();
	}

	public function register_sub_conditions() {
		foreach ( $this->post_taxonomies as $slug => $object ) {
			$in_taxonomy = new In_Taxonomy( [
				'object' => $object,
			] );
			$this->register_sub_condition( $in_taxonomy );

			if ( $object->hierarchical ) {
				$in_sub_term = new In_Sub_Term( [
					'object' => $object,
				] );
				$this->register_sub_condition( $in_sub_term );
			}
		}


	}

}
