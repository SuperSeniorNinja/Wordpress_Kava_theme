<?php

namespace WeDevs\DokanPro\Modules\RankMath;

defined( 'ABSPATH' ) || exit;

use RankMath\Helper;
use MyThemeShop\Helpers\WordPress;
use RankMath\ContentAI\Content_AI;

/**
 * Schema manger class
 *
 * @since 3.5.0
 */
class ContentAi extends Content_AI {

    /**
     * Class constructor
     *
     * @since 3.5.0
     */
    public function __construct() {
        parent::__construct();
        $this->editor_scripts();
    }

    /**
     * Enqueue assets for post editors.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function editor_scripts() {
        if ( ! in_array( WordPress::get_post_type(), (array) Helper::get_settings( 'general.content_ai_post_types' ), true ) ) {
            return;
        }

        wp_register_style(
            'rank-math-common',
            rank_math()->plugin_url() . 'assets/admin/css/common.css',
            array(),
            rank_math()->version
        );

        wp_enqueue_style(
            'rank-math-content-ai',
            rank_math()->plugin_url() . 'includes/modules/content-ai/assets/css/content-ai.css',
            [ 'rank-math-common' ],
            rank_math()->version
        );

        wp_enqueue_script(
            'rank-math-content-ai',
            rank_math()->plugin_url() . 'includes/modules/content-ai/assets/js/content-ai.js',
            [ 'rank-math-editor' ],
            rank_math()->version,
            true
        );
    }
}
