<?php
/**
 * The template for displaying search form.
 *
 * @package Kava
 */
?>
<form role="search" method="get" class="rd-search rd-search_classic" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div class="form-wrap">
		<span class="screen-reader-text"><?php echo _x( 'Search for:', 'label', 'kava' ) ?></span>
		<input type="search" class="form-input" placeholder="<?php echo esc_attr_x( 'Search in blog...', 'placeholder', 'kava' ) ?>" value="<?php echo get_search_query() ?>" name="s">
	</div>
	<button type="submit" class="rd-search-submit"></button>
</form>