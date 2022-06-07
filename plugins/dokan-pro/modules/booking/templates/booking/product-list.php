<?php

use WeDevs\Dokan\Cache;

global $post;

// @codingStandardsIgnoreStart
/*
 *  dokan_dashboard_content_before hook
 *
 *  @hooked get_dashboard_side_navigation
 *
 *  @since 2.4
 */
do_action( 'dokan_dashboard_content_inside_before' );
do_action( 'dokan_before_listing_product' );
?>
<header class="dokan-dashboard-header">
    <?php if ( dokan_is_seller_enabled( get_current_user_id() ) && current_user_can( 'dokan_add_booking_product' ) ) { ?>
    <h1 class="entry-title"><?php echo esc_html( $title ); ?>
        <span class="dokan-add-product-link">
            <?php if ( ! Dokan_WC_Booking_Helper::is_global_addon_rma_active() ): ?>
            <a href="<?php echo esc_url( $booking_url . 'add-booking' ); ?>" class="dokan-btn dokan-right dokan-add-new-product"><i class="far fa-calendar-alt"></i> <?php esc_html_e( 'Add Booking', 'dokan' ); ?></a>
            <?php endif; ?>

            <a href="<?php echo esc_url( $booking_url . 'new-product' ); ?>" class="dokan-btn dokan-btn-theme dokan-right dokan-add-new-product"><i class="fas fa-briefcase">&nbsp;</i> <?php esc_html_e( 'Add New Booking Product', 'dokan' ); ?></a>
        </span>
    </h1>
    <?php } ?>
</header><!-- .dokan-dashboard-header -->

