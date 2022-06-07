<?php

namespace Texty\Admin;

/**
 * Menu Class
 */
class Menu {

    /**
     * Initialize
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    /**
     * Register admin menu
     *
     * @return void
     */
    public function register_menu() {
        $position = apply_filters( 'texty_menu_position', 58 );

        $menu = add_menu_page(
            __( 'Texty', 'texty' ),
            __( 'Texty', 'texty' ),
            'manage_options',
            'texty',
            [ $this, 'render_page' ],
            'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="#a0a5aa" d="M10.52 1c.603 0 1.191.056 1.761.164a5.358 5.358 0 00-1.1 2.754 6.538 6.538 0 00-5.737 10.626l.629.773-.966 1.645h5.414a6.538 6.538 0 006.511-7.138 5.355 5.355 0 002.764-1.075 9.423 9.423 0 01-9.275 11.097H0l2.602-4.314-.013-.02A9.423 9.423 0 0110.521 1zm6.018 0a3.462 3.462 0 110 6.923 3.462 3.462 0 010-6.923z"/></svg>' ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
            $position
        );

        add_action( 'admin_print_scripts-' . $menu, [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Render the page
     *
     * @return void
     */
    public function render_page() {
        echo '<div id="texty-app"></div>';
    }

    /**
     * Enqueue JS and CSS
     *
     * @return void
     */
    public function enqueue_scripts() {
        $assets = [
            'version'      => TEXTY_VERSION,
            'dependencies' => [
                'wp-api-fetch',
                'wp-i18n',
            ],
        ];

        $url = TEXTY_URL . '/dist';

        // for local development
        // when webpack "hot module replacement" is enabled, this
        // constant needs to be turned "true" on "wp-config.php"
        if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
            $url = 'http://localhost:8080';
        }

        // register scripts
        wp_register_script( 'texty-runtime', $url . '/runtime.js', $assets['dependencies'], $assets['version'], true );
        wp_register_script( 'texty-vendors', $url . '/vendors.js', [ 'texty-runtime' ], $assets['version'], true );
        wp_register_script( 'texty-admin', $url . '/app.js', [ 'texty-vendors' ], $assets['version'], true );
        wp_localize_script( 'texty-admin', 'texty', $this->localize_script() );

        // register styles
        wp_register_style( 'texty-vendors-css', $url . '/vendors.css', [ 'wp-components' ], $assets['version'] );
        wp_register_style( 'texty-css', $url . '/app.css', [ 'texty-vendors-css' ], $assets['version'] );

        // enqueue scripts and styles
        wp_enqueue_script( 'texty-admin' );
        wp_enqueue_style( 'texty-css' );
    }

    /**
     * Get the localize script
     *
     * @return array
     */
    public function localize_script() {
        $i18n = [];

        return apply_filters( 'texty_localize_script', $i18n );
    }
}
