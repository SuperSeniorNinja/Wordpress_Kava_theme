<?php
$layout = jet_theme_core()->theme_builder->frontend_manager->get_matched_page_template_layouts();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head><?php

    echo $jet_theme_core_theme_head;

	do_action( 'jet-theme-core/theme-builder/override/head/before' );

	wp_head();

	do_action( 'jet-theme-core/theme-builder/override/head/after' );
?></head>
<body <?php body_class(); ?>><?php
	wp_body_open();

	jet_theme_core()->theme_builder->frontend_manager->render_location( $layout['header']['id'] );?>
