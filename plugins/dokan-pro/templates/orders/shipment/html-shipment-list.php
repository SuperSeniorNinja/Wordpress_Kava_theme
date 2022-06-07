<div class="shipping-status-tracking-shippments-inner shipment_id_<?php echo esc_attr( $shipment_id ); ?>">
    <div class="shippments-tracking-header">
        <h4 class="shippments-tracking-title">
            <strong><?php esc_html_e( 'Shipment', 'dokan' ); ?> #<?php echo esc_html( $incre ); ?> </strong>
        </h4>
        <p class="shippments-tracking-status">
            <strong class="<?php echo esc_attr( $shipping_status ); ?> status_label_<?php echo esc_attr( $shipment_id ); ?>"><?php echo esc_html( $status ); ?> </strong>

            <span class="shipment-item-details-tab-toggle" data-shipment_id="<?php echo esc_attr( $shipment_id ); ?>">
                <span class="fa fa-chevron-down details-tab-toggle-sort-desc"></span>
            </span>
        </p>
        <div class="clear"></div>
        <p class="shippments-tracking-via">
            <?php esc_html_e( 'via', 'dokan' ); ?>
            <strong><?php echo esc_html( $provider ); ?></strong>
            <a href="<?php echo esc_attr( $provider_url ); ?>" target="_blank">
                <span class="fa fa-external-link"></span>
            </a>
            <a href="<?php echo esc_attr( $provider_url ); ?>" target="_blank">
                <?php echo esc_html( $number ); ?>
            </a>
        </p>
    </div>
    <div class="shippments-tracking-items dokan-hide shipment_body_<?php echo esc_attr( $shipment_id ); ?>">
        <?php
        dokan_get_template_part(
            'orders/shipment/html-shipments-tracking-items', '', array(
                'pro'      => true,
                'item_qty' => $item_qty,
            )
        );
        ?>
        <?php if ( $shipment_timeline ) : ?>
            <div class="dokan-customer-shipment-notes-list-area">
                <h5><strong><?php esc_html_e( 'Shipment Updates Timeline', 'dokan' ); ?></strong></h5>
                <span class="shipment-notes-details-tab-toggle" data-shipment_id="<?php echo esc_attr( $shipment_id ); ?>">
                    <span class="fa fa-chevron-down details-tab-toggle-sort-desc"></span>
                </span>
                <div class="customer-shipment-list-notes-inner-area dokan-hide shipment-list-notes-inner-area<?php echo esc_attr( $shipment_id ); ?>">
                    <?php
                        dokan_get_template_part(
                            'orders/shipment/html-shipment-timeline-updates', '', array(
                                'pro'               => true,
                                'order'             => $order,
                                'shipment_id'       => $shipment_id,
                                'shipment_timeline' => $shipment_timeline,
                            )
                        );
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="clear"></div>
