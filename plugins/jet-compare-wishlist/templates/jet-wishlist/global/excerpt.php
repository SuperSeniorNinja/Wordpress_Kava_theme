<?php
/**
 * Wishlist loop item excerpt template
 */

if ( 'yes' !== $widget_settings['show_item_excerpt'] ) {
	return;
}

echo jet_cw_functions()->get_excerpt( $_product );
