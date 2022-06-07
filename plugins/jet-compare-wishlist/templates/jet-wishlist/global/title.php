<?php
/**
 * Wishlist loop item title template
 */

$full_title = get_the_title( $_product->get_id() );
$title      = jet_cw_functions()->trim_text(
	$full_title,
	$widget_settings['title_length'],
	$widget_settings['title_trim_type'],
	'...'
);

$title_link    = esc_url( get_permalink( $_product->get_id() ) );
$title_tooltip = '';

if ( -1 !== $widget_settings['title_length'] && 'yes' === $widget_settings['title_tooltip'] ) {
	$title_tooltip = 'title="' . $full_title . '"';
}

$open_wrap  = '<' . $heading_tag . ' class="jet-cw-product-title"><a href="' . $title_link . '" ' . $title_tooltip . '>';
$close_wrap = '</a></' . $heading_tag . '>';

if ( 'yes' !== $widget_settings['show_item_title'] || '' === $title ) {
	return;
}

echo $open_wrap . $title . $close_wrap;