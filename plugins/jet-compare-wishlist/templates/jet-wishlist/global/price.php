<?php
/**
 * Wishlist loop item price template
 */

$price = jet_cw_functions()->get_price( $_product );

if ( 'yes' !== $widget_settings['show_item_price'] || '' === $price ) {
	return;
}

echo $price;
