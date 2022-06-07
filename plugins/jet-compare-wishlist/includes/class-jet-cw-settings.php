<?php
/**
 * Class Compare & Wishlist Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Settings' ) ) {

	/**
	 * Define Jet_CW_Settings class
	 */
	class Jet_CW_Settings {

		/**
		 * Holds settings key
		 *
		 * @var string
		 */
		public $key = 'jet-cw-settings';

		/**
		 * Holds settings
		 *
		 * @var null
		 */
		public $settings = null;

		/**
		 * Init page
		 */
		public function __construct() {
		}

		/**
		 * Returns localize plugin settings data
		 *
		 * @return array
		 */
		public function get_localize_data() {

			$available_widgets      = [];
			$default_active_widgets = [];

			foreach ( glob( jet_cw()->plugin_path( 'includes/widgets/compare/' ) . '*.php' ) as $file ) {
				$data = get_file_data( $file, array( 'class' => 'Class', 'name' => 'Name', 'slug' => 'Slug' ) );

				$slug = basename( $file, '.php' );

				$available_widgets[] = array(
					'label' => $data['name'],
					'value' => $slug,
				);

				$default_active_widgets[ $slug ] = 'true';
			}

			foreach ( glob( jet_cw()->plugin_path( 'includes/widgets/wishlist/' ) . '*.php' ) as $file ) {
				$data = get_file_data( $file, array( 'class' => 'Class', 'name' => 'Name', 'slug' => 'Slug' ) );

				$slug = basename( $file, '.php' );

				$available_widgets[] = array(
					'label' => $data['name'],
					'value' => $slug,
				);

				$default_active_widgets[ $slug ] = 'true';
			}

			$pages     = [];
			$get_pages = get_pages( 'hide_empty=0' );

			foreach ( $get_pages as $page ) {
				$pages[] = array(
					'label' => esc_attr( $page->post_title ),
					'value' => $page->ID,
				);
			}

			$store_types = [
				[
					'label' => __( 'Session', 'jet-cw' ),
					'value' => 'session',
				],
				[
					'label' => __( 'Cookies', 'jet-cw' ),
					'value' => 'cookies',
				],
			];

			$compare_page_page_items_list = array(
				array(
					'label' => __( '2 item', 'jet-cw' ),
					'value' => 2,
				),
				array(
					'label' => __( '3 item', 'jet-cw' ),
					'value' => 3,
				),
				array(
					'label' => __( '4 item', 'jet-cw' ),
					'value' => 4,
				),
			);

			$rest_api_url = apply_filters( 'jet-cw/rest/frontend/url', get_rest_url() );

			return array(
				'messages'       => array(
					'saveSuccess' => esc_html__( 'Saved', 'jet-cw' ),
					'saveError'   => esc_html__( 'Error', 'jet-cw' ),
				),
				'settingsApiUrl' => $rest_api_url . 'jet-cw-api/v1/plugin-settings',
				'settingsData'   => array(
					'avaliable_widgets'           => array(
						'value'   => $this->get( 'avaliable_widgets', $default_active_widgets ),
						'options' => $available_widgets,
					),
					'enable_compare'              => array(
						'value' => $this->get( 'enable_compare', true ),
					),
					'compare_store_type'          => [
						'value'   => $this->get( 'compare_store_type' ),
						'options' => $store_types,
					],
					'save_user_compare_list'      => array(
						'value' => $this->get( 'save_user_compare_list', true ),
					),
					'compare_page'                => array(
						'value'   => $this->get( 'compare_page' ),
						'options' => $pages,
					),
					'compare_page_max_items'      => array(
						'value'   => $this->get( 'compare_page_max_items', 2 ),
						'options' => $compare_page_page_items_list,
					),
					'add_default_compare_button'  => array(
						'value' => $this->get( 'add_default_compare_button', true ),
					),
					'enable_wishlist'             => array(
						'value' => $this->get( 'enable_wishlist', true ),
					),
					'wishlist_store_type'         => [
						'value'   => $this->get( 'wishlist_store_type' ),
						'options' => $store_types,
					],
					'save_user_wish_list'         => array(
						'value' => $this->get( 'save_user_wish_list' ),
					),
					'wishlist_page'               => array(
						'value'   => $this->get( 'wishlist_page' ),
						'options' => $pages,
					),
					'add_default_wishlist_button' => array(
						'value' => $this->get( 'add_default_wishlist_button' ),
					),
				),
			);

		}

		/**
		 * Return settings page URL
		 *
		 * @return string
		 */
		public function get_settings_page_link() {
			return add_query_arg(
				array(
					'page' => $this->key,
				),
				esc_url( admin_url( 'admin.php' ) )
			);
		}

		/**
		 * [get description]
		 *
		 * @param         $setting
		 * @param boolean $default
		 *
		 * @return bool|mixed
		 */
		public function get( $setting = '', $default = false ) {

			if ( null === $this->settings ) {
				$this->settings = get_option( $this->key, array() );
			}

			return isset( $this->settings[ $setting ] ) ? $this->settings[ $setting ] : $default;

		}

	}

}

