<?php
/**
 * Dokan Delivery time form template
 *
 * @since 3.3.0
 * @package DokanPro
 */

$vendor_delivery_time_settings = isset( $vendor_delivery_time_settings ) ? $vendor_delivery_time_settings : [];

$allow_delivery_time_show_option = isset( $vendor_delivery_time_settings['allow_vendor_delivery_time_option'] ) ? $vendor_delivery_time_settings['allow_vendor_delivery_time_option'] : 'off';
$delivery_date_label             = isset( $vendor_delivery_time_settings['delivery_date_label'] ) ? $vendor_delivery_time_settings['delivery_date_label'] : 'Delivery Date';
$delivery_preorder_date          = isset( $vendor_delivery_time_settings['preorder_date'] ) ? $vendor_delivery_time_settings['preorder_date'] : 0;
$delivery_prep_date              = isset( $vendor_delivery_time_settings['delivery_prep_date'] ) ? $vendor_delivery_time_settings['delivery_prep_date'] : 0;
$selected_delivery_days          = isset( $vendor_delivery_time_settings['delivery_day'] ) ? $vendor_delivery_time_settings['delivery_day'] : [];
$delivery_opening_time           = isset( $vendor_delivery_time_settings['opening_time'] ) ? $vendor_delivery_time_settings['opening_time'] : [];
$delivery_closing_time           = isset( $vendor_delivery_time_settings['closing_time'] ) ? $vendor_delivery_time_settings['closing_time'] : [];
$delivery_time_slot_minutes      = isset( $vendor_delivery_time_settings['time_slot_minutes'] ) ? $vendor_delivery_time_settings['time_slot_minutes'] : [];
$delivery_order_per_slot         = isset( $vendor_delivery_time_settings['order_per_slot'] ) ? $vendor_delivery_time_settings['order_per_slot'] : 0;
$enable_delivery_notification    = isset( $vendor_delivery_time_settings['enable_delivery_notification'] ) ? $vendor_delivery_time_settings['enable_delivery_notification'] : 'off';
$vendor_can_override_settings    = isset( $vendor_can_override_settings ) ? $vendor_can_override_settings : 'off';
$all_delivery_days               = isset( $all_delivery_days ) ? $all_delivery_days : [];
$all_delivery_time_slots         = isset( $all_delivery_time_slots ) ? $all_delivery_time_slots : [];

?>

