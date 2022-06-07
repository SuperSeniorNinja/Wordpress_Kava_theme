<?php
/**
 * Dokan Product CSV Importer
 *
 * @since 3.3.3
 * @package WeDevs\DokanPro
 * @author WeDevs
 */

namespace WeDevs\DokanPro\Modules\ExIm\Import;

use WC_Product;
use WC_Product_CSV_Importer;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_Product_CSV_Importer', false ) ) {
    include_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';
}

/**
 * Class ProductCsvImporter
 *
 * Dokan Product CSv Importer from vendor dashboard.
 *
 * @package WeDevs\DokanPro\Modules\ExIm\Import
 * @since 3.3.3
 * @use WC_Product_CSV_Importer
 */
class ProductCsvImporter extends WC_Product_CSV_Importer {

    /**
     * ProductCsvImporter constructor.
     *
     * @param $file
     * @param array $params
     */
    public function __construct( $file, $params = array() ) {
        parent::__construct( $file, $params );
    }

    /**
     * Parse a field that is generally '1' or '0' but can be something else.
     *
     * We are overriding this method to give support for the value
     * Draft or Pending Review (-1)
     *
     * @param string $value Field value.
     *
     * @return bool|string
     */
    public function parse_bool_field( $value ) {
        if ( '0' === $value || '-1' === $value ) {
            return false;
        }

        if ( '1' === $value ) {
            return true;
        }

        // Don't return explicit true or false for empty fields or values like 'notify'.
        return wc_clean( $value );
    }

    /**
     * Process importer.
     *
     * Do not import products with IDs or SKUs that already exist if option
     * update existing is false, and likewise, if updating products, do not
     * process rows which do not exist if an ID/SKU is provided.
     *
     * @return array
     */
    public function import() {
        $this->start_time = time();
        $index            = 0;
        $update_existing  = $this->params['update_existing'];
        $data             = array(
            'imported' => array(),
            'failed'   => array(),
            'updated'  => array(),
            'skipped'  => array(),
        );

        foreach ( $this->parsed_data as $parsed_data_key => $parsed_data ) {
            do_action( 'woocommerce_product_import_before_import', $parsed_data );

            $id         = isset( $parsed_data['id'] ) ? absint( $parsed_data['id'] ) : 0;
            $sku        = isset( $parsed_data['sku'] ) ? $parsed_data['sku'] : '';
            $id_exists  = false;
            $sku_exists = false;

            if ( $id ) {
                $product   = wc_get_product( $id );
                $id_exists = $product && 'importing' !== $product->get_status();
            }

            if ( $id_exists && ! $this->is_my_product( $product ) ) {
                unset( $parsed_data['id'] );
                $id_exists = false;
            }

            if ( $sku ) {
                $id_from_sku = wc_get_product_id_by_sku( $sku );
                $product     = $id_from_sku ? wc_get_product( $id_from_sku ) : false;
                $sku_exists  = $product && 'importing' !== $product->get_status();
            }

            if ( $sku_exists && ! $this->is_my_product( $product ) ) {
                $data['skipped'][] = new WP_Error(
                    'woocommerce_product_importer_error',
                    __( 'A product with this SKU already exists in another vendor.', 'dokan' ),
                    array(
                        'sku' => $sku,
                        'row' => $this->get_row_id( $parsed_data ),
                    )
                );
                continue;
            }

            if ( $id_exists && ! $update_existing ) {
                $data['skipped'][] = new WP_Error(
                    'woocommerce_product_importer_error',
                    esc_html__( 'A product with this ID already exists.', 'dokan' ),
                    array(
                        'id'  => $id,
                        'row' => $this->get_row_id( $parsed_data ),
                    )
                );
                continue;
            }

            if ( $sku_exists && ! $update_existing ) {
                $data['skipped'][] = new WP_Error(
                    'woocommerce_product_importer_error',
                    esc_html__( 'A product with this SKU already exists.', 'dokan' ),
                    array(
                        'sku' => esc_attr( $sku ),
                        'row' => $this->get_row_id( $parsed_data ),
                    )
                );
                continue;
            }

            if ( $update_existing && ( isset( $parsed_data['id'] ) || isset( $parsed_data['sku'] ) ) && ! $id_exists && ! $sku_exists ) {
                $data['skipped'][] = new WP_Error(
                    'woocommerce_product_importer_error',
                    esc_html__( 'No matching product exists to update.', 'dokan' ),
                    array(
                        'id'  => $id,
                        'sku' => esc_attr( $sku ),
                        'row' => $this->get_row_id( $parsed_data ),
                    )
                );
                continue;
            }

            $result = $this->process_item( $parsed_data );

            if ( is_wp_error( $result ) ) {
                $result->add_data( array( 'row' => $this->get_row_id( $parsed_data ) ) );
                $data['failed'][] = $result;
            } elseif ( $result['updated'] ) {
                $data['updated'][] = $result['id'];
            } else {
                $data['imported'][] = $result['id'];
            }

            $index ++;

            if ( $this->params['prevent_timeouts'] && ( $this->time_exceeded() || $this->memory_exceeded() ) ) {
                $this->file_position = $this->file_positions[ $index ];
                break;
            }
        }

        return $data;
    }

    /**
     * Check if the product is from my store
     *
     * @param int|WC_Product $product
     *
     * @return bool
     */
    private function is_my_product( $product ) {
        return dokan_get_current_user_id() === dokan_get_vendor_by_product( $product )->get_id();
    }
}
