<?php
/**
 * Wishlist loop item thumbnail template
 */

if ( 'default' !== $widget_settings['thumbnail_position'] && 'preset-1' === $widget_settings['presets'] ) {
	return;
}

echo jet_cw_functions()->get_thumbnail( $_product, $widget_settings );