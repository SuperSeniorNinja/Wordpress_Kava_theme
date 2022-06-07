<?php
/**
 * Wishlist loop item SKU template
 */

if ( 'yes' !== $widget_settings['show_item_sku'] ) {
	return;
}

echo jet_cw_functions()->get_sku( $_product );
