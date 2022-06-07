<?php

/**
 * Tempalte shortcode class file
 *
 * @load all shortcode for template  rendering
 */
class Dokan_Template_Auction {

    public static $errors;
    public static $product_cat;
    public static $post_content;
    public static $validated;
    public static $validate;

    /**
     * __construct function
     *
     * @since 1.0.0
     */
    function __construct() {
        add_action( 'template_redirect', array( $this, 'auction_handle_all_submit' ), 11 );
        add_action( 'template_redirect', array( $this, 'handle_auction_product_delete' ) );
        add_action( 'dokan_auction_after_general_options', array( $this, 'load_attribute_options' ), 12 );
        add_action( 'dokan_auction_after_general_options', array( $this, 'load_shipping_options' ), 13 );

        // Remove `load_inventory_template` hook and add inventory template only for auction product
        add_action( 'init', [ $this, 'replace_auction_inventory_template' ], 10, 2 );
    }

    /**
     * Initializes the Dokan_Template_Auction() class
     *
     * Checks for an existing Dokan_Template_Auction() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Template_Auction();
        }

        return $instance;
    }

    /**
    * Load attribute templates
    *
    * @since 1.5.2
    *
    * @return void
    **/
    public function load_attribute_options( $post_id ) {
        $product_attributes   = get_post_meta( $post_id, '_product_attributes', true );
        $attribute_taxonomies = wc_get_attribute_taxonomies();

        dokan_get_template_part( 'auction/html-auction-attribute', '', array(
            'is_auction'           => true,
            'post_id'              => $post_id,
            'product_attributes'   => $product_attributes,
            'attribute_taxonomies' => $attribute_taxonomies,
        ) );
    }

    /**
    * Load Shipping templates
    *
    * @since 1.5.2
    *
    * @return void
    **/
    public function load_shipping_options( $post_id ) {
        $is_shipping_disabled = false;

        if ( 'sell_digital' === dokan_pro()->digital_product->get_selling_product_type() ) {
            $is_shipping_disabled = true;
        }

        dokan_get_template_part( 'auction/auction-shipping', '', array(
            'is_auction'           => true,
            'post_id'              => $post_id,
            'is_shipping_disabled' => $is_shipping_disabled,
        ) );
    }

