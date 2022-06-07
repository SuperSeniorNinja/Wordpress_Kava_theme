<?php
namespace WeDevs\DokanPro\Modules\ProductAdvertisement\Frontend;

use WeDevs\Dokan\ProductSections\AbstractProductSection;
use WeDevs\DokanPro\Modules\ProductAdvertisement\Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'AbstractProductSection' ) ) {
    return;
}

/**
 * Top rated products section class.
 *
 * For displaying top rated products section to single store page.
 *
 * @since 3.5.0
 *
 * @package dokan
 */
class ProductSection extends AbstractProductSection {

    /**
     * Set unique section id for the this section.
     *
     * @since 3.5.0
     *
     * @return void
     */
    protected function set_section_id() {
        $this->section_id = 'advertised';
    }

    /**
     * Get single store page section title.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public function get_section_title() {
        $sections_appearance = dokan_get_option( 'product_sections', 'dokan_appearance' );
        $section_title       = isset( $sections_appearance[ $this->get_section_id() . '_title' ] ) ? $sections_appearance[ $this->get_section_id() . '_title' ] : __( 'Popular Products', 'dokan' );

        return apply_filters( "dokan_{$this->get_section_id()}_product_section_title", $section_title );
    }

    /**
     * Get single store page section title.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public function get_section_label() {
        return __( 'Advertising Products', 'dokan' );
    }

    /**
     * Get vendor store settings page section label.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public function get_setting_label() {
        return __( 'Show advertised products section', 'dokan' );
    }

    /**
     * Get section products.
     *
     * @since 3.5.0
     *
     * @param int $vendor_id
     *
     * @return \WP_Query
     */
    public function get_products( $vendor_id ) {
        // get advertisements from database
        $args = [
            'vendor_id' => $vendor_id,
            'count'     => $this->item_count,
        ];

        $manager  = new Manager();
        $products = $manager->get_advertisement_for_display( $args );

        return $products;
    }
}
