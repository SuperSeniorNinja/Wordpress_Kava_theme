<?php

namespace WeDevs\DokanPro\Modules\ExIm;

use WeDevs\DokanPro\Modules\ExIm\Export\ProductCsvExporter;
use WeDevs\DokanPro\Modules\ExIm\Import\ProductCsvImporter;
use WeDevs\DokanPro\Modules\ExIm\Importers\Controller;

if ( ! class_exists( 'Dokan_WXR_Parser' ) ) {
    $class_wp_importer = plugin_dir_path( __FILE__ ) . 'includes/parsers.php';

    if ( file_exists( $class_wp_importer ) ) {
        require $class_wp_importer;
    }
}

/**
 * Dokan_Product_Importer class
 *
 * @class Dokan_Product_Importer The class that holds the entire Dokan_Product_Importer plugin
 */
class Module {
    public $version;

    public $authors    = [];
    public $posts      = [];
    public $terms      = [];
    public $categories = [];
    public $tags       = [];
    public $base_url   = '';

    // mappings from old information to new
    public $processed_authors    = [];
    public $author_mapping       = [];
    public $processed_terms      = [];
    public $processed_posts      = [];
    public $post_orphans         = [];
    public $processed_menu_items = [];
    public $menu_item_orphans    = [];
    public $missing_menu_items   = [];
    public $fetch_attachments    = true;
    public $url_remap            = [];
    public $featured_images      = [];

    /**
     * Constructor for the Dokan_Product_Importer class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        add_action( 'init', [ $this, 'do_product_export' ], 99 );

        define( 'DOKAN_IE_PLUGIN_VERSION', $this->version );
        define( 'DOKAN_IE_FILE', __FILE__ );
        define( 'DOKAN_IE_DIR', __DIR__ );
        define( 'DOKAN_IE_INC_DIR', __DIR__ . '/includes' );
        define( 'DOKAN_IE_ASSETS', plugins_url( 'assets', __FILE__ ) );

        add_action( 'init', [ $this, 'do_product_export' ], 99 );

        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        add_filter( 'dokan_get_dashboard_nav', [ $this, 'add_importer_page' ], 13, 1 );
        add_filter( 'dokan_query_var_filter', [ $this, 'add_endpoint' ] );
        add_filter( 'dokan_dashboard_template_render', [ $this, 'dashboard_template' ] );

        add_action( 'wp_ajax_woocommerce_do_ajax_product_import', [ $this, 'do_ajax_product_import' ] );
        add_action( 'wp_ajax_woocommerce_do_ajax_product_export', [ $this, 'do_ajax_product_export' ], 0 );
        add_action( 'template_redirect', [ $this, 'download_export_file' ] );

        add_action( 'template_redirect', [ $this, 'handle_step_submission' ], 10, 1 );

        if ( self::is_dokan_plugin() ) {
            add_filter( 'dokan_set_template_path', [ $this, 'load_export_import_templates' ], 11, 3 );
            add_action( 'dokan_load_custom_template', [ $this, 'load_tools_template_from_plugin' ], 12 );
        }

        add_filter( 'dokan_get_all_cap', [ $this, 'add_capabilities' ], 10 );
        add_action( 'dokan_after_add_product_btn', [ $this, 'render_import_export_button' ] );
        add_filter( 'dokan_dashboard_nav_active', [ $this, 'dashboard_active_menu' ] );
        add_filter( 'woocommerce_product_import_pre_insert_product_object', [ $this, 'change_product_status' ], 20, 2 );

        add_action( 'wp_footer', [ $this, 'bind_global_ajaxurl' ], 11 );

        // Module activation hook
        add_action( 'dokan_activated_module_export_import', [ $this, 'activate' ] );
        // prevent vendor create category
        add_filter( 'pre_insert_term', [ $this, 'protect_vendor_create_category' ], 10, 2 );
        //False to is_feature column
        add_filter( 'woocommerce_product_import_process_item_data', [ $this, 'feature_column_to_false' ] );
        //Handle wholesale column when export
        add_filter( 'woocommerce_product_export_meta_value', [ $this, 'non_neumeric_wholesale_handle' ], 10, 2 );
        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
    }

    public function handle_step_submission() {
        include_once DOKAN_IE_INC_DIR . '/importers/Controller.php';
        include_once DOKAN_IE_INC_DIR . '/import/ProductCsvImporter.php';
        include_once ABSPATH . '/wp-admin/includes/file.php';
        $import_controller = new Controller();

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'woocommerce-csv-importer' ) ) {
            return;
        }
        if ( isset( $_REQUEST['save_step'] ) && ! empty( sanitize_key( wp_unslash( $_REQUEST['save_step'] ) ) ) ) {
            $import_controller->upload_form_handler();
        }
    }

    /**
     * Check is Dokan is plugin or nor
     *
     * @return bool true|false
     */
    public static function is_dokan_plugin() {
        return defined( 'DOKAN_PLUGIN_VERSION' );
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        global $wp_roles;

        if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
            $wp_roles = new \WP_Roles(); // phpcs:ignore
        }

        $wp_roles->add_cap( 'seller', 'dokan_view_tools_menu' );
        $wp_roles->add_cap( 'administrator', 'dokan_view_tools_menu' );
        $wp_roles->add_cap( 'shop_manager', 'dokan_view_tools_menu' );

        // flash rewrite rules
        $this->flush_rewrite_rules();

        if ( get_option( 'dokan_importer_page_created' ) ) {
            return;
        }
        $dasboard_page = get_page_by_title( 'Dashboard' );

        $post_id = wp_insert_post(
            [
                'post_title'  => wp_strip_all_tags( 'Import' ),
                'post_status' => 'publish',
                'post_parent' => $dasboard_page->ID,
                'post_type'   => 'page',
            ]
        );

