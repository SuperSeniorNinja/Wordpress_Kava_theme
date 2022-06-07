<?php
namespace ElementorPro\Modules\Wsa_Woocommerce\Conditions;

use ElementorPro\Modules\ThemeBuilder as ThemeBuilder;
use ElementorPro\Modules\Woocommerce\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Wsa_Conditions extends ThemeBuilder\Conditions\Condition_Base {

	public static function get_type() {
		return 'woocommerce_simple_auctions';
	}

	public function get_name() {
		return 'woocommerce_simple_auctions';
	}

	public function get_label() {
		return esc_html__( 'WooCommerce Simple Auctions', 'elementor-pro' );
	}

	public function get_all_label() {
		return esc_html__( 'Entire Shop', 'elementor-pro' );
	}

	public function register_sub_conditions() {
		$auction_archive = new Auction_Archive();
		$auction_single = new Auction( [
			'post_type' => 'product',
		] );

		$this->register_sub_condition( $auction_archive );
		$this->register_sub_condition( $auction_single );

	}

	public function check( $args ) {
		return is_woocommerce() || Module::is_product_search();
	}
}
