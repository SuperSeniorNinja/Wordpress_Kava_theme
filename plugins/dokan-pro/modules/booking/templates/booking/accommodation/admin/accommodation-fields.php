<?php
/**
 * Dokan accommodation booking tab panel
 *
 * @since 3.4.2
 *
 * @package DokanPro
 */

?>
<div id="dokan_accommodation_fields">
    <?php
    woocommerce_wp_text_input(
        [
            'id'            => '_dokan_accommodation_checkin_time',
            'value'         => get_post_meta( get_the_ID(), '_dokan_accommodation_checkin_time', true ),
            'label'         => __( 'Check-in time', 'dokan' ),
            'class'         => 'dokan-accommodation-timepicker',
            'description'   => __( 'Booking check-in time', 'dokan' ),
            'wrapper_class' => 'show_if_booking',
        ]
    );

    woocommerce_wp_text_input(
        [
            'id'            => '_dokan_accommodation_checkout_time',
            'value'         => get_post_meta( get_the_ID(), '_dokan_accommodation_checkout_time', true ),
            'label'         => __( 'Check-out time', 'dokan' ),
            'class'         => 'dokan-accommodation-timepicker',
            'description'   => __( 'Booking check-out time', 'dokan' ),
            'wrapper_class' => 'show_if_booking',
        ]
    );

    wp_nonce_field( 'dokan_accommodation_fields_save', 'dokan_accommodation_nonce' );
    ?>
</div>
