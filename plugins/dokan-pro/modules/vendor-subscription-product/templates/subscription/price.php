<?php
global $wp_locale;
$chosen_price        = get_post_meta( $post_id, '_subscription_price', true );
$chosen_interval     = get_post_meta( $post_id, '_subscription_period_interval', true );
$chosen_length       = get_post_meta( $post_id, '_subscription_length', true );
$chosen_trial_length = WC_Subscriptions_Product::get_trial_length( $post_id );
$chosen_trial_period = WC_Subscriptions_Product::get_trial_period( $post_id );

$product_object         = wc_get_product( $post_id );
$_sale_price_dates_from = $product_object->get_date_on_sale_from( 'edit' ) ? $product_object->get_date_on_sale_from( 'edit' )->getTimestamp() : false;
$_sale_price_dates_to   = $product_object->get_date_on_sale_to( 'edit' ) ? $product_object->get_date_on_sale_to( 'edit' )->getTimestamp() : false;

$_sale_price_dates_from = ! empty( $_sale_price_dates_from ) ? dokan_current_datetime()->setTimeStamp( $_sale_price_dates_from )->format( 'Y-m-d' ) : '';
$_sale_price_dates_to   = ! empty( $_sale_price_dates_to ) ? dokan_current_datetime()->setTimeStamp( $_sale_price_dates_to )->format( 'Y-m-d' ) : '';

$show_schedule          = false;

if ( ! empty( $_sale_price_dates_from ) && ! empty( $_sale_price_dates_to ) ) {
    $show_schedule = true;
}

// Set month as the default billing period
if ( ! $chosen_period = get_post_meta( $post_id, '_subscription_period', true ) ) {
    $chosen_period = 'month';
}
?>

