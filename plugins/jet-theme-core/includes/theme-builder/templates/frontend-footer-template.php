<?php
$layout = jet_theme_core()->theme_builder->frontend_manager->get_matched_page_template_layouts();

jet_theme_core()->theme_builder->frontend_manager->render_location( $layout['footer']['id'] );

wp_footer();

?></body>
</html>
