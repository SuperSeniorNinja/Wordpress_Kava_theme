<?php
global $wp_locale;
$variation_product   = wc_get_product( $variation );
$chosen_price        = get_post_meta( $variation->ID, '_subscription_price', true );
$chosen_interval     = get_post_meta( $variation->ID, '_subscription_period_interval', true );
$chosen_length       = get_post_meta( $variation->ID, '_subscription_length', true );
$chosen_trial_length = WC_Subscriptions_Product::get_trial_length( $variation_product );
$chosen_trial_period = WC_Subscriptions_Product::get_trial_period( $variation_product );

$_sale_price_dates_from = $variation_product->get_date_on_sale_from( 'edit' ) ? $variation_product->get_date_on_sale_from( 'edit' )->getTimestamp() : false;
$_sale_price_dates_to   = $variation_product->get_date_on_sale_to( 'edit' ) ? $variation_product->get_date_on_sale_to( 'edit' )->getTimestamp() : false;

$_sale_price_dates_from = ! empty( $_sale_price_dates_from ) ? dokan_current_datetime()->setTimeStamp( $_sale_price_dates_from )->format( 'Y-m-d' ) : '';
$_sale_price_dates_to   = ! empty( $_sale_price_dates_to ) ? dokan_current_datetime()->setTimeStamp( $_sale_price_dates_to )->format( 'Y-m-d' ) : '';

$show_schedule          = false;

if ( ! empty( $_sale_price_dates_from ) && ! empty( $_sale_price_dates_to ) ) {
    $show_schedule = true;
}

// Set month as the default billing period
// @codingStandardsIgnoreStart
if ( ! $chosen_period = get_post_meta( $variation->ID, '_subscription_period', true ) ) {
    $chosen_period = 'month';
}
?>

