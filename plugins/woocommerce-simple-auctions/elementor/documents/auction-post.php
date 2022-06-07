<?php
namespace ElementorPro\Modules\Wsa_Woocommerce\Documents;

use Elementor\Core\DocumentTypes\Post;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Auction_Post extends Post {

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['cpt'] = [
			'product',
		];

		return $properties;
	}

	/**
	 * @since  2.0.0
	 * @access public
	 */
	public function get_name() {
		return 'product-post';
	}

	/**
	 * @since  2.0.0
	 * @access public
	 * @static
	 */
	public static function get_title() {
		return esc_html__( 'Auction Post', 'elementor-pro' );
	}

	protected static function get_editor_panel_categories() {
		$categories = parent::get_editor_panel_categories();

		unset( $categories['theme-elements-single'] );

		$categories = Utils::array_inject(
			$categories,
			'theme-elements',
			[
				'woocommerce-elements-single' => [
					'title' => esc_html__( 'Product', 'elementor-pro' ),
					'active' => false,
				],
			]
		);

		return $categories;
	}

	protected function get_remote_library_config() {
		$config = parent::get_remote_library_config();

		$config['category'] = 'single product auction';

		return $config;
	}
}
