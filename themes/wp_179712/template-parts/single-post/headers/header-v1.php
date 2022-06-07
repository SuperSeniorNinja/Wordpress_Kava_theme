<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Kava
 */

?>

<?php kava_post_thumbnail( 'thumbnail-870-412', array( 'link' => false, 'class' => '', 'link-class' => 'post-single__image' ) ); ?>
<?php the_title( '<h4 class="post-single__title">', '</h4>' ); ?>
<!-- <ul class="post-single__meta">
    <li>
    	<?php 
    	kava_posted_on( array(
			'prefix'  => '<span><b>'.__( 'Date', 'kava' ).'</b></span> <span>',
			'before' => '',
			'after'  => '</span>',
		) );
    	?>
    </li>
    <li>
    	<?php 
    	kava_posted_by( array(
    		'prefix' => '<span><b>'.__( 'Posted by', 'kava' ).'</b></span> <span>',
			'before' => '',
			'after'  => '</span>',
    	) );
    	?>
    </li>
    <li>
    	<?php 
    	kava_post_comments( array(
    		'prefix'  => __( 'Comment(s):', 'kava' ),
			'postfix' => '',
		) );
    	?>
    </li>
    <li>
    	<?php 
    		kava_posted_in( array(
				'prefix'  => '<span><b>'.__( 'Category', 'kava' ).'</b></span>',
				'delimiter' => '',
				'before'    => '',
				'after'     => '</span>'
			) );
    	?>
    </li>
</ul> -->