    /**
     * Saving handle for auction data
     *
     * @since  1.0.0
     *
     * @return void
     */
    function auction_handle_all_submit() {
        if ( !is_user_logged_in() ) {
            return;
        }

        if ( !dokan_is_user_seller( get_current_user_id() ) ) {
            return;
        }

        $errors = array();
        self::$product_cat = -1;
        self::$post_content = '';

        if ( ! $_POST ) {
            return;
        }

        $data = wp_unslash( $_POST );
        $data['product_type'] = 'auction';

        global $woocommerce_auctions;

        if ( isset( $_POST['add_auction_product'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_add_new_auction_product_nonce'] ) ), 'dokan_add_new_auction_product' ) ) {
            if ( ! current_user_can( 'dokan_add_auction_product' ) ) {
                return;
            }

            $post_title     = isset( $_POST['post_title'] ) ? trim( wc_clean( wp_unslash( $_POST['post_title'] ) ) ) : '';
            $post_content   = isset( $_POST['post_content'] ) ? wp_kses_post( $_POST['post_content'] ) : '';
            $post_excerpt   = isset( $_POST['post_excerpt'] ) ? wp_kses_post( $_POST['post_excerpt'] ) : '';
            $product_cat    = isset( $_POST['product_cat'] ) ? absint( wp_unslash( $_POST['product_cat'] ) ) : '';
            $featured_image = isset( $_POST['feat_image_id'] ) ? absint( wp_unslash( $_POST['feat_image_id'] ) ) : '';

            if ( empty( $post_title ) ) {
                $errors[] = __( 'Please enter product title', 'dokan' );
            }

            if ( $product_cat < 0 ) {
                $errors[] = __( 'Please select a category', 'dokan' );
            }

            self::$errors = apply_filters( 'dokan_can_add_product', $errors );

            if ( !self::$errors ) {
                $product_status = dokan_get_new_post_status();
                $post_data = apply_filters( 'dokan_insert_auction_product_post_data', array(
                        'post_type'    => 'product',
                        'post_status'  => $product_status,
                        'post_title'   => $post_title,
                        'post_content' => $post_content,
                        'post_excerpt' => $post_excerpt,
                        'post_author'  => dokan_get_current_user_id()
                    ) );

                $product_id = wp_insert_post( $post_data );

                if ( $product_id ) {

                    // Set featured images
                    if ( $featured_image ) {
                        set_post_thumbnail( $product_id, $featured_image );
                    }

                    // Set Gallery Images
                    if ( !empty( $_POST['product_image_gallery'] ) ) {
                        $attachment_ids = array_filter( explode( ',', wc_clean( $_POST['product_image_gallery'] ) ) );
                        update_post_meta( $product_id, '_product_image_gallery', implode( ',', $attachment_ids ) );
                    }

                     /** set product category * */
                    if( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) == 'single' ) {
                        wp_set_object_terms( $product_id, absint( wp_unslash( $_POST['product_cat'] ) ), 'product_cat' );
                    } else {
                        if( isset( $_POST['product_cat'] ) && !empty( $_POST['product_cat'] ) ) {
                            $cat_ids = array_map( 'intval', (array)$_POST['product_cat'] );
                            wp_set_object_terms( $product_id, $cat_ids, 'product_cat' );
                        }
                    }

                    // Set Product tags
                    if( isset( $_POST['product_tag'] ) ) {
                        $tags_ids = array_map( 'intval', (array)$_POST['product_tag'] );
                    } else {
                        $tags_ids = array();
                    }
                    wp_set_object_terms( $product_id, $tags_ids, 'product_tag' );

                    // Set product type
                    wp_set_object_terms( $product_id, 'auction', 'product_type' );

                    // Save downloadable
                    $is_downloadable = isset( $data['_downloadable'] ) ? 'yes' : 'no';

                    $auction_product = new \WC_Product_Auction( $product_id );

                    $auction_product->set_downloadable( $is_downloadable );

                    // Downloadable options
                    if ( 'yes' === $is_downloadable ) {

                        // file paths will be stored in an array keyed off md5(file path)
                        if ( ! empty( $data['_wc_file_urls'] ) ) {
                            $files = [];

                            $file_names    = ! empty( $data['_wc_file_names'] ) ? array_map( 'sanitize_file_name', $data['_wc_file_names'] ) : [];
                            $file_urls     = array_map( 'esc_url_raw', array_map( 'trim', $data['_wc_file_urls'] ) );

                            foreach ( $file_urls as $index => $url ) {
                                $files[] = [
                                    'download_id' => md5( $file_urls[ $index ] ),
                                    'name'        => $file_names[ $index ],
                                    'file'        => $url,
                                ];
                            }

                            // grant permission to any newly added files on any existing orders for this product prior to saving
                            $variation_id = 0;

                            do_action( 'dokan_process_file_download', $product_id, $variation_id, $files );

                            $auction_product->set_downloads( $files );
                        } else {
                            $auction_product->set_downloads( [] );
                        }

                        if ( ! empty( $data['_download_limit'] ) ) {
                            $download_limit = absint( wp_unslash( $data['_download_limit'] ) );

                            $auction_product->set_download_limit( $download_limit );
                        }

                        if ( ! empty( $data['_download_expiry'] ) ) {
                            $download_expiry = absint( wp_unslash( $data['_download_expiry'] ) );

                            $auction_product->set_download_expiry( $download_expiry );
                        }

                        if ( ! empty( $data['_download_type'] ) ) {
                            $download_type = wc_clean( wp_unslash( $data['_download_type'] ) );

                            $auction_product->update_meta_data( '_download_type', $download_type );
                        }
                    }

                    // Virtual options
                    $is_virtual = isset( $data['_virtual'] ) ? 'yes' : 'no';

                    $auction_product->set_virtual( $is_virtual );

                    // Dimensions
                    if ( 'no' === $is_virtual ) {
                        if ( ! empty( $data['_weight'] ) ) {
                            $weight = wc_format_decimal( wp_unslash( $data['_weight'] ) );

                            $auction_product->set_weight( $weight );
                        }

                        if ( ! empty( $data['_length'] ) ) {
                            $length = wc_format_decimal( wp_unslash( $data['_length'] ) );

                            $auction_product->set_length( $length );
                        }

                        if ( ! empty( $data['_width'] ) ) {
                            $width = wc_format_decimal( wp_unslash( $data['_width'] ) );

                            $auction_product->set_width( $width );
                        }

                        if ( ! empty( $data['_height'] ) ) {
                            $height = wc_format_decimal( wp_unslash( $data['_height'] ) );

                            $auction_product->set_height( $height );
                        }
                    }

                    //Save shipping meta data
                    $disable_shipping = ! empty( $post_data['_disable_shipping'] ) ? sanitize_term_field( wp_unslash( $post_data['_disable_shipping'] ) ) : 'no';

                    // _disable_shipping does not have setter method
                    $auction_product->update_meta_data( '_disable_shipping', $disable_shipping );

                    // Save shipping class
                    $shipping_class_id = ( isset( $data['product_shipping_class'] ) && $data['product_shipping_class'] > 0 && 'external' !== $data['product_type'] ) ? absint( wp_unslash( $data['product_shipping_class'] ) ) : '';

                    $auction_product->set_shipping_class_id( $shipping_class_id );

                    if ( isset( $data['_tax_status'] ) ) {
                        $_tax_status = sanitize_text_field( wp_unslash( $data['_tax_status'] ) );

                        $auction_product->set_tax_status( $_tax_status );
                    }

                    if ( isset( $data['_tax_class'] ) ) {
                        $_tax_class = sanitize_text_field( wp_unslash( $data['_tax_class'] ) );

                        $auction_product->set_tax_class( $_tax_class );
                    }

                    $auction_product->save();

                    $woocommerce_auctions->product_save_data( $product_id, get_post( $product_id ) );

                    do_action( 'dokan_new_auction_product_added', $product_id, $post_data );

                    // dokan()->email->new_product_added( $product_id, $product_status );

                    do_action( 'dokan_new_product_added', $product_id, $data );

                    if ( current_user_can( 'dokan_edit_auction_product' ) ) {
                        $redirect_url = add_query_arg( array('product_id' => $product_id, 'action' => 'edit', 'message' => 'success' ), dokan_get_navigation_url('auction') );
                    } else {
                        $redirect_url = dokan_get_navigation_url('auction');
                    }

                    wp_redirect( $redirect_url );
                    exit;
                }
            }
        }

        // Edit handle in auction product
        if ( isset( $_GET['product_id'] ) ) {
            $post_id = intval( $_GET['product_id'] );
        } else {
            global $post, $product;
            if ( !empty( $post ) ) {
                $post_id = $post->ID;
            }
        }

        if ( isset( $_POST['update_auction_product'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_edit_auction_product_nonce'] ) ), 'dokan_edit_auction_product' ) ) {
            if ( ! current_user_can( 'dokan_edit_auction_product' ) ) {
                return;
            }

            $product_info = array(
                'ID'             => absint( $post_id ),
                'post_title'     => wc_clean( wp_unslash( $_POST['post_title'] ) ),
                'post_content'   => wp_kses_post( $_POST['post_content'] ),
                'post_excerpt'   => wp_kses_post( $_POST['post_excerpt'] ),
                'post_status'    => isset( $_POST['post_status'] ) ? wc_clean( wp_unslash( $_POST['post_status'] ) ) : 'pending',
                'comment_status' => isset( $_POST['_enable_reviews'] ) ? 'open' : 'closed'
            );

            wp_update_post( $product_info );

            /** set product category * */
            if( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) == 'single' ) {
                wp_set_object_terms( $post_id, absint( $_POST['product_cat'] ), 'product_cat' );
            } else {
                if( isset( $_POST['product_cat'] ) && !empty( $_POST['product_cat'] ) ) {
                    $cat_ids = array_map( 'intval', (array)$_POST['product_cat'] );
                    wp_set_object_terms( $post_id, $cat_ids, 'product_cat' );
                }
            }

            wp_set_object_terms( $post_id, 'auction', 'product_type' );

            /** Set Product tags */
            if( isset( $_POST['product_tag'] ) ) {
                $tags_ids = array_map( 'intval', (array)$_POST['product_tag'] );
            } else {
                $tags_ids = array();
            }
            wp_set_object_terms( $post_id, $tags_ids, 'product_tag' );

            // Handle visibility ( with WC 3.0.0+ compatibility )
            $terms = array();
            $_visibility = isset( $_POST['_visibility'] ) ? wc_clean( wp_unslash( $_POST['_visibility'] ) ) : '';
            switch ( $_visibility ) {
                case 'hidden' :
                    $terms[] = 'exclude-from-search';
                    $terms[] = 'exclude-from-catalog';
                    break;
                case 'catalog' :
                    $terms[] = 'exclude-from-search';
                    break;
                case 'search' :
                    $terms[] = 'exclude-from-catalog';
                    break;
            }

            wp_set_post_terms( $post_id, $terms, 'product_visibility', false );
            update_post_meta( $post_id, '_visibility', $_visibility );

            /** set images **/
            $featured_image = absint( wp_unslash( $_POST['feat_image_id'] ) );
            if ( $featured_image ) {
                set_post_thumbnail( $post_id, $featured_image );
            } else {
                delete_post_thumbnail( $post_id );
            }

            // Gallery Images
            $attachment_ids = array_filter( explode( ',', wc_clean( $_POST['product_image_gallery'] ) ) );
            update_post_meta( $post_id, '_product_image_gallery', implode( ',', $attachment_ids ) );

            $woocommerce_auctions->product_save_data( $post_id, get_post( $post_id ) );

            // Save Attributes
            $attributes = array();

            if ( isset( $_POST['attribute_names'] ) && isset( $_POST['attribute_values'] ) ) {

                $attribute_names  = wc_clean( wp_unslash( $_POST['attribute_names'] ) );
                $attribute_values = wc_clean( wp_unslash( $_POST['attribute_values'] ) );

                if ( isset( $_POST['attribute_visibility'] ) ) {
                    $attribute_visibility = wc_clean( wp_unslash( $_POST['attribute_visibility'] ) );
                }

                if ( isset( $_POST['attribute_variation'] ) ) {
                    $attribute_variation = wc_clean( wp_unslash( $_POST['attribute_variation'] ) );
                }

                $attribute_is_taxonomy   = wc_clean( wp_unslash( $_POST['attribute_is_taxonomy'] ) );
                $attribute_position      = wc_clean( wp_unslash( $_POST['attribute_position'] ) );
                $attribute_names_max_key = max( array_keys( $attribute_names ) );

                for ( $i = 0; $i <= $attribute_names_max_key; $i++ ) {
                    if ( empty( $attribute_names[ $i ] ) ) {
                        continue;
                    }

                    $is_visible   = isset( $attribute_visibility[ $i ] ) ? 1 : 0;
                    $is_variation = isset( $attribute_variation[ $i ] ) ? 1 : 0;
                    $is_taxonomy  = $attribute_is_taxonomy[ $i ] ? 1 : 0;

                    if ( $is_taxonomy ) {

                        $values_are_slugs = false;

                        if ( isset( $attribute_values[ $i ] ) ) {

                            // Select based attributes - Format values (posted values are slugs)
                            if ( is_array( $attribute_values[ $i ] ) ) {
                                $values           = array_map( 'sanitize_title', $attribute_values[ $i ] );
                                $values_are_slugs = true;

                            // Text based attributes - Posted values are term names - don't change to slugs
                            } else {
                                $values = array_map( 'stripslashes', array_map( 'strip_tags', explode( WC_DELIMITER, $attribute_values[ $i ] ) ) );
                            }

                            // Remove empty items in the array
                            $values = array_filter( $values, 'strlen' );

                        } else {
                            $values = array();
                        }

                        // Update post terms
                        if ( taxonomy_exists( $attribute_names[ $i ] ) ) {

                            foreach ( $values as $key => $value ) {
                                $term = get_term_by( $values_are_slugs ? 'slug' : 'name', trim( $value ), $attribute_names[ $i ] );

                                if ( $term ) {
                                    $values[ $key ] = intval( $term->term_id );
                                } else {
                                    $term = wp_insert_term( trim( $value ), $attribute_names[ $i ] );
                                    if ( isset( $term->term_id ) ) {
                                        $values[ $key ] = intval( $term->term_id );
                                    }
                                }
                            }

                            wp_set_object_terms( $post_id, $values, $attribute_names[ $i ] );
                        }

                        if ( ! empty( $values ) ) {
                            // Add attribute to array, but don't set values
                            $attributes[ sanitize_title( $attribute_names[ $i ] ) ] = array(
                                'name'         => wc_clean( $attribute_names[ $i ] ),
                                'value'        => '',
                                'position'     => $attribute_position[ $i ],
                                'is_visible'   => $is_visible,
                                'is_variation' => $is_variation,
                                'is_taxonomy'  => $is_taxonomy,
                            );
                        }
                    } elseif ( isset( $attribute_values[ $i ] ) ) {

                        // Text based, possibly separated by pipes (WC_DELIMITER). Preserve line breaks in non-variation attributes.
                        $values = implode( ' ' . WC_DELIMITER . ' ', array_map( 'wc_clean', array_map( 'stripslashes', $attribute_values[ $i ] ) ) );

                        // Custom attribute - Add attribute to array and set the values
                        $attributes[ sanitize_title( $attribute_names[ $i ] ) ] = array(
                            'name'         => wc_clean( $attribute_names[ $i ] ),
                            'value'        => $values,
                            'position'     => $attribute_position[ $i ],
                            'is_visible'   => $is_visible,
                            'is_variation' => $is_variation,
                            'is_taxonomy'  => $is_taxonomy,
                        );
                    }
                }
            }
            uasort( $attributes, 'wc_product_attribute_uasort_comparison' );

            /**
             * Unset removed attributes by looping over previous values and
             * unsetting the terms.
             */
            $old_attributes = array_filter( (array) maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) ) );

            if ( ! empty( $old_attributes ) ) {
                foreach ( $old_attributes as $key => $value ) {
                    if ( empty( $attributes[ $key ] ) && ! empty( $value['is_taxonomy'] ) && taxonomy_exists( $key ) ) {
                        wp_set_object_terms( $post_id, array(), $key );
                    }
                }
            }

            update_post_meta( $post_id, '_product_attributes', $attributes );

           // Dimensions
            if ( isset( $_POST['_weight'] ) ) {
                update_post_meta( $post_id, '_weight', ( '' === $_POST['_weight'] ) ? '' : wc_format_decimal( wc_clean( wp_unslash( $_POST['_weight'] ) ) )  );
            }

            if ( isset( $_POST['_length'] ) ) {
                update_post_meta( $post_id, '_length', ( '' === $_POST['_length'] ) ? '' : wc_format_decimal( wc_clean( wp_unslash( $_POST['_length'] ) ) )  );
            }

            if ( isset( $_POST['_width'] ) ) {
                update_post_meta( $post_id, '_width', ( '' === $_POST['_width'] ) ? '' : wc_format_decimal( wc_clean( wp_unslash( $_POST['_width'] ) ) )  );
            }

            if ( isset( $_POST['_height'] ) ) {
                update_post_meta( $post_id, '_height', ( '' === $_POST['_height'] ) ? '' : wc_format_decimal( wc_clean( wp_unslash( $_POST['_height'] ) ) )  );
            }

            //Save shipping meta data
            update_post_meta( $post_id, '_disable_shipping', stripslashes( isset( $_POST['_disable_shipping'] ) ? wc_clean( wp_unslash( $_POST['_disable_shipping'] ) ) : 'no' ) );

            if ( isset( $_POST['_overwrite_shipping'] ) && $_POST['_overwrite_shipping'] == 'yes' ) {
                update_post_meta( $post_id, '_overwrite_shipping', stripslashes( $_POST['_overwrite_shipping'] ) );
            } else {
                update_post_meta( $post_id, '_overwrite_shipping', 'no' );
            }

            update_post_meta( $post_id, '_additional_price', stripslashes( isset( $_POST['_additional_price'] ) ? wc_clean( wp_unslash( $_POST['_additional_price'] ) ) : ''  ) );
            update_post_meta( $post_id, '_additional_qty', stripslashes( isset( $_POST['_additional_qty'] ) ? wc_clean( wp_unslash( $_POST['_additional_qty'] ) ) : ''  ) );
            update_post_meta( $post_id, '_dps_processing_time', stripslashes( isset( $_POST['_dps_processing_time'] ) ? wc_clean( wp_unslash( $_POST['_dps_processing_time'] ) ) : ''  ) );

            // Update auction date
            $auction_dates_to   = isset( $_POST['_auction_dates_to'] ) ? sanitize_text_field( wp_unslash( $_POST['_auction_dates_to'] ) ) : '';
            $auction_dates_from = isset( $_POST['_auction_dates_from'] ) ? sanitize_text_field( wp_unslash( $_POST['_auction_dates_from'] ) ) : '';

            if ( ! empty( $_POST['_relist_auction_dates_from'] ) && ! empty( $_POST['_relist_auction_dates_to'] ) ) {
                // Set relisted dates data.
                $auction_dates_to   = sanitize_text_field( wp_unslash( $_POST['_relist_auction_dates_to'] ) );
                $auction_dates_from = sanitize_text_field( wp_unslash( $_POST['_relist_auction_dates_from'] ) );

                // Update auction relisted data.
                $this->relist_auction( $post_id, $auction_dates_to, $auction_dates_from );
            }

            update_post_meta( $post_id, '_auction_dates_to', $auction_dates_to );
            update_post_meta( $post_id, '_auction_dates_from', $auction_dates_from );

            if ( strtotime( $auction_dates_to ) >= time() ) {
                delete_post_meta( $post_id, '_auction_closed' );
            }

            // Save shipping class
            $product_shipping_class = ( isset( $_POST['product_shipping_class'] ) && $_POST['product_shipping_class'] > 0 && 'external' !== $product_type ) ? absint( wp_unslash( $_POST['product_shipping_class'] ) ) : '';
            wp_set_object_terms( $post_id, $product_shipping_class, 'product_shipping_class' );

            // Update SKU
            $old_sku = get_post_meta( $post_id, '_sku', true );
            delete_post_meta( $post_id, '_sku' );

            $product = wc_get_product( $post_id );

            $sku = trim( wp_unslash( $_POST['_sku'] ) ) !== '' ? sanitize_text_field( wp_unslash( $_POST['_sku'] ) ) : '';
            try {
                $product->set_sku( $sku );
            } catch ( \WC_Data_Exception $e ) {
                wc_add_notice( __( 'Same SKU can not be set for multiple products! The SKU has been restored to previous one.', 'dokan' ), 'error' );
                $product->set_sku( $old_sku );
            }

            // Save virtual
            $_virtual   = isset( $_POST['_virtual'] ) ? wc_clean( wp_unslash( $_POST['_virtual'] ) ) : '';
            $is_virtual = 'on' === $_virtual ? 'yes' : 'no';
            $product->set_virtual( $is_virtual );

            // Save downloadable
            $is_downloadable   = isset( $_POST['_downloadable'] ) ? wc_clean( wp_unslash( $_POST['_downloadable'] ) ) : '';
            $is_downloadable = 'on' === $is_downloadable ? 'yes' : 'no';
            $product->set_downloadable( $is_downloadable );

            $product->save();

            do_action( 'dokan_update_auction_product', $post_id, wp_unslash( $_POST ) );

            do_action( 'dokan_product_updated', $post_id, $data );

            $edit_url = add_query_arg( array('product_id' => $post_id, 'action' => 'edit' ), dokan_get_navigation_url('auction') );
            wp_redirect( add_query_arg( array( 'message' => 'success' ), $edit_url ) );
            exit;
        }
    }