<div class="dokan-form-group dokan-clearfix show_if_variable-subscription">
    <div class="subscription-price">
        <div class="content-half-part">
            <label for="variable_subscription_price" class="form-label"><?php esc_html_e( 'Subscription price', 'dokan-lite' ); ?>(<?php echo get_woocommerce_currency_symbol() ?>) <span class="vendor-earning">( <?php _e( ' You Earn : ', 'dokan' ) ?><?php echo get_woocommerce_currency_symbol() ?><span class="vendor-price"><?php echo esc_html( dokan()->commission->get_earning_by_product( $variation->ID ) ); ?></span> )</span></label>

            <div class="dokan-input-group">
                <input type="text" name="variable_subscription_price[<?php echo $loop; ?>]" value="<?php if ( isset( $chosen_price ) ) echo esc_attr( $chosen_price ); ?>" class="wc_input_price dokan-form-control dokan-product-regular-price-variable" placeholder="<?php esc_attr_e( 'Variation price (required)', 'dokan' ); ?>" />
            </div>
            <div class="dokan-input-group">
                <select id="variable_subscription_period_interval[<?php echo $loop; ?>]" name="variable_subscription_period_interval[<?php echo $loop; ?>]" class="dokan-form-control">
                    <?php foreach ( wcs_get_subscription_period_interval_strings() as $value => $label ) { ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $chosen_interval, true ) ?>><?php echo esc_html( $label ); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="dokan-input-group">
                <select id="variable_subscription_period[<?php echo $loop; ?>]" name="variable_subscription_period[<?php echo $loop; ?>]" class="dokan-form-control" >
                    <?php foreach ( wcs_get_subscription_period_strings() as $value => $label ) { ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $chosen_period, true ) ?>><?php echo esc_html( $label ); ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="content-half-part">
            <div class="dokan-form-group subscription-expire">
                <label for="variable_subscription_length" class="form-label"><?php esc_html_e( 'Subscription expire after', 'dokan-lite' ); ?></label>
                <select id="variable_subscription_length[<?php echo $loop; ?>]" name="variable_subscription_length[<?php echo $loop; ?>]" class="dokan-form-control" >
                    <?php foreach ( wcs_get_subscription_ranges( $chosen_period ) as $value => $label ) { ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $chosen_length, true ) ?>><?php echo esc_html( $label ); ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="dokan-clearfix"></div>
    </div>

    <div class="dokan-form-group subscription-sign-up-fee dokan-clearfix">
        <div class="dokan-form-group content-half-part">
            <label class="form-label" for="variable_subscription_sign_up_fee[<?php echo esc_attr( $loop ); ?>]"><?php printf( esc_html__( 'Sign-up fee (%s)', 'dokan' ), esc_html( get_woocommerce_currency_symbol() ) ); ?></label>
            <input type="text" class="dokan-form-control wc_input_subscription_intial_price wc_input_subscription_initial_price wc_input_price" name="variable_subscription_sign_up_fee[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_price( get_post_meta( $variation->ID, '_subscription_sign_up_fee', true ) ) ); ?>" placeholder="<?php echo esc_attr_x( 'e.g. 9.90', 'example price', 'dokan' ); ?>">
        </div>

        <div class="content-half-part">
            <label class="form-label" for="variable_subscription_trial_length[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Free trial', 'dokan' ); ?></label>
            <div class="dokan-form-group dokan-clearfix">
                <div class="content-half-part">
                    <input type="text" class="dokan-form-control wc_input_subscription_trial_length" name="variable_subscription_trial_length[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $chosen_trial_length ); ?>">
                </div>
                <div class="content-half-part">
                    <select name="variable_subscription_trial_period[<?php echo esc_attr( $loop ); ?>]" class="dokan-form-control wc_input_subscription_trial_period">
                        <?php foreach ( wcs_get_available_time_periods() as $key => $value ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $chosen_trial_period ); ?>><?php echo esc_html( $value ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="dokan-clearfix"></div>
    <?php
    if ( WC_Subscriptions_Synchroniser::is_syncing_enabled() ):
        // Set month as the default billing period
        $subscription_period = WC_Subscriptions_Product::get_period( $variation );

        if ( empty( $subscription_period ) ) {
            $subscription_period = 'month';
        }

        $display_week_month_select = ( ! in_array( $subscription_period, array( 'month', 'week' ) ) ) ? 'display: none;' : '';
        $display_annual_select     = ( 'year' != $subscription_period ) ? 'display: none;' : '';

        $payment_day = WC_Subscriptions_Synchroniser::get_products_payment_day( $variation );

        // An annual sync date is already set in the form: array( 'day' => 'nn', 'month' => 'nn' ), create a MySQL string from those values (year and time are irrelvent as they are ignored)
        if ( is_array( $payment_day ) ) {
            $payment_month = $payment_day['month'];
            $payment_day   = $payment_day['day'];
        } else {
            $payment_month = gmdate( 'm' );
        }
    ?>
    <div class="variable-subscription-sync">
        <div class="dokan-form-group subscription_sync_week_month" style="<?php echo esc_attr( $display_week_month_select ) ?>">
            <label for="<?php echo 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key . '[' . $loop . ']';?>" class="form-label"><?php esc_html_e( 'Synchronise renewals', 'dokan-lite' ); ?></label>
            <select id="<?php echo 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key . '[' . $loop . ']';?>" name="<?php echo 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key . '[' . $loop . ']';?>" class="dokan-form-control wc_input_subscription_payment_sync" >
                <?php foreach ( WC_Subscriptions_Synchroniser::get_billing_period_ranges( $subscription_period ) as $value => $label ) { ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $payment_day, true ) ?>><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="dokan-form-group subscription_sync_annual dokan-clearfix" style="<?php echo esc_attr( $display_annual_select ) ?>">
            <label for="<?php echo WC_Subscriptions_Synchroniser::$post_meta_key_month;?>" class="form-label"><?php esc_html_e( 'Synchronise renewals', 'dokan-lite' ); ?></label>
            <div class="content-half-part">
                <input type="number" id="<?php echo 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key_day . '[' . $loop . ']' ; ?>" name="<?php echo 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key_day . '[' . $loop . ']' ; ?>" class="dokan-form-control wc_input_subscription_payment_sync" value="<?php echo esc_attr( $payment_day ); ?>" placeholder="<?php esc_html_e( 'Day', 'dokan-lite' );?>"/>
            </div>
            <div class="content-half-part">
                <select id="<?php echo 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key_month . '[' . $loop . ']';?>" name="<?php echo 'variable' . WC_Subscriptions_Synchroniser::$post_meta_key_month . '[' . $loop . ']';?>" class="dokan-form-control wc_input_subscription_payment_sync" >
                    <?php foreach ( $wp_locale->month as $value => $label ) { ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $payment_month, true ) ?>><?php echo esc_html( $label ); ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php // @codingStandardsIgnoreEnd ?>
