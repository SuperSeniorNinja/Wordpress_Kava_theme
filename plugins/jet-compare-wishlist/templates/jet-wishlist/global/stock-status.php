<?php
/**
 * Wishlist loop item stock status template
 */

if ( 'yes' !== $widget_settings['show_item_stock_status'] ) {
	return;
}

echo jet_cw_functions()->get_stock_status( $_product );
