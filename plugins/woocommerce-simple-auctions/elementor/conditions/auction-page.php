<?php
namespace ElementorPro\Modules\Wsa_Woocommerce\Conditions;

use ElementorPro\Modules\ThemeBuilder as ThemeBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Auction_Page extends ThemeBuilder\Conditions\Condition_Base {

	public static function get_type() {
		return 'singular';
	}

	public function get_name() {
		return 'Auction_page';
	}

	public static function get_priority() {
		return 40;
	}

	public function get_label() {
		return esc_html__( 'Auction Page', 'elementor-pro' );
	}

	public function check( $args ) {
		return is_shop();
	}
}
