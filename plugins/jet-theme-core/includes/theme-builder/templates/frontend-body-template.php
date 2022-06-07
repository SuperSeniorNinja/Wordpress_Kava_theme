<?php
$layout = jet_theme_core()->theme_builder->frontend_manager->get_matched_page_template_layouts();

get_header();

jet_theme_core()->theme_builder->frontend_manager->render_location( $layout['body']['id'] );

get_footer();
