<?php

namespace WeDevs\DokanPro\Modules\RankMath;

defined( 'ABSPATH' ) || exit;

use RankMath\Admin\Metabox\Post_Screen;

/**
 * Class for post screen handler
 *
 * @since 3.4.0
 */
class PostScreen extends Post_Screen {

    /**
     * Class constructor
     *
     * @since 3.4.0
     */
    public function __construct() {

        /**
         * At a point the function `get_sample_permalink`
         * is being used by a private method of the parent class.
         *
         * But as the `get_sample_permalink` function works
         * only within the admin panel, we need to require
         * the file that contains the function as we need to use
         * the function on the frontend for Vendor Dashboard.
         *
         * Note that we cannot override the method that is using
         * the function as the method is private in the parent class.
         */
        if ( ! function_exists( 'get_sample_permalink' ) ) {
            require_once ABSPATH . 'wp-admin/includes/post.php';
        }

        parent::__construct();
    }

    /**
     * Retrieves object id
     *
     * @since 3.4.0
     *
     * @return int
     */
    public function get_object_id() {
        return ! empty( $_GET['product_id'] ) ? absint( wp_unslash( $_GET['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }
}