    /**
     * Relist our auction & update relisted data.
     *
     * @since 3.5.0
     *
     * @param int    $post_id
     * @param string $relist_from
     * @param string $relist_to
     *
     * @return void
     */
    public function relist_auction( $post_id, $relist_from, $relist_to ) {
        global $wpdb;

        // Get auction product.
        $auction_product = wc_get_product( $post_id );

        // Set auction metas for updated.
        $update_auction_metas = [
            '_stock'              => '1',
            '_backorders'         => 'no',
            '_stock_status'       => 'instock',
            '_manage_stock'       => 'yes',
            '_auction_dates_to'   => stripslashes( $relist_to ),
            '_auction_relisted'   => current_time( 'mysql' ),
            '_sold_individually'  => 'yes',
            '_auction_dates_from' => stripslashes( $relist_from ),
        ];

        // Set auction metas for deleted.
        $delete_auction_metas = [
            '_stop_mails',
            '_auction_closed',
            '_auction_max_bid',
            '_auction_started',
            '_auction_bid_count',
            '_auction_fail_reason',
            '_auction_current_bid',
            '_auction_current_bider',
            '_auction_max_current_bider',
        ];

        // Update auction metas for relisted auction.
        $auction_product->set_props( $update_auction_metas );

        // Delete auction metas for relisted auction.
        foreach ( $delete_auction_metas as $auction_meta_key ) {
            $auction_product->delete_meta_data( $auction_meta_key );
        }

        $order_id = get_post_meta( $post_id, 'order_id', true );
        $order    = wc_get_order( $order_id );

        if ( $order instanceof \WC_Order ) {
            $order->update_status( 'failed', __( 'Failed because off relisting', 'dokan' ) );
            delete_post_meta( $post_id, '_order_id' );
        }

        // @codingStandardsIgnoreLine
        $wpdb->delete( $wpdb->usermeta, [ 'meta_key' => 'wsa_my_auctions', 'meta_value' => $post_id ], [ '%s', '%d' ] );

        /**
         * Fires after auction product relisted.
         *
         * @since 3.5.0
         *
         * @param \WC_Product $auction_product
         * @param string      $relist_from
         * @param string      $relist_to
         */
        do_action( 'dokan_do_simple_auction_relist', $auction_product, $relist_from, $relist_to );
    }

