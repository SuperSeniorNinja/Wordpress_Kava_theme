<?php
/**
 * Display the billing schedule for a subscription
 *
 * @var object $subscription The WC_Subscription object to display the billing schedule for
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$wcs_get_subscription_date_types = wcs_get_subscription_date_types();

unset($wcs_get_subscription_date_types['trial_end']); //Temp

?>
<div class="wc-metaboxes-wrapper">

	<div id="billing-schedule">
		<?php if ( $subscription->can_date_be_updated( 'next_payment' ) ) : ?>
		<div class="billing-schedule-edit wcs-date-input">

			<label for="_billing_interval" class="form-label"><?php esc_html_e( 'Payment', 'dokan-lite' ); ?> <i class="fas fa-question-circle tips" aria-hidden="true" data-title="<?php esc_html_e( 'Choose Variable if your product has multiple attributes - like sizes, colors, quality etc', 'dokan-lite' ); ?>"></i></label>
			<select name="_billing_interval" class="dokan-form-control" id="_billing_interval">
					<?php foreach ( wcs_get_subscription_period_interval_strings() as $key => $value ) { ?>
							<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $subscription->get_billing_interval(), $key ) ?>><?php echo esc_html( $value ) ?></option>
					<?php } ?>
			</select>

			<label for="_billing_period" class="form-label"><?php esc_html_e( 'Payment', 'dokan-lite' ); ?> <i class="fas fa-question-circle tips" aria-hidden="true" data-title="<?php esc_html_e( 'Choose Variable if your product has multiple attributes - like sizes, colors, quality etc', 'dokan-lite' ); ?>"></i></label>
			<select name="_billing_period" class="dokan-form-control" id="_billing_period">
					<?php foreach ( wcs_get_subscription_period_strings() as $key => $value ) { ?>
							<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $subscription->get_billing_period(), $key ) ?>><?php echo esc_html( $value ) ?></option>
					<?php } ?>
			</select>

			<input type="hidden" name="wcs-lengths" id="wcs-lengths" data-subscription_lengths="<?php echo esc_attr( wcs_json_encode( wcs_get_subscription_ranges() ) ); ?>">
		</div>
		<?php else : ?>
		<strong><?php esc_html_e( 'Recurring:', 'woocommerce-subscriptions' ); ?></strong>
		<?php printf( '%s %s', esc_html( wcs_get_subscription_period_interval_strings( $subscription->get_billing_interval() ) ), esc_html( wcs_get_subscription_period_strings( 1, $subscription->get_billing_period() ) ) ); ?>
	<?php endif; ?>
	</div>
	<?php do_action( 'wcs_subscription_schedule_after_billing_schedule', $subscription ); ?>
	<?php foreach ( $wcs_get_subscription_date_types as $date_key => $date_label ) : ?>
		<?php $internal_date_key = wcs_normalise_date_type_key( $date_key ) ?>
		<?php if ( false === wcs_display_date_type( $date_key, $subscription ) ) : ?>
			<?php continue; ?>
		<?php endif;?>
	<div id="subscription-<?php echo esc_attr( $date_key ); ?>-date" class="date-fields">
		<strong><?php echo esc_html( $date_label ); ?>:</strong>
		<input type="hidden" name="<?php echo esc_attr( $date_key ); ?>_timestamp_utc" id="<?php echo esc_attr( $date_key ); ?>_timestamp_utc" value="<?php echo esc_attr( $subscription->get_time( $internal_date_key, 'gmt' ) ); ?>"/>
		<?php if ( $subscription->can_date_be_updated( $internal_date_key ) ) : ?>
			<?php echo wp_kses( wcs_date_input( $subscription->get_time( $internal_date_key, 'site' ), array( 'name_attr' => $date_key ) ), array( 'input' => array( 'type' => array(), 'class' => array(), 'placeholder' => array(), 'name' => array(), 'id' => array(), 'maxlength' => array(), 'size' => array(), 'value' => array(), 'patten' => array() ), 'div' => array( 'class' => array() ), 'span' => array(), 'br' => array() ) ); ?>
		<?php else : ?>
			<?php echo esc_html( $subscription->get_date_to_display( $internal_date_key ) ); ?>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>


</div>
