<?php

namespace WeDevs\DokanPro\Modules\RankMath;

use RankMath\Admin\Assets as RankMathAssets;

defined( 'ABSPATH' ) || exit;

/**
 * Asset manager class
 *
 * @since 3.4.0
 */
class Assets extends RankMathAssets {

    /**
     * Class constructor
     *
     * @since 3.4.0
     */
    public function __construct() {
        $this->run_hooks();
        $this->register();
        $this->enqueue();
        $this->overwrite_wplink();
    }

    /**
     * Initializes required hooks.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function run_hooks() {
        do_action( 'rank_math/admin/before_editor_scripts' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
    }

    /**
     * Enqueues required styles and scripts
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function enqueue() {
        $styles  = $this->get_styles();
        $scripts = $this->get_scripts();

        foreach ( $styles as $style ) {
            wp_enqueue_style( $style );
        }

        foreach ( $scripts as $script ) {
            wp_enqueue_script( $script );
        }
    }

    /**
     * Get all registered styles
     *
     * @since 3.4.0
     *
     * @return array
     */
    private function get_styles() {
        return array(
            parent::PREFIX . 'common',
            parent::PREFIX . 'post-metabox',
            parent::PREFIX . 'cmb2',
            'wp-components',
        );
    }

    /**
     * Get all registered scripts
     *
     * @since 3.4.0
     *
     * @return array
     */
    private function get_scripts() {
        return array(
            parent::PREFIX . 'common',
            parent::PREFIX . 'analyzer',
            parent::PREFIX . 'validate',
            'clipboard',
            'wp-hooks',
            'wp-date',
            'wp-data',
            'wp-api-fetch',
            'wp-components',
            'wp-element',
            'wp-i18n',
            'wp-url',
            'wp-media-utils',
            'wp-url',
            'wp-block-editor',
        );
    }
}
