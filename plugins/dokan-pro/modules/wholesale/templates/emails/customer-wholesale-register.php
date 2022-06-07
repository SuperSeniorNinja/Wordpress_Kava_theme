<?php
/**
 * Admin new wholesale customer email
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

	$opening_paragraph = __( 'A customer has been request for beign wholesale. and is awaiting your approval. The details of this  are as follows:', 'dokan' );
?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>


<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'User Name', 'dokan' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $user->display_name; ?></td>
		</tr>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php _e( 'User Email', 'dokan' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $user->user_email; ?></td>
		</tr>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php _e( 'User NiceName', 'dokan' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $user->user_nicename; ?></td>
		</tr>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php _e( 'User Total Spent', 'dokan' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo (int) wc_get_customer_total_spent($user->ID); ?></td>
		</tr>
	</tbody>
</table>

<p><?php _e( 'This request is awaiting your approval. Please check it and inform the customer if he is eligble or not.', 'dokan' ); ?></p>

<p><?php echo make_clickable(sprintf('<a href="%s">' . __('View and edit this this request in teh admin panel ', 'dokan') . '</a>', untrailingslashit(admin_url()) . '?page=dokan#/wholesale-customer')); ?></p>

<?php do_action( 'woocommerce_email_footer' ); ?>
