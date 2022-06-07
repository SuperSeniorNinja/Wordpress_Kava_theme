<?php
/**
 * Wishlist loop item rating template
 */

$rating = jet_cw_functions()->get_rating( $_product, $widget_settings );

if ( 'yes' !== $widget_settings['show_item_rating'] ) {
	return;
}

echo $rating;
