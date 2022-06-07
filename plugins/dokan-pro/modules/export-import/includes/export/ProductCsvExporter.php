<?php
namespace WeDevs\DokanPro\Modules\ExIm\Export;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_Product_CSV_Exporter', false ) ) {
    include_once WC_ABSPATH . 'includes/export/class-wc-product-csv-exporter.php';
}

class ProductCsvExporter extends \WC_Product_CSV_Exporter {
    /**
     * Dokan_Product_CSV_Exporter constructor.
     */
    public function __construct() {
        add_filter('woocommerce_product_export_product_query_args', array( $this, 'only_export_current_vendors_product' ) );
        add_filter('woocommerce_product_export_product_variation_query_args', array( $this, 'only_export_current_vendors_product' ) );
        parent::__construct();
    }

    /**
     * Add author filter in product query.
     *
     * @since 3.3.3
     * @param $args
     *
     * @return array
     */
    public function only_export_current_vendors_product( $args ) {
        $args['author'] = dokan_get_current_user_id();
        return $args;
    }

    /**
     * Product types to export.
     *
     * @since 3.3.3
     * @param array $product_types_to_export
     */
    public function set_product_types_to_export( $product_types_to_export ) {
        $variations_with_variable_key = array_search( 'variable-variation', $product_types_to_export, true );
        if ( false !== $variations_with_variable_key  ) {
            $product_types_to_export[ $variations_with_variable_key ] = 'variable';
        }

        if ( false !== $variations_with_variable_key  && false === array_search( 'variation', $product_types_to_export, true ) ) {
            $product_types_to_export[] = 'variation';
        }

        $this->product_types_to_export = array_map( 'wc_clean', $product_types_to_export );
    }
}