<div class="dokan-delivery-time-wrapper">
    <form id="dokan_delivery_time" method="post" action="" class="dokan-form-horizontal">

        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label"><?php esc_html_e( 'Delivery Support', 'dokan' ); ?></label>
            <div class="dokan-w5 dokan-text-left">
                <div class="checkbox">
                    <label>
                        <input type="hidden" name="delivery_show_time_option" value="off">
                        <input type="checkbox" name="delivery_show_time_option" value="on"<?php checked( $allow_delivery_time_show_option, 'on' ); ?>> <?php esc_html_e( 'Home Delivery', 'dokan' ); ?>
                    </label>

                    <?php
                    /**
                     * @since 3.3.7
                     *
                     * @param array $vendor_delivery_time_settings
                     */
                    do_action( 'dokan_delivery_time_settings_after_delivery_show_time_option', $vendor_delivery_time_settings );
                    ?>
                </div>
            </div>
        </div>

        <?php
        /**
         * @since 3.3.7
         *
         * @param int $id
         * @param array $info
         */
        do_action( 'dokan_delivery_time_settings_after_time_option', $vendor_delivery_time_settings );
        ?>

        <div id="dokan-delivery-time-vendor-settings">
            <?php if ( 'off' === $vendor_can_override_settings ) : ?>
                <div class="overlay"></div>
            <?php endif; ?>

            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="pre_order_date"><?php esc_html_e( 'Delivery blocked buffer', 'dokan' ); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <input id="pre_order_date" min="0" required value="<?php echo esc_attr( $delivery_preorder_date ); ?>" name="preorder_date" placeholder="<?php esc_attr_e( 'Delivery blocked buffer count', 'dokan' ); ?>" class="dokan-form-control" type="number">
                    <span class="dokan-page-help"><?php esc_html_e( 'How many days the delivery date is blocked from current date? 0 for no block buffer', 'dokan' ); ?></span>
                </div>
            </div>

            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label"><?php esc_html_e( 'Delivery day', 'dokan' ); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <div class="checkbox">
                        <?php foreach ( $all_delivery_days as $key => $delivery_day ) : ?>
                            <div>
                                <label>
                                    <input class="delivery-day-checkbox" type="checkbox"
                                        name="delivery_day[<?php echo esc_attr( $key ); ?>]"
                                        data-tag="dokan-delivery-tab-<?php echo esc_attr( $key ); ?>"
                                        value="<?php echo esc_attr( $key ); ?>" <?php checked( $key, isset( $selected_delivery_days[ $key ] ) ? $selected_delivery_days[ $key ] : '' ); ?>> <?php echo esc_html( $delivery_day ); //phpcs:ignore ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="delivery-time-slot-tabs">
                <div class="tab-panels">
                    <ul class="tabs">
                        <?php foreach ( $all_delivery_days as $day_key => $delivery_day ) : ?>
                            <li class="<?php echo ( empty( $selected_delivery_days ) ? 'sunday' : reset( $selected_delivery_days ) ) === $day_key ? 'active' : ''; ?>" rel="dokan-delivery-tab-<?php echo esc_attr( $day_key ); ?>"><?php echo esc_attr( $delivery_day ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php foreach ( $all_delivery_days as $day_key => $delivery_day ) : ?>
                        <div id="dokan-delivery-tab-<?php echo esc_attr( $day_key ); ?>" class="panel <?php echo ( empty( $selected_delivery_days ) ? 'sunday' : reset( $selected_delivery_days ) ) === $day_key ? 'active' : ''; ?>">
                            <div class="dokan-form-group">
                                <label class="dokan-w3 dokan-control-label" for="delivery_opening_time[<?php echo esc_html( $day_key ); ?>]"><?php esc_html_e( 'Opening time', 'dokan' ); ?></label>
                                <select name="delivery_opening_time[<?php echo esc_attr( $day_key ); ?>]" id="delivery_opening_time[<?php echo esc_attr( $day_key ); ?>]" class="dokan-w5 dokan-text-left dokan-form-control">
                                    <option disabled="disabled" selected value=""><?php esc_html_e( 'Select opening time', 'dokan' ); ?></option>
                                    <?php foreach ( $all_delivery_time_slots as $key => $time_slot ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php isset( $delivery_opening_time[ $day_key ] ) ? selected( $delivery_opening_time[ $day_key ], $key ) : ''; ?>><?php echo esc_html( $time_slot ); ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="dokan-form-group">
                                <label class="dokan-w3 dokan-control-label" for="<?php echo 'delivery_closing_time[' . $day_key . ']'; ?>"><?php esc_html_e( 'Closing time', 'dokan' ); ?></label>
                                <select name="delivery_closing_time[<?php echo esc_attr( $day_key ); ?>]" id="delivery_closing_time[<?php echo esc_attr( $day_key ); ?>]" class="dokan-w5 dokan-text-left dokan-form-control">
                                    <option disabled="disabled" selected value=""><?php esc_html_e( 'Select closing time', 'dokan' ); ?></option>
                                    <?php foreach ( $all_delivery_time_slots as $key => $time_slot ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php isset( $delivery_closing_time[ $day_key ] ) ? selected( $delivery_closing_time[ $day_key ], $key ) : ''; ?>><?php echo esc_html( $time_slot ); ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="dokan-form-group">
                                <label class="dokan-w3 dokan-control-label" for="delivery_time_slot-<?php echo esc_attr( $day_key ); ?>"><?php esc_html_e( 'Time slot', 'dokan' ); ?></label>
                                <div class="dokan-w5 dokan-text-left">
                                    <input id="delivery_time_slot-<?php echo esc_attr( $day_key ); ?>" value="<?php echo isset( $delivery_time_slot_minutes[ $day_key ] ) ? esc_attr( $delivery_time_slot_minutes[ $day_key ] ) : 0; ?>" name="delivery_time_slot[<?php echo esc_attr( $day_key ); ?>]" placeholder="<?php esc_attr_e( 'Time slot', 'dokan' ); ?>" class="dokan-form-control" type="number">
                                    <span class="dokan-page-help"><?php esc_html_e( 'Time slot in minutes. Please keep opening and closing time divisible by slot minutes. E.g ( 30, 60, 120 )', 'dokan' ); ?></span>
                                </div>
                            </div>

                            <div class="dokan-form-group">
                                <label class="dokan-w3 dokan-control-label" for="<?php echo 'order_per_slot-' . $day_key; ?>"><?php esc_html_e( 'Order per slot', 'dokan' ); ?></label>
                                <div class="dokan-w5 dokan-text-left">
                                    <input id="order_per_slot-<?php echo esc_attr( $day_key ); ?>" value="<?php echo isset( $delivery_order_per_slot[ $day_key ] ) ? esc_attr( $delivery_order_per_slot[ $day_key ] ) : 0; ?>" name="order_per_slot[<?php echo esc_attr( $day_key ); ?>]" placeholder="<?php esc_attr_e( 'Order per slot', 'dokan' ); ?>" class="dokan-form-control" type="number">
                                    <span class="dokan-page-help"><?php esc_html_e( 'Maximum orders per slot. 0 for unlimited orders', 'dokan' ); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <?php wp_nonce_field( 'dokan_delivery_time_form_action', 'dokan_delivery_settings_nonce' ); ?>

        <div class="dokan-form-group">
            <div class="dokan-w4 dokan-text-left" style="margin-left: 24%">
                <input type="submit" name="dokan_update_delivery_time_settings" class="dokan-btn dokan-btn-danger dokan-btn-theme" value="<?php esc_attr_e( 'Update Settings', 'dokan' ); ?>">
            </div>
        </div>

    </form>
</div>

<script>
    jQuery( document ).ready( function ( $ ) {

        let checkedDays = [];
        getSelectedDeliveryDays();

        $( '.delivery-day-checkbox' ).on( 'change', function() {
            getSelectedDeliveryDays();
        } );

        function getSelectedDeliveryDays() {
            checkedDays = [];

            $( '.delivery-day-checkbox:checked' ).each( function() {
                let el = $(this).attr( 'data-tag' );
                $('.tab-panels .tabs li[rel="' + el + '"]').removeClass('deactivated');
                checkedDays.push(el);
            });

            $( '.delivery-day-checkbox:not(:checked)' ).each( function() {
                let el = $(this).attr( 'data-tag' );
                $('.tab-panels .tabs li[rel="' + el + '"]').addClass('deactivated');
            });
        }

        $('.tab-panels .tabs li').each( function (){
            //figure out which panel to show
            let panelToShow = $(this).attr('rel');

            if ( ! checkedDays.includes( panelToShow ) ) {
                $(this).addClass('deactivated');
            }
        });

        $('.tab-panels .tabs li').on('click', function(){
            //figure out which panel to show
            let panelToShow = $(this).attr('rel');

            if ( ! checkedDays.includes( panelToShow ) ) {
                return;
            }

            let $panel = $(this).closest('.tab-panels');
            $panel.find('.tabs li.active').removeClass('active');
            $(this).addClass('active');
            $(this).removeClass('deactivated');

            //hide current panel
            $panel.find(' .panel.active').slideUp(300, showNextPanel);

            //create function to show panel
            function showNextPanel(){
                $(this).removeClass('active');
                //show new panel
                $('#'+panelToShow).slideDown(300, function(){
                    $(this).addClass('active');
                });
            }
        });
    } );
</script>