<div class="dokan-product-listing">

    <article class="dokan-product-listing-area">

        <?php dokan_product_dashboard_errors(); ?>

        <div class="dokan-w12">
            <?php dokan_product_listing_filter(); ?>
        </div>

        <table class="dokan-table dokan-table-striped product-listing-table">
            <thead>
                <tr>
                    <th><?php _e( 'Image', 'dokan' ); ?></th>
                    <th><?php _e( 'Name', 'dokan' ); ?></th>
                    <th><?php _e( 'Status', 'dokan' ); ?></th>
                    <th><?php _e( 'SKU', 'dokan' ); ?></th>
                    <th><?php _e( 'Stock', 'dokan' ); ?></th>
                    <th><?php _e( 'Price', 'dokan' ); ?></th>
                    <th><?php _e( 'Type', 'dokan' ); ?></th>
                    <th><?php _e( 'Views', 'dokan' ); ?></th>
                    <th><?php _e( 'Date', 'dokan' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pagenum      = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;

                $post_statuses = ['publish', 'draft', 'pending'];
                $args          = [
                    'post_type'      => 'product',
                    'post_status'    => $post_statuses,
                    'posts_per_page' => 10,
                    'author'         => dokan_get_current_user_id(),
                    'orderby'        => 'post_date',
                    'order'          => 'DESC',
                    'paged'          => $pagenum,
                    'tax_query'      => [
                        [
                            'taxonomy' => 'product_type',
                            'field'    => 'slug',
                            'terms'    => 'booking',
                        ],
                    ],
                ];

                if ( isset( $_GET['post_status'] ) && in_array( $_GET['post_status'], $post_statuses ) ) {
                    $args['post_status'] = $_GET['post_status'];
                }

                if ( isset( $_GET['date'] ) && $_GET['date'] != 0 ) {
                    $args['m'] = $_GET['date'];
                }

                if ( isset( $_GET['product_cat'] ) && $_GET['product_cat'] != -1 ) {
                    $args['tax_query']= [
                        'relation' => 'and',
                        [
                            'taxonomy'         => 'product_cat',
                            'field'            => 'id',
                            'terms'            => (int) $_GET['product_cat'],
                            'include_children' => false,
                        ],
                        [
                            'taxonomy' => 'product_type',
                            'field'    => 'slug',
                            'terms'    => 'booking',
                        ],
                    ];
                }

                if ( isset( $_GET['product_search_name'] ) && !empty( $_GET['product_search_name'] ) ) {
                    $args['s'] = $_GET['product_search_name'];
                }

                $original_post = $post;

                $cache_group   = "booking_products_{$args['author']}";
                $cache_key     = 'products_' . md5( wp_json_encode( $args ) );
                $product_query = Cache::get( $cache_key, $cache_group );

                if ( false === $product_query ) {
                    $product_query = new WP_Query( apply_filters( 'dokan_product_listing_query', $args ) );
                    Cache::set( $cache_key, $product_query, $cache_group );
                }

                if ( $product_query->have_posts() ) {
                    while ( $product_query->have_posts() ) {
                        $product_query->the_post();

                        $tr_class = ( $post->post_status == 'pending' ) ? ' class="danger"' : '';
                        $product  = dokan_wc_get_product( $post->ID );

                        $edit_url = dokan_get_navigation_url( 'booking' ) . 'edit/?product_id=' . $post->ID; ?>
                        <tr<?php echo $tr_class; ?>>

                            <td data-title="<?php esc_attr_e( 'Image', 'dokan' ); ?>">
                                <a href="<?php echo $edit_url; ?>"><?php echo $product->get_image(); ?></a>
                            </td>

                            <td class="column-primary">
                                <p><a href="<?php echo $edit_url; ?>"><?php echo $product->get_title(); ?></a></p>

                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="<?php echo $edit_url; ?>"><?php _e( 'Edit', 'dokan' ); ?></a> |
                                    </span>

                                    <?php
                                    if ( current_user_can( 'dokan_delete_booking_product' ) ) {
                                        $delete_url = wp_nonce_url(
                                            add_query_arg(
                                                [
                                                    'action'     => 'dokan-delete-product',
                                                    'product_id' => $post->ID, 'tab' => 'booking',
                                                ],
                                                dokan_get_navigation_url( 'booking' )
                                            ),
                                            'dokan-delete-product'
                                        ); ?>
                                        <span class="delete">
                                            <a onclick="dokan_show_delete_prompt( event, dokan.delete_confirm );" href="<?php echo $delete_url; ?>"><?php _e( 'Delete Permanently', 'dokan' ); ?></a> |
                                        </span>
                                        <?php
                                    }

                                    $can_duplicate_product        = current_user_can( 'dokan_duplicate_product' );
                                    $vendor_can_duplicate_product = dokan_get_option( 'vendor_duplicate_product', 'dokan_selling', 'on' );

                                    if ( $can_duplicate_product && 'on' === $vendor_can_duplicate_product ) {
                                        $duplicate_url = wp_nonce_url(
                                            add_query_arg(
                                                [
                                                    'action'       => 'dokan-duplicate-product',
                                                    'product_type' => 'booking',
                                                    'product_id'   => $post->ID,
                                                    'tab'          => 'booking',
                                                ],
                                                dokan_get_navigation_url( 'booking' )
                                            ),
                                            'dokan-duplicate-product'
                                        ); ?>
                                        <span class="duplicate">
                                            <a href="<?php echo $duplicate_url; ?>"><?php esc_html_e( 'Duplicate', 'dokan' ); ?></a> |
                                        </span>
                                        <?php
                                    } ?>

                                    <span class="view"><a href="<?php echo get_permalink( $product->get_id() ); ?>" rel="permalink"><?php _e( 'View', 'dokan' ); ?></a></span>
                                </div>

                                <button type="button" class="toggle-row"></button>
                            </td>

                            <td class="post-status" data-title="<?php esc_attr_e( 'Status', 'dokan' ); ?>">
                                <label class="dokan-label <?php echo $post->post_status; ?>"><?php echo dokan_get_post_status( $post->post_status ); ?></label>
                            </td>

                            <td data-title="<?php esc_attr_e( 'SKU', 'dokan' ); ?>">
                                <?php
                                if ( $product->get_sku() ) {
                                    echo $product->get_sku();
                                } else {
                                    echo '<span class="na">&ndash;</span>';
                                } ?>
                            </td>

                            <td data-title="<?php esc_attr_e( 'Stock', 'dokan' ); ?>">
                                <?php
                                if ( $product->is_in_stock() ) {
                                    echo '<mark class="instock">' . __( 'In stock', 'dokan' ) . '</mark>';
                                } else {
                                    echo '<mark class="outofstock">' . __( 'Out of stock', 'dokan' ) . '</mark>';
                                }

                                if ( $product->managing_stock() ) {
                                    echo ' &times; ' . $product->get_total_stock();
                                } ?>
                            </td>
                            <td data-title="<?php esc_attr_e( 'Price', 'dokan' ); ?>">
                                <?php
                                if ( $product->get_price_html() ) {
                                    echo $product->get_price_html();
                                } else {
                                    echo '<span class="na">&ndash;</span>';
                                } ?>
                            </td>
                            <td data-title="<?php esc_attr_e( 'Type', 'dokan' ); ?>">
                                <?php
                                if ( dokan_get_prop( $product, 'product_type', 'get_type' ) == 'grouped' ) {
                                    echo '<span class="product-type tips grouped" title="' . __( 'Grouped', 'dokan' ) . '"></span>';
                                } elseif ( dokan_get_prop( $product, 'product_type', 'get_type' ) == 'external' ) {
                                    echo '<span class="product-type tips external" title="' . __( 'External/Affiliate', 'dokan' ) . '"></span>';
                                } elseif ( dokan_get_prop( $product, 'product_type', 'get_type' ) == 'simple' ) {
                                    if ( $product->is_virtual() ) {
                                        echo '<span class="product-type tips virtual" title="' . __( 'Virtual', 'dokan' ) . '"></span>';
                                    } elseif ( $product->is_downloadable() ) {
                                        echo '<span class="product-type tips downloadable" title="' . __( 'Downloadable', 'dokan' ) . '"></span>';
                                    } else {
                                        echo '<span class="product-type tips simple" title="' . __( 'Simple', 'dokan' ) . '"></span>';
                                    }
                                } elseif ( dokan_get_prop( $product, 'product_type', 'get_type' ) == 'variable' ) {
                                    echo '<span class="product-type tips variable" title="' . __( 'Variable', 'dokan' ) . '"></span>';
                                } else {
                                    // Assuming that we have other types in future
                                    echo '<span class="product-type tips ' . dokan_get_prop( $product, 'product_type', 'get_type' ) . '" title="' . ucfirst( dokan_get_prop( $product, 'product_type', 'get_type' ) ) . '"></span>';
                                } ?>
                            </td>

                            <td data-title="<?php esc_attr_e( 'Views', 'dokan' ); ?>">
                                <?php echo (int) get_post_meta( $post->ID, 'pageview', true ); ?>
                            </td>

                            <td class="post-date" data-title="<?php esc_attr_e( 'Date', 'dokan' ); ?>">
                                <?php
                                if ( '0000-00-00 00:00:00' == $post->post_date ) {
                                    $t_time    = $h_time    = __( 'Unpublished', 'dokan' );
                                    $time_diff = 0;
                                } else {
                                    $t_time = get_the_time( __( 'Y/m/d g:i:s A', 'dokan' ) );
                                    $m_time = $post->post_date;
                                    $time   = get_post_time( 'G', true, $post );

                                    $time_diff = time() - $time;

                                    if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
                                        $h_time = sprintf( __( '%s ago', 'dokan' ), human_time_diff( $time ) );
                                    } else {
                                        $h_time = mysql2date( get_option( 'date_format' ), $m_time );
                                    }
                                }

                                echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $post, 'date', 'all' ) . '</abbr>';
                                echo '<br />';

                                if ( 'publish' == $post->post_status ) {
                                    _e( 'Published', 'dokan' );
                                } elseif ( 'future' == $post->post_status ) {
                                    if ( $time_diff > 0 ) {
                                        echo '<strong class="attention">' . __( 'Missed schedule', 'dokan' ) . '</strong>';
                                    } else {
                                        _e( 'Scheduled', 'dokan' );
                                    }
                                } else {
                                    _e( 'Last Modified', 'dokan' );
                                } ?>
                            </td>
                        </tr>

                        <?php
                    } ?>

                        <?php
                    } else { ?>
                        <tr>
                            <td colspan="7"><?php _e( 'No product found', 'dokan' ); ?></td>
                        </tr>
                        <?php } ?>

                    </tbody>

                </table>

                <?php
                wp_reset_postdata();

                $pagenum      = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
                $base_url     = dokan_get_navigation_url( 'booking' );

                if ( $product_query->max_num_pages > 1 ) {
                    echo '<div class="pagination-wrap">';
                    $page_links = paginate_links( [
                        'current'   => $pagenum,
                        'total'     => $product_query->max_num_pages,
                        'base'      => $base_url . '%_%',
                        'format'    => '?pagenum=%#%',
                        'add_args'  => false,
                        'type'      => 'array',
                        'prev_text' => __( '&laquo; Previous', 'dokan' ),
                        'next_text' => __( 'Next &raquo;', 'dokan' ),
                    ] );

                    echo '<ul class="pagination"><li>';
                    echo join( "</li>\n\t<li>", $page_links );
                    echo "</li>\n</ul>\n";
                    echo '</div>';
                }
                ?>
            </article>
        </div>

<?php

/**
 *  dokan_dashboard_content_before hook
 *
 * @hooked get_dashboard_side_navigation
 *
 * @since 2.4
 */
do_action( 'dokan_dashboard_content_inside_after' );
do_action( 'dokan_after_listing_product' );

/*
 *  dokan_dashboard_content_after hook
 *
 * @since 2.4
 */
do_action( 'dokan_dashboard_content_after' );
// @codingStandardsIgnoreEnd
