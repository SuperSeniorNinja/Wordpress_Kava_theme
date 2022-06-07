<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_edit_account_form' );?>

<?php $user_id = get_current_user_id(); ?>

<form class="woocommerce-EditAccountForm edit-account" action="" method="post">	
	 <?php
	 	$uwa_disable_display_user_name = get_user_meta($user_id, 'uwa_disable_display_user_name', true) !== '0' ? '1' : '0';
	 	woocommerce_form_field( 'uwa_disable_display_user_name', array(
        'type'          => 'checkbox',
        'class'         => array('input-checkbox'),
        'label'         => __('Display your name publicly', 'ultimate-woocommerce-auction'),
        'required'  => false,
        'default' => 1
        ), $uwa_disable_display_user_name );
       ?>
	<div class="clear"></div>
	<p>
		<?php wp_nonce_field( 'save_uwa_auctions_settings' ); ?>
		<input type="submit" class="woocommerce-Button button" name="save_uwa_auctions_settings" value="<?php esc_attr_e( 'Save changes', 'ultimate-woocommerce-auction' ); ?>" />
		<input type="hidden" name="action" value="save_uwa_auctions_settings" />
	</p>
</form>