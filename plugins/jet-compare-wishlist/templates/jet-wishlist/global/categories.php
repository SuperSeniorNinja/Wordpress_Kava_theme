<?php
/**
 * Wishlist loop item categories template
 */

if ( 'yes' !== $widget_settings['show_item_categories'] ) {
	return;
}

echo jet_cw_functions()->get_categories( $_product );
