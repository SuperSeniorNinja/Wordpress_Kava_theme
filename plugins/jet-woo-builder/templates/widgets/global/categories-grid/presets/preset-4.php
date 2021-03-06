<?php
/**
 * JetWooBuilder Categories Grid widget loop item layout 4 template.
 */

include $this->get_template( 'item-thumb' );
?>

<div class="jet-woo-categories-content">
	<div class="jet-woo-categories-title__wrap">
		<?php
		include $this->get_template( 'item-title' );
		include $this->get_template( 'item-count' );
		?>
	</div>

	<?php include $this->get_template( 'item-description' ); ?>
</div>