    /**
     * Handle auction product delete
     *
     * @since  1.0.0
     *
     * @return void
     */
    function handle_auction_product_delete() {
        if ( !is_user_logged_in() ) {
            return;
        }

        if ( !dokan_is_user_seller( get_current_user_id() ) ) {
            return;
        }

        if ( ! current_user_can( 'dokan_delete_auction_product' ) ) {
            return;
        }

        if ( isset( $_GET['action'] ) && $_GET['action'] == 'dokan-delete-auction-product' ) {
            $product_id = isset( $_GET['product_id'] ) ? intval( $_GET['product_id'] ) : 0;

            if ( !$product_id ) {
                wp_redirect( add_query_arg( array( 'message' => 'error' ), dokan_get_navigation_url( 'auction' ) ) );
                return;
            }

            if ( !wp_verify_nonce( $_GET['_wpnonce'], 'dokan-delete-auction-product' ) ) {
                wp_redirect( add_query_arg( array( 'message' => 'error' ), dokan_get_navigation_url( 'auction' ) ) );
                return;
            }

            if ( !dokan_is_product_author( $product_id ) ) {
                wp_redirect( add_query_arg( array( 'message' => 'error' ), dokan_get_navigation_url( 'auction' ) ) );
                return;
            }

            wp_delete_post( $product_id );

            /**
             * Fires Auction Product Deleted Action
             *
             * @since 3.4.2
             *
             * @param int $product_id
             */
            do_action( 'dokan_delete_auction_product', $product_id );

            wp_redirect( add_query_arg( array( 'message' => 'product_deleted' ), dokan_get_navigation_url( 'auction' ) ) );
            exit;
        }

    }

    /**
     * Replace action inventory template
     *
     * @since  3.2.2
     *
     * @return void
     */
    public function replace_auction_inventory_template() {
        $product_id = ! empty( $_GET['product_id'] ) ? wp_unslash( $_GET['product_id'] ) : 0;
        $product    = wc_get_product( $product_id );

        if ( $product && 'auction' === $product->get_type() ) {
            remove_action( 'dokan_product_edit_after_main', [ \WeDevs\Dokan\Dashboard\Templates\Products::class, 'load_inventory_template' ], 5 );
            add_action( 'dokan_product_edit_after_main', [ $this, 'load_inventory_template' ], 5, 2 );
        }
    }

    /**
     * Load inventory template
     *
     * @since  3.2.2
     *
     * @param WP_Post $post
     * @param int $post_id
     *
     * @return void
     */
    public function load_inventory_template( $post, $post_id ) {
        dokan_get_template_part( 'auction/auction-inventory', '', [
            'post_id'    => $post_id,
            'is_auction' => true,
        ] );
    }

}

Dokan_Template_Auction::init();
