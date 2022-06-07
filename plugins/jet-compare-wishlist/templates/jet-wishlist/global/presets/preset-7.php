<?php
/**
 * Wishlist preset 7
 */
?>

<div class="jet-wishlist-products-thumb-wrapper">
	<?php include $this->get_template( 'thumbnail' ); ?>
</div>
<div class="jet-wishlist-products-remove-btn-wrapper">
	<?php include $this->get_template( 'remove-button' ); ?>
</div>
<div class="jet-wishlist-products-content-wrapper">
	<?php
	include $this->get_template( 'categories' );
	include $this->get_template( 'sku' );
	include $this->get_template( 'stock-status' );
	include $this->get_template( 'title' );
	include $this->get_template( 'price' );
	include $this->get_template( 'rating' );
	?>
	<div class="hovered-content">
		<?php
		include $this->get_template( 'excerpt' );
		include $this->get_template( 'action-button' );
		include $this->get_template( 'tags' );
		?>
	</div>
</div>
