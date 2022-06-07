<?php

namespace WeDevs\DokanPro\Modules\RankMath;

defined( 'ABSPATH' ) || exit;

use RankMath\Rest\Rest_Helper;

/**
 * Class for Rank math SEO integration module
 *
 * @since 3.4.0
 */
class Module {

    /**
     * Class constructor
     *
     * @since 3.4.0
     */
    public function __construct() {
        // Define constants
        $this->constants();

        $dependency = new DependencyNotice();

        if ( $dependency->is_missing_dependency() ) {
            return;
        }

        // Check if current user has the permission to edit product
        if ( ! current_user_can( 'dokan_edit_product' ) ) {
            return;
        }

        // Check if rank math setup is completed
        if ( rank_math()->registration->invalid ) {
            return;
        }

        // Initialize the module
        add_action( 'init', array( $this, 'hooks' ) );
    }

    /**
     * Defines the required constants
     *
     * @since 3.4.0
     *
     * @return void
     */
    private function constants() {
        define( 'DOKAN_RANK_MATH_FILE', __FILE__ );
        define( 'DOKAN_RANK_MATH_PATH', dirname( DOKAN_RANK_MATH_FILE ) );
        define( 'DOKAN_RANK_MATH_TEMPLATE_PATH', dirname( DOKAN_RANK_MATH_FILE ) . '/templates/' );
    }

    /**
     * Registers required hooks
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function hooks() {
        // Map meta cap for `vendor_staff` to bypass some primitive capability requirements.
        add_filter( 'map_meta_cap', array( $this, 'map_meta_cap_for_rank_math' ), 10, 4 );
        // Load SEO content after inventory variants widget on the edit product page
        add_action( 'dokan_product_edit_after_inventory_variants', array( $this, 'load_product_seo_content' ), 6, 2 );
        // Initiates rest api
        add_action( 'rest_api_init', array( $this, 'init_rest_api' ) );
    }

    /**
     * Maps meta cap for users with vendor staff role to bypass some primitive
     * capability requirements.
     *
     * To access the rank math rest api functionality, a user must have one or some
     * primitive capabilities which are `edit_products`, `edit_published_products`,
     * `edit_others_products`, and `edit_private_products`
     *
     * Often users with `vendor_staff` role miss those required capabilities that
     * would lead them to being unable to update the product although they are given
     * permission to edit product.
     *
     * So to ensure their ability to update product and to use the Rank Math SEO
     * functionalities, the required premitive capabilities are bypassed.
     *
     * Note that it is ensured the capabilities will be bypassed only while
     * the rest api endpoint for Rank Math SEO is being hit.
     *
     * Also for rank math redirection settings, all users need to have the
     * capability of `rank_math_redirections`. So it needs to be ensured all users
     * are given that capability while updating the rank math redirection settings
     * for products.
     *
     * @since 3.4.0
     *
     * @uses global   $wp          Used to retrieve \WP class data
     * @uses function get_userdata Used to retrieve userdata by id
     *
     * @param array   $caps    Premitive capabilities that must be possessed by user
     * @param string  $cap     Capability that is mapping the premitive capabilities
     * @param integer $user_id ID of the current user
     *
     * @return array List of premitive capabilities to be satisfied
     */
    public function map_meta_cap_for_rank_math( $caps, $cap, $user_id, $args ) {
        switch ( $cap ) {
            case 'edit_others_products':
                global $wp;

                if (
                    empty( $wp->query_vars['rest_route'] ) ||
                    false === strpos( $wp->query_vars['rest_route'], Rest_Helper::BASE )
                ) {
                    return $caps;
                }

                /*
                 * Here the userdata is being retrieved
                 * to get all capabilities of the user
                 * in order to check specific capability
                 * like `vendor_staff`.
                 */
                $user = get_userdata( $user_id );

                // Bypass the primitive caps only if the user is `vendor_staff`
                if ( ! empty( $user->allcaps['vendor_staff'] ) ) {
                    return array();
                }

                break;

            default:
                if ( 0 !== strpos( $cap, 'rank_math_' ) ) {
                    break;
                }

                /*
                 * For Redirection user need to have the capability
                 * of `rank_math_redirections`. So here the users
                 * who can edit dokan products are given that
                 * capability so that they can edit redierct settings.
                 */
                add_filter(
                    'user_has_cap', function( $all_caps ) use ( $cap ) {
                        $all_caps[ $cap ] = true;
                        return $all_caps;
                    }, 10, 1
                );
        }

        return $caps;
    }

    /**
     * Loads rank math seo content for product update
     *
     * @since 3.4.0
     *
     * @param object $product
     * @param int $product_id
     *
     * @return void
     */
    public function load_product_seo_content( $product, $product_id ) {

        /*
         * Process the required functionality
         * for frontend application including
         * all the styles and scripts
         */
        $frontend = new Frontend();
        $frontend->process();

        // Require the template for rank math seo content
        require_once DOKAN_RANK_MATH_TEMPLATE_PATH . 'product-seo-content.php';
    }

    /**
     * Registers necessary rest routes.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function init_rest_api() {
        $rest = new \RankMath\ContentAI\Rest();
        $rest->register_routes();
    }
}