        update_option( 'dokan_importer_page_created', true );
        update_option( 'dokan_importer_page_id', $post_id );
    }

    /**
     * Flush rewrite rules
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        dokan()->rewrite->register_rule();
        flush_rewrite_rules( true );
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {
        global $wp;

        if ( dokan_is_seller_dashboard() && isset( $wp->query_vars['tools'] ) ) {
            wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', [], WC_VERSION );
        }

        /*
         * All styles goes here
         */
        wp_enqueue_style( 'dpi-styles', DOKAN_IE_ASSETS . '/css/style.css', false, date( 'Ymd' ) ); // phpcs:ignore

        wp_register_script( 'wc-product-import', WC()->plugin_url() . '/assets/js/admin/wc-product-import.js', [ 'jquery' ], WC_VERSION );
        wp_register_script( 'wc-product-export', WC()->plugin_url() . '/assets/js/admin/wc-product-export.js', [ 'jquery' ], WC_VERSION );
    }

    /**
     * Add Subscription endpoint to the end of Dashboard
     *
     * @param array $query_var
     */
    public function add_endpoint( $query_var ) {
        $query_var['tools'] = 'tools';

        return $query_var;
    }

    /**
     * Add Importer page in seller dashboard
     *
     * @param array $urls
     *
     * @return array $urls
     */
    public function add_importer_page( $urls ) {
        if ( ! current_user_can( 'dokan_view_tools_menu' ) ) {
            return $urls;
        }

        if ( dokan_is_seller_enabled( get_current_user_id() ) ) {
            $installed_version = get_option( 'dokan_theme_version' );

            if ( $installed_version >= '2.4' ) {
                $urls['tools'] = [
                    'title' => __( 'Tools', 'dokan' ),
                    'icon'  => '<i class="fas fa-wrench"></i>',
                    'url'   => dokan_get_navigation_url( 'tools' ),
                    'pos'   => 182,
                ];
            } else {
                $urls['tools'] = [
                    'title' => __( 'Tools', 'dokan' ),
                    'icon'  => '<i class="fas fa-wrench"></i>',
                    'url'   => dokan_get_navigation_url( 'tools' ),
                ];
            }
        }

        return $urls;
    }

    public function dashboard_template( $template_part ) {
        dokan_get_template_part( 'export-import/template_importer', '', [ 'is_export_import' => true ] );

        return;
    }

    /**
     * Get plugin path
     *
     * @since 2.8
     *
     * @return void
     **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Load Dokan Export-Import templates
     *
     * @since 2.8
     *
     * @return void
     **/
    public function load_export_import_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_export_import'] ) && $args['is_export_import'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Load tools template
     *
     * @since  0.4
     *
     * @param array $query_vars
     *
     * @return string
     */
    public function load_tools_template_from_plugin( $query_vars ) {
        if ( isset( $query_vars['tools'] ) ) {
            if ( ! current_user_can( 'dokan_view_tools_menu' ) ) {
                dokan_get_template_part(
                    'global/dokan-error', '', [
                        'deleted' => false,
                        'message' => __( 'You have no permission to view this tool page', 'dokan' ),
                    ]
                );
            } else {
                switch ( $query_vars['tools'] ) {
                    case 'csv-import':
                        include_once DOKAN_IE_INC_DIR . '/importers/Controller.php';
                        include_once DOKAN_IE_INC_DIR . '/import/ProductCsvImporter.php';
                        include_once ABSPATH . '/wp-admin/includes/file.php';
                        $importer = new Controller();
                        $importer->dispatch();
                        break;

                    case 'csv-export':
                        wp_localize_script(
                            'wc-product-export', 'wc_product_export_params', [
                                'export_nonce' => wp_create_nonce( 'wc-product-export' ),
                            ]
                        );
                        $this->product_exporter();

                        break;

                    default:
                        $installed_version = get_option( 'dokan_theme_version' );

                        if ( $installed_version >= '2.4' ) {
                            dokan_get_template_part( 'export-import/template_importer_new', '', [ 'is_export_import' => true ] );
                        } else {
                            dokan_get_template_part( 'export-import/template_importer', '', [ 'is_export_import' => true ] );
                        }

                        break;
                }
            }
        }
    }

    /**
     * Add capabilities
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_capabilities( $capabilities ) {
        $capabilities['menu']['dokan_view_tools_menu'] = __( 'View tools menu', 'dokan' );

        return $capabilities;
    }

    /**
     * Load Importer page templates in template directory
     *
     * @param string $file
     *
     * @return string $file
     */
    public function importer_page_template( $file ) {
        $page_id = get_option( 'dokan_importer_page_id' );

        if ( is_page( $page_id ) ) {
            $file = plugin_dir_path( __FILE__ ) . 'templates/template_importer.php';

            if ( file_exists( $file ) ) {
                return $file;
            }
        }

        return $file;
    }

    /**
     * Do export in product
     */
    public function do_product_export() {
        $post_data = wp_unslash( $_POST ); // phpcs:ignore

        if ( isset( $post_data['export_xml'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/admin.php';
            require_once plugin_dir_path( __FILE__ ) . 'includes/dokan_export.php';

            $args['content'] = $post_data['content'];
            $args['author']  = (int) get_current_user_id();

            export_wp( $args );
            die();
        }
    }

    /**
     * The main controller for the actual import stage.
     *
     * @param string $file Path to the WXR file for importing
     */
    public function import( $file ) {
        add_filter( 'import_post_meta_key', [ $this, 'is_valid_meta_key' ] );
        add_filter( 'http_request_timeout', [ &$this, 'bump_request_timeout' ] );

        require_once ABSPATH . 'wp-admin/includes/image.php';

        $this->import_start( $file );

        //$this->get_author_mapping();

        wp_suspend_cache_invalidation( true );
        $this->process_categories();
        $this->process_tags();
        $this->process_terms();
        $this->process_posts();
        wp_suspend_cache_invalidation( false );

        // update incorrect/missing information in the DB
        $this->backfill_parents();
        $this->backfill_attachment_urls();
        $this->remap_featured_images();

        $this->import_end();
    }

    /**
     * Create new categories based on import information
     *
     * Doesn't create a new category if its slug already exists
     */
    public function process_categories() {
        $this->categories = apply_filters( 'wp_import_categories', $this->categories );

        if ( empty( $this->categories ) ) {
            return;
        }

        foreach ( $this->categories as $cat ) {
            // if the category already exists leave it alone
            $term_id = term_exists( $cat['category_nicename'], 'category' );

            if ( $term_id ) {
                if ( is_array( $term_id ) ) {
                    $term_id = $term_id['term_id'];
                }

                if ( isset( $cat['term_id'] ) ) {
                    $this->processed_terms[ intval( $cat['term_id'] ) ] = (int) $term_id;
                }
                continue;
            }

            $category_parent      = empty( $cat['category_parent'] ) ? 0 : category_exists( $cat['category_parent'] );
            $category_description = isset( $cat['category_description'] ) ? $cat['category_description'] : '';
            $catarr               = [
                'category_nicename'    => $cat['category_nicename'],
                'category_parent'      => $category_parent,
                'cat_name'             => $cat['cat_name'],
                'category_description' => $category_description,
            ];

            $id = wp_insert_category( $catarr );

            if ( ! is_wp_error( $id ) ) {
                if ( isset( $cat['term_id'] ) ) {
                    $this->processed_terms[ intval( $cat['term_id'] ) ] = $id;
                }
            } else {
                // translators: %s: category name
                printf( __( 'Failed to import category %s', 'dokan' ), esc_html( $cat['category_nicename'] ) );

                if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
                    echo ': ' . $id->get_error_message();
                }
                echo '<br />';
                continue;
            }
        }

        unset( $this->categories );
    }

    /**
     * Create new post tags based on import information
     *
     * Doesn't create a tag if its slug already exists
     */
    public function process_tags() {
        $this->tags = apply_filters( 'wp_import_tags', $this->tags );

        if ( empty( $this->tags ) ) {
            return;
        }

        foreach ( $this->tags as $tag ) {
            // if the tag already exists leave it alone
            $term_id = term_exists( $tag['tag_slug'], 'post_tag' );

            if ( $term_id ) {
                if ( is_array( $term_id ) ) {
                    $term_id = $term_id['term_id'];
                }

                if ( isset( $tag['term_id'] ) ) {
                    $this->processed_terms[ intval( $tag['term_id'] ) ] = (int) $term_id;
                }
                continue;
            }

            $tag_desc = isset( $tag['tag_description'] ) ? $tag['tag_description'] : '';
            $tagarr   = [
                'slug'        => $tag['tag_slug'],
                'description' => $tag_desc,
            ];

            $id = wp_insert_term( $tag['tag_name'], 'post_tag', $tagarr );

            if ( ! is_wp_error( $id ) ) {
                if ( isset( $tag['term_id'] ) ) {
                    $this->processed_terms[ intval( $tag['term_id'] ) ] = $id['term_id'];
                }
            } else {
                // translators: %s: post tag name
                printf( __( 'Failed to import post tag %s', 'dokan' ), esc_html( $tag['tag_name'] ) );

                if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
                    echo ': ' . $id->get_error_message();
                }
                echo '<br />';
                continue;
            }
        }

        unset( $this->tags );
    }

    /**
     * Create new terms based on import information
     *
     * Doesn't create a term its slug already exists
     */
    public function process_terms() {
        $this->terms = apply_filters( 'wp_import_terms', $this->terms );

        if ( empty( $this->terms ) ) {
            return;
        }

        foreach ( $this->terms as $term ) {
            // if the term already exists in the correct taxonomy leave it alone
            $term_id = term_exists( $term['slug'], $term['term_taxonomy'] );

            if ( $term_id ) {
                if ( is_array( $term_id ) ) {
                    $term_id = $term_id['term_id'];
                }

                if ( isset( $term['term_id'] ) ) {
                    $this->processed_terms[ intval( $term['term_id'] ) ] = (int) $term_id;
                }
                continue;
            }

            if ( empty( $term['term_parent'] ) ) {
                $parent = 0;
            } else {
                $parent = term_exists( $term['term_parent'], $term['term_taxonomy'] );

                if ( is_array( $parent ) ) {
                    $parent = $parent['term_id'];
                }
            }
            $description = isset( $term['term_description'] ) ? $term['term_description'] : '';
            $termarr     = [
                'slug'        => $term['slug'],
                'description' => $description,
                'parent'      => intval( $parent ),
            ];

            $id = wp_insert_term( $term['term_name'], $term['term_taxonomy'], $termarr );

            if ( ! is_wp_error( $id ) ) {
                if ( isset( $term['term_id'] ) ) {
                    $this->processed_terms[ intval( $term['term_id'] ) ] = $id['term_id'];
                }
            } else {
                // translators: 1: taxnomy name, 2: term name
                printf( __( 'Failed to import %1$s %2$s', 'dokan' ), esc_html( $term['term_taxonomy'] ), esc_html( $term['term_name'] ) );

                if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
                    echo ': ' . $id->get_error_message();
                }
                echo '<br />';
                continue;
            }
        }

        unset( $this->terms );
    }

    /**
     * Create new posts based on import information
     *
     * Posts marked as having a parent which doesn't exist will become top level items.
     * Doesn't create a new post if: the post type doesn't exist, the given post ID
     * is already noted as imported or a post with the same title and date already exists.
     * Note that new/updated terms, comments and meta are imported for the last of the above.
     */
    public function process_posts() {
        foreach ( $this->posts as $post ) {
            $post = apply_filters( 'wp_import_post_data_raw', $post );

            if ( ! post_type_exists( $post['post_type'] ) ) {
                // translators: 1: post title, 2: post type
                printf( __( 'Failed to import &#8220;%1$s&#8221;: Invalid post type %2$s', 'dokan' ), esc_html( $post['post_title'] ), esc_html( $post['post_type'] ) );
                echo '<br />';
                do_action( 'wp_import_post_exists', $post );
                continue;
            }

            if ( isset( $this->processed_posts[ $post['post_id'] ] ) && ! empty( $post['post_id'] ) ) {
                continue;
            }

            if ( 'auto-draft' === $post['status'] ) {
                continue;
            }

            $post_type_object = get_post_type_object( $post['post_type'] );

            $post_exists = $this->post_exists( $post['post_title'], '', $post['post_date'] );

            if ( $post_exists && get_post_type( $post_exists ) === $post['post_type'] ) {
                // translators: 1: singular label name, 2: post title
                printf( __( '%1$s &#8220;%2$s&#8221; already exists.', 'dokan' ), $post_type_object->labels->singular_name, esc_html( $post['post_title'] ) );
                echo '<br />';
                $post_id         = $post_exists;
                $comment_post_id = $post_id;
            } else {
                $post_parent = (int) $post['post_parent'];

                if ( $post_parent ) {
                    // if we already know the parent, map it to the new local ID
                    if ( isset( $this->processed_posts[ $post_parent ] ) ) {
                        $post_parent = $this->processed_posts[ $post_parent ];
                        // otherwise record the parent for later
                    } else {
                        $this->post_orphans[ intval( $post['post_id'] ) ] = $post_parent;
                        $post_parent                                      = 0;
                    }
                }

                $author = (int) get_current_user_id();

                if ( 'product_variation' === $post['post_type'] ) {
                    $post_status = $post['status'];
                } else {
                    $post_status = ( 'publish' === $post['status'] ) ? dokan_get_option( 'product_status', 'dokan_selling' ) : $post['status'];
                }

                $postdata = [
                    'import_id'      => $post['post_id'],
                    'post_author'    => $author,
                    'post_date'      => $post['post_date'],
                    'post_date_gmt'  => $post['post_date_gmt'],
                    'post_content'   => $post['post_content'],
                    'post_excerpt'   => $post['post_excerpt'],
                    'post_title'     => $post['post_title'],
                    'post_status'    => $post_status,
                    'post_name'      => $post['post_name'],
                    'comment_status' => $post['comment_status'],
                    'ping_status'    => $post['ping_status'],
                    'guid'           => $post['guid'],
                    'post_parent'    => $post_parent,
                    'menu_order'     => $post['menu_order'],
                    'post_type'      => $post['post_type'],
                    'post_password'  => $post['post_password'],
                ];

                $original_post_id = $post['post_id'];
                $postdata         = apply_filters( 'wp_import_post_data_processed', $postdata, $post );

                if ( 'attachment' === $postdata['post_type'] ) {
                    $remote_url = ! empty( $post['attachment_url'] ) ? $post['attachment_url'] : $post['guid'];

                    // try to use _wp_attached file for upload folder placement to ensure the same location as the export site
                    // e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload()
                    $postdata['upload_date'] = $post['post_date'];

                    if ( isset( $post['postmeta'] ) ) {
                        foreach ( $post['postmeta'] as $meta ) {
                            if ( '_wp_attached_file' === $meta['key'] ) {
                                if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta['value'], $matches ) ) {
                                    $postdata['upload_date'] = $matches[0];
                                }
                                break;
                            }
                        }
                    }

                    $post_id         = $this->process_attachment( $postdata, $remote_url );
                    $comment_post_id = $post_id;
                } else {
                    $post_id         = wp_insert_post( $postdata, true );
                    $comment_post_id = $post_id;
                    do_action( 'wp_import_insert_post', $post_id, $original_post_id, $postdata, $post );
                }

                if ( is_wp_error( $post_id ) ) {
                    // translators: 1: singular label name, 2: post title
                    printf( __( 'Failed to import %1$s &#8220;%2$s&#8221;', 'dokan' ), $post_type_object->labels->singular_name, esc_html( $post['post_title'] ) );

                    if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
                        echo ': ' . $post_id->get_error_message();
                    }
                    echo '<br />';
                    continue;
                }

                if ( $post['is_sticky'] === 1 ) {
                    stick_post( $post_id );
                }
            }

            // map pre-import ID to local ID
            $this->processed_posts[ intval( $post['post_id'] ) ] = (int) $post_id;

            if ( ! isset( $post['terms'] ) ) {
                $post['terms'] = [];
            }

            $post['terms'] = apply_filters( 'wp_import_post_terms', $post['terms'], $post_id, $post );

            // add categories, tags and other terms
            if ( ! empty( $post['terms'] ) ) {
                $terms_to_set = [];

                foreach ( $post['terms'] as $term ) {
                    // back compat with WXR 1.0 map 'tag' to 'post_tag'
                    $taxonomy    = ( 'tag' === $term['domain'] ) ? 'post_tag' : $term['domain'];
                    $term_exists = term_exists( $term['slug'], $taxonomy );
                    $term_id     = is_array( $term_exists ) ? $term_exists['term_id'] : $term_exists;

                    if ( ! $term_id ) {
                        $t = wp_insert_term( $term['name'], $taxonomy, [ 'slug' => $term['slug'] ] );

                        if ( ! is_wp_error( $t ) ) {
                            $term_id = $t['term_id'];
                            do_action( 'wp_import_insert_term', $t, $term, $post_id, $post );
                        } else {
                            // translators: 1: taxonomy name, 2: term name
                            printf( __( 'Failed to import %1$s %2$s', 'dokan' ), esc_html( $taxonomy ), esc_html( $term['name'] ) );

                            if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
                                echo ': ' . $t->get_error_message();
                            }
                            echo '<br />';
                            do_action( 'wp_import_insert_term_failed', $t, $term, $post_id, $post );
                            continue;
                        }
                    }
                    $terms_to_set[ $taxonomy ][] = intval( $term_id );
                }

                foreach ( $terms_to_set as $tax => $ids ) {
                    $tt_ids = wp_set_post_terms( $post_id, $ids, $tax );
                    do_action( 'wp_import_set_post_terms', $tt_ids, $ids, $tax, $post_id, $post );
                }
                unset( $post['terms'], $terms_to_set );
            }

            if ( ! isset( $post['comments'] ) ) {
                $post['comments'] = [];
            }

            $post['comments'] = apply_filters( 'wp_import_post_comments', $post['comments'], $post_id, $post );

            // add/update comments
            if ( ! empty( $post['comments'] ) ) {
                $num_comments      = 0;
                $inserted_comments = [];

                foreach ( $post['comments'] as $comment ) {
                    $comment_id                                         = $comment['comment_id'];
                    $newcomments[ $comment_id ]['comment_post_ID']      = $comment_post_id;
                    $newcomments[ $comment_id ]['comment_author']       = $comment['comment_author'];
                    $newcomments[ $comment_id ]['comment_author_email'] = $comment['comment_author_email'];
                    $newcomments[ $comment_id ]['comment_author_IP']    = $comment['comment_author_IP'];
                    $newcomments[ $comment_id ]['comment_author_url']   = $comment['comment_author_url'];
                    $newcomments[ $comment_id ]['comment_date']         = $comment['comment_date'];
                    $newcomments[ $comment_id ]['comment_date_gmt']     = $comment['comment_date_gmt'];
                    $newcomments[ $comment_id ]['comment_content']      = $comment['comment_content'];
                    $newcomments[ $comment_id ]['comment_approved']     = $comment['comment_approved'];
                    $newcomments[ $comment_id ]['comment_type']         = $comment['comment_type'];
                    $newcomments[ $comment_id ]['comment_parent']       = $comment['comment_parent'];
                    $newcomments[ $comment_id ]['commentmeta']          = isset( $comment['commentmeta'] ) ? $comment['commentmeta'] : [];

                    if ( isset( $this->processed_authors[ $comment['comment_user_id'] ] ) ) {
                        $newcomments[ $comment_id ]['user_id'] = $this->processed_authors[ $comment['comment_user_id'] ];
                    }
                }
                ksort( $newcomments );

                foreach ( $newcomments as $key => $comment ) {
                    // if this is a new post we can skip the comment_exists() check
                    if ( ! $post_exists || ! $this->comment_exists( $comment['comment_author'], $comment['comment_date'] ) ) {
                        if ( isset( $inserted_comments[ $comment['comment_parent'] ] ) ) {
                            $comment['comment_parent'] = $inserted_comments[ $comment['comment_parent'] ];
                        }
                        $comment                   = wp_filter_comment( $comment );
                        $inserted_comments[ $key ] = wp_insert_comment( $comment );
                        do_action( 'wp_import_insert_comment', $inserted_comments[ $key ], $comment, $comment_post_id, $post );

                        foreach ( $comment['commentmeta'] as $meta ) {
                            $value = maybe_unserialize( $meta['value'] );
                            add_comment_meta( $inserted_comments[ $key ], $meta['key'], $value );
                        }

                        $num_comments++;
                    }
                }
                unset( $newcomments, $inserted_comments, $post['comments'] );
            }

            if ( ! isset( $post['postmeta'] ) ) {
                $post['postmeta'] = [];
            }

            $post['postmeta'] = apply_filters( 'wp_import_post_meta', $post['postmeta'], $post_id, $post );

            // add/update post meta
            if ( ! empty( $post['postmeta'] ) ) {
                foreach ( $post['postmeta'] as $meta ) {
                    $key   = apply_filters( 'import_post_meta_key', $meta['key'], $post_id, $post );
                    $value = false;

                    if ( '_edit_last' === $key ) {
                        if ( isset( $this->processed_authors[ intval( $meta['value'] ) ] ) ) {
                            $value = $this->processed_authors[ intval( $meta['value'] ) ];
                        } else {
                            $key = false;
                        }
                    }

                    if ( $key ) {
                        // export gets meta straight from the DB so could have a serialized string
                        if ( ! $value ) {
                            $value = maybe_unserialize( $meta['value'] );
                        }

                        add_post_meta( $post_id, $key, $value );
                        do_action( 'import_post_meta', $post_id, $key, $value );

                        // if the post has a featured image, take note of this in case of remap
                        if ( '_thumbnail_id' === $key ) {
                            $this->featured_images[ $post_id ] = (int) $value;
                        }
                    }
                }
            }
        }

        unset( $this->posts );
    }

    /**
     * If fetching attachments is enabled then attempt to create a new attachment
     *
     * @param array  $post Attachment post details from WXR
     * @param string $url  URL to fetch attachment from
     *
     * @return int|WP_Error Post ID on success, WP_Error otherwise
     */
    public function process_attachment( $post, $url ) {
        if ( ! $this->fetch_attachments ) {
            return new \WP_Error( 'attachment_processing_error', __( 'Fetching attachments is not enabled', 'dokan' ) );
        }

        // if the URL is absolute, but does not contain address, then upload it assuming base_site_url
        if ( preg_match( '|^/[\w\W]+$|', $url ) ) {
            $url = rtrim( $this->base_url, '/' ) . $url;
        }

        $upload = $this->fetch_remote_file( $url, $post );

        if ( is_wp_error( $upload ) ) {
            return $upload;
        }

        $info = wp_check_filetype( $upload['file'] );

        if ( $info ) {
            $post['post_mime_type'] = $info['type'];
        } else {
            return new \WP_Error( 'attachment_processing_error', __( 'Invalid file type', 'dokan' ) );
        }

        $post['guid'] = $upload['url'];

        // as per wp-admin/includes/upload.php
        $post_id = wp_insert_attachment( $post, $upload['file'] );
        wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

        // remap resized image URLs, works by stripping the extension and remapping the URL stub.
        if ( preg_match( '!^image/!', $info['type'] ) ) {
            $parts = pathinfo( $url );
            $name  = basename( $parts['basename'], ".{$parts['extension']}" ); // PATHINFO_FILENAME in PHP 5.2

            $parts_new = pathinfo( $upload['url'] );
            $name_new  = basename( $parts_new['basename'], ".{$parts_new['extension']}" );

            $this->url_remap[ $parts['dirname'] . '/' . $name ] = $parts_new['dirname'] . '/' . $name_new;
        }

        return $post_id;
    }

    /**
     * Attempt to download a remote file attachment
     *
     * @param string $url  URL of item to fetch
     * @param array  $post Attachment details
     *
     * @return array|WP_Error Local file location details on success, WP_Error otherwise
     */
    public function fetch_remote_file( $url, $post ) {
        // extract the file name and extension from the url
        $file_name = basename( $url );

        // get placeholder file in the upload dir with a unique, sanitized filename
        $upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] ); // phpcs:ignore

        if ( $upload['error'] ) {
            return new \WP_Error( 'upload_dir_error', $upload['error'] );
        }

        // fetch the remote url and write it to the placeholder file
        $headers = wp_get_http( $url, $upload['file'] );

        // request failed
        if ( ! $headers ) {
            @unlink( $upload['file'] );

            return new \WP_Error( 'import_file_error', __( 'Remote server did not respond', 'dokan' ) );
        }

        // make sure the fetch was successful
        if ( '200' !== $headers['response'] ) {
            @unlink( $upload['file'] );
            // translators: 1; response show, 2: response show
            return new \WP_Error( 'import_file_error', sprintf( __( 'Remote server returned error response %1$d %2$s', 'dokan' ), esc_html( $headers['response'] ), get_status_header_desc( $headers['response'] ) ) );
        }

        $filesize = filesize( $upload['file'] );

        if ( isset( $headers['content-length'] ) && $filesize !== $headers['content-length'] ) {
            @unlink( $upload['file'] );

            return new \WP_Error( 'import_file_error', __( 'Remote file is incorrect size', 'dokan' ) );
        }

        if ( 0 === $filesize ) {
            @unlink( $upload['file'] );

            return new \WP_Error( 'import_file_error', __( 'Zero size file downloaded', 'dokan' ) );
        }

        $max_size = (int) $this->max_attachment_size();

        if ( ! empty( $max_size ) && $filesize > $max_size ) {
            @unlink( $upload['file'] );
            // translators: %s: show max size
            return new \WP_Error( 'import_file_error', sprintf( __( 'Remote file is too large, limit is %s', 'dokan' ), size_format( $max_size ) ) );
        }

        // keep track of the old and new urls so we can substitute them later
        $this->url_remap[ $url ]                         = $upload['url'];
        $this->url_remap[ $post['guid'] ]                = $upload['url']; // r13735, really needed?
        // keep track of the destination if the remote url is redirected somewhere else
        if ( isset( $headers['x-final-location'] ) && $headers['x-final-location'] !== $url ) {
            $this->url_remap[ $headers['x-final-location'] ] = $upload['url'];
        }

        return $upload;
    }

    /**
     * Performs post-import cleanup of files and the cache
     */
    public function import_end() {
        wp_cache_flush();

        foreach ( get_taxonomies() as $tax ) {
            delete_option( "{$tax}_children" );
            _get_term_hierarchy( $tax );
        }

        wp_defer_term_counting( false );
        wp_defer_comment_counting( false );

        echo '<p>' . __( 'All done.', 'dokan' ) . ' <a href="' . dokan_get_page_url( 'products' ) . '">' . __( 'Have fun!', 'dokan' ) . '</a>' . '</p>'; // phpcs:ignore

        do_action( 'import_end' );
    }

    /**
     * Parses the WXR file and prepares us for the task of processing parsed data
     *
     * @param string $file Path to the WXR file for importing
     */
    public function import_start( $file ) {
        if ( ! is_file( $file ) ) {
            echo '<p><strong>' . __( 'Sorry, there has been an error.', 'dokan' ) . '</strong><br />';
            echo __( 'The file does not exist, please try again.', 'dokan' ) . '</p>';
            die();
        }

        $parser = new \Dokan_WXR_Parser();

        $import_data = $parser->parse( $file );

        if ( is_wp_error( $import_data ) ) {
            echo '<p><strong>' . __( 'Sorry, there has been an error.', 'dokan' ) . '</strong><br />';
            echo esc_html( $import_data->get_error_message() ) . '</p>';
            die();
        }

        $this->version = $import_data['version'];
        //$this->get_authors_from_import( $import_data );
        $this->posts      = $import_data['posts'];
        $this->terms      = $import_data['terms'];
        $this->categories = $import_data['categories'];
        $this->tags       = $import_data['tags'];
        $this->base_url   = esc_url( $import_data['base_url'] );

        wp_defer_term_counting( true );
        wp_defer_comment_counting( true );

        do_action( 'import_start' );
    }

    /**
     * Attempt to associate posts and menu items with previously missing parents
     *
     * An imported post's parent may not have been imported when it was first created
     * so try again. Similarly for child menu items and menu items which were missing
     * the object (e.g. post) they represent in the menu
     */
    public function backfill_parents() {
        global $wpdb;

        // find parents for post orphans
        foreach ( $this->post_orphans as $child_id => $parent_id ) {
            $local_parent_id = false;
            $local_child_id  = false;

            if ( isset( $this->processed_posts[ $child_id ] ) ) {
                $local_child_id = $this->processed_posts[ $child_id ];
            }

            if ( isset( $this->processed_posts[ $parent_id ] ) ) {
                $local_parent_id = $this->processed_posts[ $parent_id ];
            }

            if ( $local_child_id && $local_parent_id ) {
                $wpdb->update( $wpdb->posts, [ 'post_parent' => $local_parent_id ], [ 'ID' => $local_child_id ], '%d', '%d' );
            }
        }

        // all other posts/terms are imported, retry menu items with missing associated object
        $missing_menu_items = $this->missing_menu_items;

        foreach ( $missing_menu_items as $item ) {
            $this->process_menu_item( $item );
        }

        // find parents for menu item orphans
        foreach ( $this->menu_item_orphans as $child_id => $parent_id ) {
            $local_parent_id = 0;
            $local_child_id  = 0;

            if ( isset( $this->processed_menu_items[ $child_id ] ) ) {
                $local_child_id = $this->processed_menu_items[ $child_id ];
            }

            if ( isset( $this->processed_menu_items[ $parent_id ] ) ) {
                $local_parent_id = $this->processed_menu_items[ $parent_id ];
            }

            if ( $local_child_id && $local_parent_id ) {
                update_post_meta( $local_child_id, '_menu_item_menu_item_parent', (int) $local_parent_id );
            }
        }
    }

    /**
     * Use stored mapping information to update old attachment URLs
     */
    public function backfill_attachment_urls() {
        global $wpdb;
        // make sure we do the longest urls first, in case one is a substring of another
        uksort( $this->url_remap, [ &$this, 'cmpr_strlen' ] );

        foreach ( $this->url_remap as $from_url => $to_url ) {
            // remap urls in post_content
            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", $from_url, $to_url ) );
            // remap enclosure urls
            $result = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='enclosure'", $from_url, $to_url ) );
        }
    }

    /**
     * Update _thumbnail_id meta to new, imported attachment IDs
     */
    public function remap_featured_images() {
        // cycle through posts that have a featured image
        foreach ( $this->featured_images as $post_id => $value ) {
            if ( isset( $this->processed_posts[ $value ] ) ) {
                $new_id = $this->processed_posts[ $value ];
                // only update if there's a difference
                if ( $new_id !== $value ) {
                    update_post_meta( $post_id, '_thumbnail_id', $new_id );
                }
            }
        }
    }

    /**
     * Decide if the given meta key maps to information we will want to import
     *
     * @param string $key The meta key to check
     *
     * @return string|bool The key if we do want to import, false if not
     */
    public function is_valid_meta_key( $key ) {
        // skip attachment metadata since we'll regenerate it from scratch
        // skip _edit_lock as not relevant for import
        if ( in_array( $key, [ '_wp_attached_file', '_wp_attachment_metadata', '_edit_lock' ], true ) ) {
            return false;
        }

        return $key;
    }

    /**
     * Added to http_request_timeout filter to force timeout at 60 seconds during import
     *
     * @return int 60
     */
    public function bump_request_timeout() {
        return 60;
    }

    /**
     * Check if psot exists or not
     *
     * @param string $title   [description]
     * @param string $content [description]
     * @param string $date    [description]
     *
     * @return [type] [description]
     */
    public function post_exists( $title, $content = '', $date = '' ) {
        global $wpdb;

        $post_title   = wp_unslash( sanitize_post_field( 'post_title', $title, 0, 'db' ) );
        $post_content = wp_unslash( sanitize_post_field( 'post_content', $content, 0, 'db' ) );
        $post_date    = wp_unslash( sanitize_post_field( 'post_date', $date, 0, 'db' ) );

        $query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
        $args  = [];

        if ( ! empty( $date ) ) {
            $query .= ' AND post_date = %s';
            $args[] = $post_date;
        }

        if ( ! empty( $title ) ) {
            $query .= ' AND post_title = %s';
            $args[] = $post_title;
        }

        if ( ! empty( $content ) ) {
            $query .= 'AND post_content = %s';
            $args[] = $post_content;
        }

        if ( ! empty( $args ) ) {
            return (int) $wpdb->get_var( $wpdb->prepare( $query, $args ) );
        }

        return 0;
    }

    /**
     * Determine if a comment exists based on author and date.
     *
     * @since 2.0.0
     *
     * @uses $wpdb
     *
     * @param string $comment_author Author of the comment
     * @param string $comment_date   Date of the comment
     *
     * @return mixed comment post ID on success
     */
    public function comment_exists( $comment_author, $comment_date ) {
        global $wpdb;

        $comment_author = stripslashes( $comment_author );
        $comment_date   = stripslashes( $comment_date );

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT comment_post_ID FROM $wpdb->comments
                WHERE comment_author = %s AND comment_date = %s", $comment_author, $comment_date
            )
        );
    }

    // return the difference in length between two strings
    public function cmpr_strlen( $a, $b ) {
        return strlen( $b ) - strlen( $a );
    }

    /**
     * Decide what the maximum file size for downloaded attachments is.
     * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
     *
     * @return int Maximum attachment file size to import
     */
    public function max_attachment_size() {
        return apply_filters( 'import_attachment_size_limit', 0 );
    }

    /**
     * Ajax callback for importing one batch of products from a CSV.
     */
    public function do_ajax_product_import() {
        global $wpdb;

        check_ajax_referer( 'wc-product-import', 'security' );

        // only process request from vendor dashboard.
        if ( substr( wp_get_referer(), 0, strlen( get_admin_url() ) ) === get_admin_url() ) {
            return;
        }

        if ( ! ( current_user_can( 'edit_products' ) && dokan_is_user_seller( dokan_get_current_user_id() ) ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient privileges to import products.', 'dokan' ) ) );
        }

        if ( ! isset( $_POST['file'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No file found for product upload.', 'dokan' ) ) );
        }

        include_once DOKAN_IE_INC_DIR . '/importers/Controller.php';
        include_once DOKAN_IE_INC_DIR . '/import/ProductCsvImporter.php';

        $file   = wc_clean( wp_unslash( $_POST['file'] ) );
        $params = array(
            'delimiter'       => ! empty( $_POST['delimiter'] ) ? wc_clean( wp_unslash( $_POST['delimiter'] ) ) : ',',
            'start_pos'       => isset( $_POST['position'] ) ? absint( sanitize_key( $_POST['position'] ) ) : 0,
            'mapping'         => isset( $_POST['mapping'] ) ? (array) wc_clean( wp_unslash( $_POST['mapping'] ) ) : array(),
            'update_existing' => isset( $_POST['update_existing'] ) && (bool) $_POST['update_existing'],
            'lines'           => apply_filters( 'woocommerce_product_import_batch_size', 30 ),
            'parse'           => true,
        );

        // Log failures.
        if ( 0 !== $params['start_pos'] ) {
            $error_log = array_filter( (array) get_user_option( 'product_import_error_log' ) );
        } else {
            $error_log = [];
        }

        $importer         = new ProductCsvImporter( $file, $params );
        $results          = $importer->import();
        $percent_complete = $importer->get_percent_complete();
        $error_log        = array_merge( $error_log, $results['failed'], $results['skipped'] );

        update_user_option( get_current_user_id(), 'product_import_error_log', $error_log );

        if ( 100 === $percent_complete ) {
            // @codingStandardsIgnoreStart.
            $wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_original_id' ) );
            $wpdb->delete( $wpdb->posts, array(
                'post_type'   => 'product',
                'post_status' => 'importing',
            ) );
            $wpdb->delete( $wpdb->posts, array(
                'post_type'   => 'product_variation',
                'post_status' => 'importing',
            ) );
            // @codingStandardsIgnoreEnd.

            // Clean up orphaned data.
            $wpdb->query(
                "
				DELETE {$wpdb->posts}.* FROM {$wpdb->posts}
				LEFT JOIN {$wpdb->posts} wp ON wp.ID = {$wpdb->posts}.post_parent
				WHERE wp.ID IS NULL AND {$wpdb->posts}.post_type = 'product_variation'
			"
            );
            $wpdb->query(
                "
				DELETE {$wpdb->postmeta}.* FROM {$wpdb->postmeta}
				LEFT JOIN {$wpdb->posts} wp ON wp.ID = {$wpdb->postmeta}.post_id
				WHERE wp.ID IS NULL
			"
            );
            // @codingStandardsIgnoreStart.
            $wpdb->query( "
				DELETE tr.* FROM {$wpdb->term_relationships} tr
				LEFT JOIN {$wpdb->posts} wp ON wp.ID = tr.object_id
				LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE wp.ID IS NULL
				AND tt.taxonomy IN ( '" . implode( "','", array_map( 'esc_sql', get_object_taxonomies( 'product' ) ) ) . "' )
			" );
            // @codingStandardsIgnoreEnd.

            $server_global_referrer = wp_get_referer();
            $url_to_redirect = add_query_arg( array( '_wpnonce' => wp_create_nonce( 'woocommerce-csv-importer' ) ), dokan_get_navigation_url() . 'tools/csv-import?step=done' );
            if ( $server_global_referrer && substr( $server_global_referrer, 0, strlen( get_admin_url() ) ) === get_admin_url() ) {
                $url_to_redirect = add_query_arg( array( '_wpnonce' => wp_create_nonce( 'woocommerce-csv-importer' ) ), admin_url( 'edit.php?post_type=product&page=product_importer&step=done' ) );
            }

            // Send success.
            wp_send_json_success(
                [
                    'position'   => 'done',
                    'percentage' => 100,
                    'url'        => $url_to_redirect,
                    'imported'   => count( $results['imported'] ),
                    'failed'     => count( $results['failed'] ),
                    'updated'    => count( $results['updated'] ),
                    'skipped'    => count( $results['skipped'] ),
                ]
            );
        } else {
            wp_send_json_success(
                [
                    'position'   => $importer->get_file_position(),
                    'percentage' => $percent_complete,
                    'imported'   => count( $results['imported'] ),
                    'failed'     => count( $results['failed'] ),
                    'updated'    => count( $results['updated'] ),
                    'skipped'    => count( $results['skipped'] ),
                ]
            );
        }
    }

    /**
     * Export page UI.
     */
    public function product_exporter() {
        include_once DOKAN_IE_INC_DIR . '/export/ProductCsvExporter.php';
        include_once DOKAN_IE_INC_DIR . '/export/views/html-admin-page-product-export.php';
    }

    /**
     * Serve the generated file.
     */
    public function download_export_file() {
        if ( isset( $_GET['action'], $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'product-csv' ) && 'download_product_csv' === $_GET['action'] ) { // phpcs:ignore
            include_once DOKAN_IE_INC_DIR . '/export/ProductCsvExporter.php';
            $exporter = new ProductCsvExporter();
            $exporter->export();
        }
    }

    /**
     * AJAX callback for doing the actual export to the CSV file.
     */
    public function do_ajax_product_export() {
        check_ajax_referer( 'wc-product-export', 'security' );
        // only process request from vendor dashboard.
        if ( substr( wp_get_referer(), 0, strlen( get_admin_url() ) ) === get_admin_url() ) {
            return;
        }

        if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
            wp_die( -1 );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) && ! dokan_is_user_seller( dokan_get_current_user_id() ) ) {
            return;
        }

        include_once DOKAN_IE_INC_DIR . '/export/ProductCsvExporter.php';

        $step     = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 1; // phpcs:ignore
        $exporter = new ProductCsvExporter();

        if ( ! empty( $_POST['columns'] ) ) {
            $exporter->set_column_names( wc_clean( $_POST['columns'] ) ); // phpcs:ignore
        }

        if ( ! empty( $_POST['selected_columns'] ) ) {
            $exporter->set_columns_to_export( wc_clean( $_POST['selected_columns'] ) ); // phpcs:ignore
        }

        if ( ! empty( $_POST['export_meta'] ) ) {
            $exporter->enable_meta_export( true );
        }

        if ( ! empty( $_POST['export_types'] ) ) {
            $exporter->set_product_types_to_export( wc_clean( $_POST['export_types'] ) ); // phpcs:ignore
        }

        $exporter->set_page( $step );
        $exporter->generate_file();

        if ( 100 === $exporter->get_percent_complete() ) {
            wp_send_json_success(
                [
                    'step'       => 'done',
                    'percentage' => 100,
                    'url'        => add_query_arg(
                        [
                            'nonce'  => wp_create_nonce( 'product-csv' ),
                            'action' => 'download_product_csv',
                        ], dokan_get_navigation_url( 'tools/csv-export/' ) . '?product_exporter'
                    ),
                ]
            );
        } else {
            wp_send_json_success(
                [
                    'step'       => ++$step,
                    'percentage' => $exporter->get_percent_complete(),
                    'columns'    => $exporter->get_column_names(),
                ]
            );
        }
        exit();
    }

    /**
     * Render Import Export button on product listing
     */
    public function render_import_export_button() {
        $product_query = dokan()->product->all( [ 'author' => dokan_get_current_user_id(), 'posts_per_page' => 1 ] );
        ?>
        <?php if ( current_user_can( 'dokan_import_product' ) ) { ?>
            <a href="<?php echo dokan_get_navigation_url( 'tools/csv-import' ); ?>" class="dokan-btn">
                <?php esc_html_e( 'Import', 'dokan' ); ?>
            </a>
        <?php } ?>
        <?php if ( current_user_can( 'dokan_export_product' ) && apply_filters( 'dokan_csv_export_enabled', true ) && $product_query->have_posts() ) { ?>
            <a href="<?php echo dokan_get_navigation_url( 'tools/csv-export' ); ?>" class="dokan-btn">
                <?php esc_html_e( 'Export', 'dokan' ); ?>
            </a>
        <?php } ?>
        <?php
    }

    /**
     * Set auction active menu in dokan dashboard
     *
     * @param string $active_menu
     *
     * @return string
     */
    public function dashboard_active_menu( $active_menu ) {
        if ( 'tools/csv-import' === $active_menu || 'tools/csv-export' === $active_menu ) {
            $active_menu = 'tools';
        }

        return $active_menu;
    }

    /**
     * Change imported product status
     *
     * @param object $object
     * @param array  $item
     *
     * @since  2.8.3
     *
     * @return object
     */
    public function change_product_status( $object, $item ) {
        $can_publish    = get_user_meta( get_current_user_id(), 'dokan_publishing', true );
        $product_status = dokan_get_option( 'product_status', 'dokan_selling' );

        // if uploading pending product make it pending
        if ( 'draft' === $object->get_status() ) {
            $object->set_status( 'pending' );
        }

        // if new product status set to pending then make product status to pending
        if ( 'pending' === $product_status && 'yes' !== $can_publish ) {
            $object->set_status( 'pending' );
        }

        if ( 'variation' === $object->get_type() ) {
            $object->set_status( 'publish' ); // Variations cannot be drafts - set to public.
        }

        return $object;
    }

    /**
     * Bind global ajax url
     *
     * @since 0.5
     *
     * @return void
     */
    public function bind_global_ajaxurl() {
        global $wp;

        if ( isset( $wp->query_vars['tools'] ) ) {
            ?>
                <script>
                    window.ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                </script>
            <?php
        }
    }

    /**
     * CSV import is_feature to false if not admin
     *
     * @param $data
     *
     * @return $data
     */
    public function feature_column_to_false( $data ) {
        if ( ! wc_current_user_has_role( 'administrator' ) ) {
            $data['featured'] = false;
        }

        return $data;
    }

    /**
     * Non scalar to scalar due to wc export escape non-scalar value
     *
     * @param $meta_value
     * @param $meta
     *
     * @return $string
     */
    public function non_neumeric_wholesale_handle( $meta_value, $meta ) {
        if ( ! is_scalar( $meta_value ) && $meta->key === '_dokan_wholesale_meta' ) {
            if ( $meta_value['enable_wholesale'] ) {
                $meta_value = 'price:' . $meta_value['price'] . ',quantity:' . $meta_value['quantity'];
            }
        }

        return $meta_value;
    }

    /**
     * Prevent vendor create new category from product import.
     *
     * @since 3.4.0
     *
     * @param string|WP_Error $term     The term name to add, or a WP_Error object if there's an error.
     * @param string          $taxonomy Taxonomy slug.
     *
     * @return array|WP_Error
     */
    public function protect_vendor_create_category( $term, $taxonomy ) {
        if ( ! current_user_can( 'manage_options' ) && $taxonomy === 'product_cat' && ! get_term_by( 'name', $term, 'product_cat' ) ) {
            return new \WP_Error( 'invalid_vendor_cat', __( 'You do not have permission to create product category.', 'dokan' ) );
        }

        return $term;
    }
}