<div class="dokan-clearfix dokan-price-container dokan-subscription-product-price show_if_subscription"> <!--  -->
    <div class="dokan-form-group subscription-price dokan-clearfix">
        <label for="_subscription_price" class="form-label"><?php esc_html_e( 'Subscription price', 'dokan' ); ?>
            <span
                class="vendor-earning subscription-product"
                data-commission="<?php echo esc_attr( dokan()->commission->get_earning_by_product( $post_id ) ); ?>"
                data-product-id="<?php echo esc_attr( $post_id ); ?>">
                    ( <?php esc_html_e( ' You Earn : ', 'dokan' ) ?><?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
                        <span class="vendor-price">
                            <?php echo esc_attr( dokan()->commission->get_earning_by_product( $post_id, 'seller', WC_Subscriptions_Product::get_price( $post_id ) ) ); ?>
                        </span>
                    )
            </span>
        </label>

        <div class="dokan-input-group">
            <span class="dokan-input-group-addon"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span>
            <?php dokan_post_input_box( $post_id, '_subscription_price', array( 'class' => 'dokan-form-control dokan-product-subscription-price', 'placeholder' => __( '0.00', 'dokan' ) ), 'price' ); ?>
        </div>

        <div class="dokan-input-group">
            <select id="_subscription_period_interval" name="_subscription_period_interval" class="dokan-form-control">
                <?php foreach ( wcs_get_subscription_period_interval_strings() as $value => $label ) { ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $chosen_interval, true ) ?>><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="dokan-input-group">
            <select id="_subscription_period" name="_subscription_period" class="dokan-form-control" >
                <?php foreach ( wcs_get_subscription_period_strings() as $value => $label ) { ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $chosen_period, true ) ?>><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="content-full-part sale-price dokan-clearfix dokan-form-group">
        <label for="_subscription_sale_price" class="form-label">
            <?php esc_html_e( 'Sale Price', 'dokan' ); ?>
            <a href="#" class="sale_schedule <?php echo ($show_schedule ) ? 'dokan-hide' : ''; ?>"><?php esc_html_e( 'Schedule', 'dokan' ); ?></a>
            <a href="#" class="cancel_sale_schedule <?php echo ( ! $show_schedule ) ? 'dokan-hide' : ''; ?>"><?php esc_html_e( 'Cancel', 'dokan' ); ?></a>
        </label>

        <div class="dokan-input-group">
            <span class="dokan-input-group-addon"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span>
            <?php dokan_post_input_box( $post_id, '_sale_price', array( 'class' => 'dokan-form-control dokan-product-sales-price','placeholder' => __( '0.00', 'dokan' ), 'name' => '_subscription_sale_price' ), 'price' ); ?>
        </div>
    </div>

    <div class="sale_price_dates_fields dokan-clearfix dokan-form-group <?php echo ( ! $show_schedule ) ? 'dokan-hide' : ''; ?>">
        <div class="content-half-part from">
            <div class="dokan-input-group">
                <span class="dokan-input-group-addon"><?php esc_html_e( 'From', 'dokan' ); ?></span>
                <input type="text" name="_subscription_sale_price_dates_from" class="dokan-form-control dokan-start-date" value="<?php echo esc_attr( $_sale_price_dates_from ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" placeholder="<?php esc_html_e( 'YYYY-MM-DD', 'dokan' ); ?>">
            </div>
        </div>

        <div class="content-half-part to">
            <div class="dokan-input-group">
                <span class="dokan-input-group-addon"><?php esc_html_e( 'To', 'dokan' ); ?></span>
                <input type="text" name="_subscription_sale_price_dates_to" class="dokan-form-control dokan-end-date" value="<?php echo esc_attr( $_sale_price_dates_to ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" placeholder="<?php esc_html_e( 'YYYY-MM-DD', 'dokan' ); ?>">
            </div>
        </div>
    </div>

    <div class="dokan-form-group dokan-clearfix dokan-price-container">
        <div class="dokan-product-less-price-alert dokan-hide">
            <?php esc_html_e('Product price can\'t be less than the vendor fee!', 'dokan' ); ?>
        </div>
    </div>

    <div class="dokan-form-group subscription-expire">
        <label for="_subscription_length" class="form-label"><?php esc_html_e( 'Subscription expire after', 'dokan' ); ?>
        <select id="_subscription_length" name="_subscription_length" class="dokan-form-control" >
            <?php foreach ( wcs_get_subscription_ranges( $chosen_period ) as $value => $label ) { ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $chosen_length, true ) ?>><?php echo esc_html( $label ); ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="dokan-form-group subscription-sign-up-fee">
        <label for="_subscription_sign_up_fee" class="form-label"><?php esc_html_e( 'Sign up Fee', 'dokan' ); ?>
        <div class="dokan-input-group">
            <span class="dokan-input-group-addon"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span>
            <?php dokan_post_input_box( $post_id, '_subscription_sign_up_fee', array( 'class' => 'dokan-form-control dokan-product-subscription-price', 'placeholder' => __( '0.00', 'dokan' ) ), 'price' ); ?>
        </div>
    </div>

    <div class="dokan-form-group subscription-trial-length-field dokan-clearfix">
        <label for="_subscription_trial_length" class="form-label"><?php esc_html_e( 'Free Trial', 'dokan' ); ?></label>
        <div class="content-half-part">
            <input type="number" step="1" min="0" name="_subscription_trial_length" id="_subscription_trial_length" class="dokan-form-control" value="<?php echo $chosen_trial_length ?>" placeholder="0">
        </div>
        <div class="content-half-part">
            <select id="_subscription_trial_period" name="_subscription_trial_period" class="dokan-form-control wc_input_subscription_trial_period last" >
                <?php foreach ( wcs_get_available_time_periods() as $value => $label ) { ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $chosen_trial_period, true ) ?>><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <?php
    if ( WC_Subscriptions_Synchroniser::is_syncing_enabled() ):
        // Set month as the default billing period
        if ( ! $subscription_period = get_post_meta( $post_id, '_subscription_period', true ) ) {
            $subscription_period = 'month';
        }

        // Determine whether to display the week/month sync fields or the annual sync fields
        $display_week_month_select = ( ! in_array( $subscription_period, array( 'month', 'week' ) ) ) ? 'display: none;' : '';
        $display_annual_select     = ( 'year' != $subscription_period ) ? 'display: none;' : '';

        $payment_day = WC_Subscriptions_Synchroniser::get_products_payment_day( $post_id );

        // An annual sync date is already set in the form: array( 'day' => 'nn', 'month' => 'nn' ), create a MySQL string from those values (year and time are irrelvent as they are ignored)
        if ( is_array( $payment_day ) ) {
            $payment_month = $payment_day['month'];
            $payment_day   = $payment_day['day'];
        } else {
            $payment_month = gmdate( 'm' );
        }
        ?>
        <div class="subscription-sync">
            <div class="dokan-form-group subscription_sync_week_month" style="<?php echo esc_attr( $display_week_month_select ) ?>">
                <label for="<?php echo WC_Subscriptions_Synchroniser::$post_meta_key;?>" class="form-label"><?php esc_html_e( 'Synchronise renewals', 'dokan' ); ?></label>
                <select id="<?php echo WC_Subscriptions_Synchroniser::$post_meta_key;?>" name="<?php echo WC_Subscriptions_Synchroniser::$post_meta_key;?>" class="dokan-form-control wc_input_subscription_payment_sync select" >
                    <?php foreach ( WC_Subscriptions_Synchroniser::get_billing_period_ranges( $subscription_period ) as $value => $label ) { ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $payment_day, true ) ?>><?php echo esc_html( $label ); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="dokan-form-group subscription_sync_annual" style="<?php echo esc_attr( $display_annual_select ) ?>">
                <label for="<?php echo WC_Subscriptions_Synchroniser::$post_meta_key_month;?>" class="form-label"><?php esc_html_e( 'Synchronise renewals', 'dokan' ); ?></label>
                <div class="dokan-form-group dokan-clearfix">
                    <div class="content-half-part">
                        <input type="number" id="<?php echo esc_attr( WC_Subscriptions_Synchroniser::$post_meta_key_day ); ?>" name="<?php echo esc_attr( WC_Subscriptions_Synchroniser::$post_meta_key_day ); ?>" class="dokan-form-control wc_input_subscription_payment_sync" value="<?php echo esc_attr( $payment_day ); ?>" placeholder="<?php esc_html_e( 'Day', 'dokan' );?>"/>
                    </div>
                    <div class="content-half-part">
                        <select id="<?php echo WC_Subscriptions_Synchroniser::$post_meta_key_month;?>" name="<?php echo WC_Subscriptions_Synchroniser::$post_meta_key_month;?>" class="dokan-form-control wc_input_subscription_payment_sync" >
                            <?php foreach ( $wp_locale->month as $value => $label ) { ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $payment_month, true ) ?>><?php echo esc_html( $label ); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>
</div>
