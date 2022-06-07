<?php

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
    <?php echo wp_kses_post( $message ); ?>
</p>

<p>
    <?php printf( __( 'Enquiry Summary:', 'dokan' ) ); ?>
</p>

<ul class="dokan-product-enquery">
    <li>
    	<?php
    	// translators: %1: Customer name, %2: Customer emaiil
    	printf( __( 'From: %1$s - %2$s', 'dokan' ), $customer_name, $customer_email );
    	?>
    </li>
    <li>
    	<?php
	    // translators: %s: Product title
	    printf( __( 'Product: %s', 'dokan' ), $product->get_title() );
	    ?>
    </li>
    <li>
    	<?php
    	// translators: %s: Product URL
    	printf( __( 'Product URL: %s', 'dokan' ), $product->get_permalink() );
    	?>
	
    </li>
</ul>
<style type="text/css">
.dokan-product-enquery li {
    font-style: italic;
}
</style>

<?php
do_action( 'woocommerce_email_footer', $email );
