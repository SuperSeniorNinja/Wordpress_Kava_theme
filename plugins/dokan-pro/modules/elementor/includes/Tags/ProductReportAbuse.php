<?php

namespace WeDevs\DokanPro\Modules\Elementor\Tags;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\TagBase;

class ProductReportAbuse extends TagBase {

    /**
     * Class constructor
     *
     * @since 3.3.0
     *
     * @param array $data
     */
    public function __construct( $data = [] ) {
        parent::__construct( $data );
    }

    /**
     * Tag name
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-product-report-abuse';
    }

    /**
     * Tag title
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Report Abuse', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function render() {
    }
}
