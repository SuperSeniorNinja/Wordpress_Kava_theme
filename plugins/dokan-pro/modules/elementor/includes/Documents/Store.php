<?php

namespace WeDevs\DokanPro\Modules\Elementor\Documents;

use ElementorPro\Modules\ThemeBuilder\Documents\Single;

class Store extends Single {

    /**
     * Class constructor
     *
     * @since 2.9.11
     *
     * @param array $data
     *
     * @return void
     */
    public function __construct( $data = [] ) {
        parent::__construct( $data );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
    }

    /**
     * Enqueue document related scripts
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'dokan-elementor-doc-store',
            DOKAN_ELEMENTOR_ASSETS . '/css/dokan-elementor-document-store.css',
            [],
            DOKAN_ELEMENTOR_VERSION
        );
    }

    /**
     * Document properties
     *
     * @since 2.9.11
     *
     * @return array
     */
    public static function get_properties() {
        $properties = parent::get_properties();

        $properties['location']       = 'single';
        $properties['condition_type'] = 'general';

        return $properties;
    }

    /**
     * Document name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'store';
    }

    /**
     * Document title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public static function get_title() {
        return __( 'Single Store', 'dokan' );
    }

    /**
     * Elementor builder panel categories
     *
     * @since 2.9.11
     *
     * @return array
     */
    protected static function get_editor_panel_categories() {
        $categories = [
            'dokan-store-elements-single' => [
                'title'  => __( 'Store', 'dokan' ),
                'active' => true,
            ],
        ];

        $categories += parent::get_editor_panel_categories();

        return $categories;
    }

    /**
     * Document library type
     *
     * @since 2.9.11
     * @since 2.9.13 From elementor pro v2.4.0 it is deprecated
     *
     * @return string
     */
    public function get_remote_library_type() {
        return 'single store';
    }

    /**
     * Remote library config
     *
     * From elementor pro v2.4.0 `get_remote_library_config` is used
     * instead of `get_remote_library_type`
     *
     * @since 2.9.13
     *
     * @return array
     */
    public function get_remote_library_config() {
        $config = parent::get_remote_library_config();

        $config['category'] = 'single store';

        return $config;
    }
}
