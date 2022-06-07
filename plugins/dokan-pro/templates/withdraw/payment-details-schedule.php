<?php
/**
 * Payment details schedule popup template
 *
 * @since 3.5.0
 */
?>
<div class="dokan-clearfix dokan-panel-inner-container">
    <div class="dokan-w8">
        <p>
            <strong><?php esc_html_e( 'Schedule', 'dokan' ); ?></strong><br>
            <?php echo wp_kses_post( $schedule_information ); ?><br>
            <?php echo wp_kses_post( $threshold_information ); ?>
        </p>

        <?php do_action( 'dokan_withdraw_content_after_schedule' ); ?>

    </div>
    <div class="dokan-w5">
        <button class="dokan-btn" id="dokan-withdraw-display-schedule-popup"><?php esc_html_e( 'Edit Schedule', 'dokan' ); ?></button>
        <?php do_action( 'dokan_withdraw_content_after_schedule_button' ); ?>
    </div>
</div>
