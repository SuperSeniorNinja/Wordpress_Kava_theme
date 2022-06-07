<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Kava
 */

get_header();

	do_action( 'kava-theme/site/site-content-before', 'search' ); ?>

	<div <?php kava_content_class() ?>>
		<div class="row">

			<?php do_action( 'kava-theme/site/primary-before', 'search' ); ?>

			<div id="primary" class="col-xs-12">

				<?php do_action( 'kava-theme/site/main-before', 'search' ); ?>

				<main id="main" class="site-main site-main__search-loop-wrapper"><?php
					if ( have_posts() ) : ?>

						<?php

						kava_theme()->do_location( 'archive', 'template-parts/search-loop' );

					else :

						get_template_part( 'template-parts/content', 'none' );

					endif;
				?></main><!-- #main -->

				<?php do_action( 'kava-theme/site/main-after', 'search' ); ?>

			</div><!-- #primary -->

			<?php do_action( 'kava-theme/site/primary-after', 'search' ); ?>

		</div>
	</div><?php
get_footer();
