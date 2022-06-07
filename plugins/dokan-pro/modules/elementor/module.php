<?php

namespace WeDevs\DokanPro\Modules\Elementor;

final class Module {

    /**
     * Module version
     *
     * @since 2.9.11
     *
     * @var string
     */
    public $version = '2.9.11';

    /**
     * Exec after first instance has been created
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ], 99 );
    }

    /**
     * Load module
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function init() {
        $dependency = new DependencyNotice();

        // Check if dependencies are not missing.
        if ( ! $dependency->is_missing_dependency() ) {
            $this->define_constants();
            $this->instances();
        }
    }

    /**
     * Module constants
     *
     * @since 2.9.11
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_ELEMENTOR_VERSION', $this->version );
        define( 'DOKAN_ELEMENTOR_FILE', __FILE__ );
        define( 'DOKAN_ELEMENTOR_PATH', dirname( DOKAN_ELEMENTOR_FILE ) );
        define( 'DOKAN_ELEMENTOR_INCLUDES', DOKAN_ELEMENTOR_PATH . '/includes' );
        define( 'DOKAN_ELEMENTOR_URL', plugins_url( '', DOKAN_ELEMENTOR_FILE ) );
        define( 'DOKAN_ELEMENTOR_ASSETS', DOKAN_ELEMENTOR_URL . '/assets' );
        define( 'DOKAN_ELEMENTOR_VIEWS', DOKAN_ELEMENTOR_PATH . '/views' );
    }

    /**
     * Create module related class instances
     *
     * @since 2.9.11
     *
     * @return void
     */
    private function instances() {
        \WeDevs\DokanPro\Modules\Elementor\Templates::instance();
        \WeDevs\DokanPro\Modules\Elementor\StoreWPWidgets::instance();
        \WeDevs\DokanPro\Modules\Elementor\Bootstrap::instance();
    }

    /**
     * Elementor\Plugin instance
     *
     * @since 2.9.11
     *
     * @return \Elementor\Plugin
     */
    public function elementor() {
        return \Elementor\Plugin::instance();
    }

    /**
     * Is editing or preview mode running
     *
     * @since 2.9.11
     *
     * @return bool
     */
    public function is_edit_or_preview_mode() {
        $is_edit_mode    = $this->elementor() ? $this->elementor()->editor->is_edit_mode() : null;
        $is_preview_mode = $this->elementor() ? $this->elementor()->preview->is_preview_mode() : null;

        $get_data = wp_unslash( $_REQUEST ); // phpcs:ignore

        if ( empty( $is_edit_mode ) && empty( $is_preview_mode ) ) {
            if ( ! empty( $get_data['action'] ) && ! empty( $get_data['editor_post_id'] ) ) {
                $is_edit_mode = true;
            } elseif ( ! empty( $get_data['preview'] ) && $get_data['preview'] && ! empty( $get_data['theme_template_id'] ) ) {
                $is_preview_mode = true;
            }
        }

        if ( $is_edit_mode || $is_preview_mode ) {
            return true;
        }

        return false;
    }

    /**
     * Default dynamic store data for widgets
     *
     * @since 2.9.11
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function get_store_data( $prop = null ) {
        $store_data = \WeDevs\DokanPro\Modules\Elementor\StoreData::instance();

        return $store_data->get_data( $prop );
    }

    /**
     * Social network name mapping to elementor icon names
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_social_networks_map() {
        $map = [
            'fb'        => 'fab fa-facebook',
            'twitter'   => 'fab fa-twitter',
            'pinterest' => 'fab fa-pinterest',
            'linkedin'  => 'fab fa-linkedin',
            'youtube'   => 'fab fa-youtube',
            'instagram' => 'fab fa-instagram',
            'flickr'    => 'fab fa-flickr',
        ];

        return apply_filters( 'dokan_elementor_social_network_map', $map );
    }
}
