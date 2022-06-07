<?php

namespace WeDevs\DokanPro\Modules\Elementor;

use WeDevs\Dokan\Traits\Singleton;

class Templates {

    use Singleton;

    public function boot() {
        add_filter( 'elementor/api/get_templates/body_args', [ self::class, 'add_http_request_filter' ] );
        add_filter( 'option_' . \Elementor\Api::LIBRARY_OPTION_KEY, [ self::class, 'add_template_library' ] );
        add_action( 'woocommerce_api_dokan-elementor-template-preview', [ self::class, 'template_preview' ] );
    }

    /**
     * Filter elementor https request
     *
     * @since 2.9.11
     *
     * @param array $body_args
     */
    public static function add_http_request_filter( $body_args ) {
        add_filter( 'pre_http_request', [ self::class, 'pre_http_request' ], 10, 3 );

        return $body_args;
    }

    /**
     * Returns dokan templates for related request
     *
     * @since 2.9.11
     *
     * @param bool   $pre
     * @param array  $r
     * @param string $url
     *
     * @return bool|array
     */
    public static function pre_http_request( $pre, $r, $url ) {
        $templates   = [
            '1000001' => '1',
            '1000002' => '2',
            '1000003' => '3',
            '1000004' => '4'
        ];

        $template_id = ! empty( $r['body']['id'] ) ? $r['body']['id'] : 0;

        if ( array_key_exists( $template_id, $templates ) ) {
            $json_file = DOKAN_ELEMENTOR_PATH . '/template-library/' . $templates[ $template_id ] . '.json';

            if ( file_exists( $json_file ) ) {
                $content = json_decode( file_get_contents( $json_file ), true );

                return [
                    'response' => [
                        'code' => 200,
                    ],
                    'body' => json_encode( $content )
                ];
            }
        }

        return $pre;
    }

    /**
     * Add Dokan templates as remote template source
     *
     * @since 2.9.11
     *
     * @param array $value
     */
    public static function add_template_library( $value ) {
        if ( 'string' === gettype($value['categories']) ) {
            $categories          = json_decode( $value['categories'], true );
            $categories[]        = 'single store';
            $value['categories'] = $categories;
        } else {
            $value['categories'][] = 'single store';
        }

        $store_templates = [
            [
                'id'                => "1000001",
                'source'            => "remote",
                'type'              => "block",
                'subtype'           => "single store",
                'title'             => "Store Header Layout 1",
                'thumbnail'         => DOKAN_PLUGIN_ASSEST . '/images/store-header-templates/default.png',
                'tmpl_created'      => "1475067229",
                'author'            => "weDevs",
                'tags'              => '',
                'is_pro'            => false,
                'popularity_index'  => 1,
                'trend_index'       => 1,
                'favorite'          => false,
                'has_page_settings' => false,
                'url'               => home_url( '/?wc-api=dokan-elementor-template-preview&id=01' ),
            ],
            [
                'id'                => "1000002",
                'source'            => "remote",
                'type'              => "block",
                'subtype'           => "single store",
                'title'             => "Store Header Layout 2",
                'thumbnail'         => DOKAN_PLUGIN_ASSEST . '/images/store-header-templates/layout1.png',
                'tmpl_created'      => "1475067229",
                'author'            => "weDevs",
                'tags'              => '',
                'is_pro'            => false,
                'popularity_index'  => 1,
                'trend_index'       => 1,
                'favorite'          => false,
                'has_page_settings' => false,
                'url'               => home_url( '/?wc-api=dokan-elementor-template-preview&id=02' ),
            ],
            [
                'id'                => "1000003",
                'source'            => "remote",
                'type'              => "block",
                'subtype'           => "single store",
                'title'             => "Store Header Layout 3",
                'thumbnail'         => DOKAN_PLUGIN_ASSEST . '/images/store-header-templates/layout2.png',
                'tmpl_created'      => "1475067229",
                'author'            => "weDevs",
                'tags'              => '',
                'is_pro'            => false,
                'popularity_index'  => 1,
                'trend_index'       => 1,
                'favorite'          => false,
                'has_page_settings' => false,
                'url'               => home_url( '/?wc-api=dokan-elementor-template-preview&id=03' ),
            ],
            [
                'id'                => "1000004",
                'source'            => "remote",
                'type'              => "block",
                'subtype'           => "single store",
                'title'             => "Store Header Layout 4",
                'thumbnail'         => DOKAN_PLUGIN_ASSEST . '/images/store-header-templates/layout3.png',
                'tmpl_created'      => "1475067229",
                'author'            => "weDevs",
                'tags'              => '',
                'is_pro'            => false,
                'popularity_index'  => 1,
                'trend_index'       => 1,
                'favorite'          => false,
                'has_page_settings' => false,
                'url'               => home_url( '/?wc-api=dokan-elementor-template-preview&id=04' ),
            ],
        ];

        $value['templates'] = array_merge( $value['templates'], $store_templates );

        return $value;
    }

    /**
     * Template preview
     *
     * @since 2.9.11
     *
     * @return void
     */
    public static function template_preview() {
        include DOKAN_ELEMENTOR_VIEWS . '/template-preview.php';
    }
}
