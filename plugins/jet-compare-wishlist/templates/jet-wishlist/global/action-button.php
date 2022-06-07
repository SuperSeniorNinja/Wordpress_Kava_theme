<?php
/**
 * Wishlist loop item button template
 */

if ( 'yes' !== $widget_settings['show_item_button'] ) {
	return;
}

echo jet_cw_functions()->get_add_to_cart_button( $_product );