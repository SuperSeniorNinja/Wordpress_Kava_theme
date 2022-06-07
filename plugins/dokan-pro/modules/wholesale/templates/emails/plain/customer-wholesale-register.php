<?php
/**
 * Admin new wholesale customer email
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo "= " . $email_heading . " =\n\n";

$opening_paragraph = __( 'A customer has been request for beign wholesale. and is awaiting your approval. The details of this  are as follows:', 'dokan' );

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo sprintf( __( 'User Name: %s', 'dokan' ), $user->display_name ) . "\n";
echo sprintf( __( 'User Email: %s', 'dokan' ), $user->user_email ) . "\n";


echo sprintf( __( 'User NiceName: %s', 'dokan' ), $user->user_nicename ) . "\n";
echo sprintf( __( 'User Total Spent: %s', 'dokan' ), (int) wc_get_customer_total_spent($user->ID) ) . "\n";


echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

     echo make_clickable(sprintf('<a href="%s">' . __('View and edit this this request in teh admin panel ', 'dokan') . '</a>', untrailingslashit(admin_url()) . '?page=dokan#/wholesale-customer'));

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
