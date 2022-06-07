<?php
/**
*  Dokan Dashboard User Subscriptions Template
*
*  Load User Subscriptions related template
*
*  @since 2.4
*
*  @package dokan
*/

global $woocommerce, $wpdb;

$subscription_id    = isset( $_GET['subscription_id'] ) ? intval( $_GET['subscription_id'] ) : 0;
$subscription       = new WC_Subscription($subscription_id);
$statuses           = wcs_get_subscription_statuses();
$hide_customer_info = dokan_get_option( 'hide_customer_info', 'dokan_selling', 'off' );
$subscription_post  = get_post( $subscription_id );
?>

<?php do_action( 'dokan_dashboard_wrap_start' ); ?>

<div class="dokan-dashboard-wrap">
    <?php
        /**
        *  dokan_dashboard_content_before hook
        *  dokan_dashboard_user_subscription_content_before hook
        *
        *  @hooked get_dashboard_side_navigation
        *
        *  @since 2.4
        */
        do_action( 'dokan_dashboard_content_before' );
        do_action( 'dokan_dashboard_orders_content_before' );
    ?>

    <div class="dokan-dashboard-content dokan-orders-content">
        <?php

            /**
            *  dokan_user_subscription_content_inside_before hook
            *
            *  @since 1.0
            */
            do_action( 'dokan_orders_content_inside_before' );
        ?>

    <article class="dokan-orders-area">

      <div class="dokan-clearfix">
        <div class="dokan-w8 dokan-order-left-content">

          <div class="dokan-clearfix">
            <div class="" style="width:100%">
              <div class="dokan-panel dokan-panel-default">
                <div class="dokan-panel-heading"><strong><?php printf( esc_html__( 'Subscription', 'dokan' ) . '#%d', esc_attr( $subscription->get_id() ) ); ?></strong> &rarr; <?php esc_html_e( 'Order Items', 'dokan' ); ?></div>
                <div class="dokan-panel-body" id="woocommerce-order-items">
                  <?php
                  if ( function_exists( 'dokan_render_order_table_items' ) ) {
                    dokan_render_order_table_items( $subscription_id );
                  } else {
                    ?>
                    <table cellpadding="0" cellspacing="0" class="dokan-table order-items">
                      <thead>
                        <tr>
                          <th class="item" colspan="2"><?php esc_html_e( 'Item', 'dokan' ); ?></th>

                          <?php do_action( 'woocommerce_admin_order_item_headers', $subscription ); ?>

                          <th class="quantity"><?php esc_html_e( 'Qty', 'dokan' ); ?></th>

                          <th class="line_cost"><?php esc_html_e( 'Totals', 'dokan' ); ?></th>
                        </tr>
                      </thead>
                      <tbody id="order_items_list">

                        <?php
                        // List order items
                        $subscription_items = $subscription->get_items( apply_filters( 'woocommerce_admin_order_item_types', array( 'line_item', 'fee' ) ) );

                        foreach ( $subscription_items as $item_id => $item ) {

                          switch ( $item['type'] ) {
                            case 'line_item' :
                            if ( version_compare( WC_VERSION, '4.4.0', '>=' ) ) {
                                $_product = $item->get_product();
                            } else {
                                $_product = $subscription->get_product_from_item( $item );
                            }

                            dokan_get_template_part( 'orders/order-item-html', '', array(
                              'order'    => $subscription,
                              'item_id'  => $item_id,
                              '_product' => $_product,
                              'item'     => $item
                            ) );
                            break;
                            case 'fee' :
                            dokan_get_template_part( 'orders/order-fee-html', '', array(
                              'item_id' => $item_id,
                            ) );

                            break;
                          }

                          do_action( 'woocommerce_order_item_' . $item['type'] . '_html', $item_id, $item );

                        }
                        ?>
                      </tbody>

                      <tfoot>
                        <?php
                        if ( $totals = $subscription->get_order_item_totals() ) {
                          foreach ( $totals as $total ) {
                            ?>
                            <tr>
                              <th colspan="2"><?php echo wp_kses_data( $total['label'] ); ?></th>
                              <td colspan="2" class="value"><?php echo wp_kses_post( $total['value']); ?></td>
                            </tr>
                            <?php
                          }
                        }
                        ?>
                      </tfoot>

                    </table>

                  <?php } ?>

                    <?php
                    $coupons = $subscription->get_items( array( 'coupon' ) );

                    if ( $coupons ) {
                      ?>
                      <table class="dokan-table order-items">
                        <tr>
                          <th><?php esc_html_e( 'Coupons', 'dokan' ); ?></th>
                          <td>
                            <ul class="list-inline"><?php
                            foreach ( $coupons as $item_id => $item ) {

                              $post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' LIMIT 1;", $item['name'] ) );

                              $link = dokan_get_coupon_edit_url( $post_id );

                              echo '<li><a data-html="true" class="tips code" title="' . esc_attr( wc_price( $item['discount_amount'] ) ) . '" href="' . esc_url( $link ) . '"><span>' . esc_html( $item['name'] ). '</span></a></li>';
                            }
                            ?></ul>
                          </td>
                        </tr>
                      </table>
                      <?php
                    }
                  ?>
                </div>
              </div>
            </div>

            <?php do_action( 'dokan_order_detail_after_order_items', $subscription ); ?>

            <div class="dokan-left dokan-order-billing-address">
              <div class="dokan-panel dokan-panel-default">
                <div class="dokan-panel-heading"><strong><?php esc_html_e( 'Billing Address', 'dokan' ); ?></strong></div>
                <div class="dokan-panel-body">
                  <?php
                  if ( $subscription->get_formatted_billing_address() ) {
                    echo wp_kses_post( $subscription->get_formatted_billing_address() );
                  } else {
                    _e( 'No billing address set.', 'dokan' );
                  }
                  ?>
                </div>
              </div>
            </div>

            <div class="dokan-left dokan-order-shipping-address">
              <div class="dokan-panel dokan-panel-default">
                <div class="dokan-panel-heading"><strong><?php esc_html_e( 'Shipping Address', 'dokan' ); ?></strong></div>
                <div class="dokan-panel-body">
                  <?php
                  if ( $subscription->get_formatted_shipping_address() ) {
                    echo wp_kses_post( $subscription->get_formatted_shipping_address() );
                  } else {
                    _e( 'No shipping address set.', 'dokan' );
                  }
                  ?>
                </div>
              </div>
            </div>

            <div class="clear"></div>

            <div class="" style="width: 100%">
              <div class="dokan-panel dokan-panel-default">
                <div class="dokan-panel-heading"><strong><?php esc_html_e( 'Downloadable Product Permission', 'dokan' ); ?></strong></div>
                <div class="dokan-panel-body">
                  <?php
                  dokan_get_template_part( 'orders/downloadable', '', array( 'order'=> $subscription ) );
                  ?>
                </div>
              </div>
            </div>

            <div class="" style="width: 100%">
              <div class="dokan-panel dokan-panel-default">
                <div class="dokan-panel-heading"><strong><?php esc_html_e( 'Related orders', 'dokan' ); ?></strong></div>
                <div class="dokan-panel-body">
                  <table class="dokan-table">
                    <thead>
                      <tr>
                        <th><?php esc_html_e( 'Order Number', 'woocommerce-subscriptions' ); ?></th>
                        <th><?php esc_html_e( 'Relationship', 'woocommerce-subscriptions' ); ?></th>
                        <th><?php esc_html_e( 'Date', 'woocommerce-subscriptions' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'woocommerce-subscriptions' ); ?></th>
                        <th><?php echo esc_html_x( 'Total', 'table heading', 'woocommerce-subscriptions' ); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php do_action( 'dokan_vps_subscriptions_related_orders_meta_box_rows', $subscription_post ); ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </div>

        </div>
        <div class="dokan-w4 dokan-order-right-content">
          <div class="dokan-clearfix">
            <div class="" style="width:100%">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php esc_html_e( 'General Details', 'dokan' ); ?></strong></div>
                    <div class="dokan-panel-body general-details">
                        <ul class="list-unstyled order-status">
                            <li>
                                <span><?php esc_html_e( 'Subscription Status:', 'dokan' ); ?></span>
                                <label class="dokan-label dokan-label-<?php echo esc_attr( dokan_vps_get_subscription_status_class( $subscription->get_status() ) ); ?>"><?php echo esc_html( dokan_vps_get_subscription_status_translated( $subscription->get_status() ) ); ?></label>

                                <?php if ( current_user_can( 'dokan_manage_order' ) && dokan_get_option( 'order_status_change', 'dokan_selling', 'on' ) == 'on' && $subscription->get_status() !== 'cancelled' && $subscription->get_status() !== 'refunded' ) {?>
                                    <a href="#" class="dokan-edit-status"><small><?php esc_html_e( '&nbsp; Edit', 'dokan' ); ?></small></a>
                                <?php } ?>
                            </li>
                            <?php if ( current_user_can( 'dokan_manage_order' ) ): ?>
                                <li class="dokan-hide">
                                    <form id="dokan-subscription-status-form" method="post">

                                        <select id="order_status" name="subscription_status" class="form-control">
                                            <?php
                                            foreach ( $statuses as $status => $status_name ) {
                                              if ( ! $subscription->can_be_updated_to( $status ) && ! $subscription->has_status( str_replace( 'wc-', '', $status ) ) ) {
                                                continue;
                                              }
                                              echo '<option value="' . esc_attr( $status ) . '" ' . selected( $status, 'wc-' . $subscription->get_status(), false ) . '>' . esc_html( $status_name ) . '</option>';
                                            }
                                            ?>
                                        </select>

                                        <input type="hidden" name="subscription_id" value="<?php echo $subscription->get_id(); ?>">
                                        <input type="hidden" name="action" value="dokan_vps_change_status">
                                        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'dokan_vps_change_status' ) ); ?>">
                                        <input type="submit" class="dokan-btn dokan-btn-success dokan-btn-sm" name="dokan_vps_change_status" value="<?php esc_attr_e( 'Update', 'dokan' ); ?>">

                                        <a href="#" class="dokan-btn dokan-btn-default dokan-btn-sm dokan-cancel-status"><?php esc_html_e( 'Cancel', 'dokan' ) ?></a>
                                    </form>
                                </li>
                            <?php endif ?>

                            <li>
                                <span><?php esc_html_e( 'Order Date:', 'dokan' ); ?></span>
                                <?php echo esc_html( dokan_get_date_created( $subscription ) ); ?>
                            </li>
                        </ul>
                        <?php if ( 'off' === $hide_customer_info && ( $subscription->get_formatted_billing_address() || $subscription->get_formatted_shipping_address() ) ) : ?>
                        <ul class="list-unstyled customer-details">
                            <li>
                                <span><?php esc_html_e( 'Customer:', 'dokan' ); ?></span>
                                <?php
                                $customer_user = absint( get_post_meta( $subscription->get_id(), '_customer_user', true ) );
                                if ( $customer_user && $customer_user != 0 ) {
                                    $customer_userdata = get_userdata( $customer_user );
                                    $display_name =  $customer_userdata->display_name;
                                } else {
                                    $display_name = get_post_meta( $subscription->get_id(), '_billing_first_name', true ). ' '. get_post_meta( $subscription->get_id(), '_billing_last_name', true );
                                }
                                ?>
                                <a href="#"><?php echo esc_html( $display_name ); ?></a><br>
                            </li>
                            <li>
                                <span><?php esc_html_e( 'Email:', 'dokan' ); ?></span>
                                <?php echo esc_html( get_post_meta( $subscription->get_id(), '_billing_email', true ) ); ?>
                            </li>
                            <li>
                                <span><?php esc_html_e( 'Phone:', 'dokan' ); ?></span>
                                <?php echo esc_html( get_post_meta( $subscription->get_id(), '_billing_phone', true ) ); ?>
                            </li>
                            <li>
                                <span><?php esc_html_e( 'Customer IP:', 'dokan' ); ?></span>
                                <?php echo esc_html( get_post_meta( $subscription->get_id(), '_customer_ip_address', true ) ); ?>
                            </li>
                        </ul>
                        <?php endif; ?>
                        <?php
                        if ( get_option( 'woocommerce_enable_order_comments' ) != 'no' ) {
                            $customer_note = get_post_field( 'post_excerpt', $subscription->get_id() );

                            if ( !empty( $customer_note ) ) {
                                ?>
                                <div class="alert alert-success customer-note">
                                    <strong><?php esc_html_e( 'Customer Note:', 'dokan' ) ?></strong><br>
                                    <?php echo wp_kses_post( $customer_note ); ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="" style="width:100%">
              <div class="dokan-panel dokan-panel-default">
                <div class="dokan-panel-heading"><strong><?php esc_html_e( 'Subscription Schedule', 'dokan' ); ?></strong></div>
                <div class="dokan-panel-body general-details">
                  <form id="dokan-subscription-schedule-form" action="" method="post">
                    <?php include_once plugin_dir_path( __FILE__ ) . 'html-subscription-schedule.php'; ?>
                    <input type="hidden" name="subscription_id" value="<?php echo $subscription->get_id(); ?>">
                    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'dokan_change_subscription_schedule' ) ); ?>">
                    <input type="submit" class="dokan-btn dokan-btn-success dokan-btn-sm" name="dokan_change_subscription_schedule" value="<?php esc_attr_e( 'Update Schedule', 'dokan' ); ?>">
                  </form>
                </div>
              </div>
            </div>

            <div class="" style="width:100%">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php esc_html_e( 'Subscription Notes', 'dokan' ); ?></strong></div>
                    <div class="dokan-panel-body" id="dokan-order-notes">
                        <?php
                        $args = array(
                            'post_id' => $subscription_id,
                            'approve' => 'approve',
                            'type'    => 'order_note'
                        );

                        remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
                        $notes = get_comments( $args );

                        echo '<ul class="order_notes list-unstyled">';

                        if ( $notes ) {
                            foreach( $notes as $note ) {
                                $note_classes = get_comment_meta( $note->comment_ID, 'is_customer_note', true ) ? array( 'customer-note', 'note' ) : array( 'note' );

                                ?>
                                <li rel="<?php echo esc_attr( absint( $note->comment_ID ) ) ; ?>" class="<?php echo esc_attr( implode( ' ', $note_classes ) ); ?>">
                                    <div class="note_content">
                                        <?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?>
                                    </div>
                                    <p class="meta">
                                        <?php printf( esc_html__( 'added %s ago', 'dokan' ), esc_textarea( human_time_diff( strtotime( $note->comment_date_gmt ), current_time( 'timestamp', 1 ) ) ) ); ?>
                                        <?php if ( current_user_can( 'dokan_manage_order_note' ) ): ?>
                                            <a href="#" class="delete_note"><?php esc_html_e( 'Delete note', 'dokan' ); ?></a>
                                        <?php endif ?>
                                    </p>
                                </li>
                                <?php
                            }
                        } else {
                            echo '<li>' . esc_html__( 'There are no notes for this order yet.', 'dokan' ) . '</li>';
                        }

                        echo '</ul>';

                        add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
                        ?>
                        <div class="add_note">
                            <?php if ( current_user_can( 'dokan_manage_order_note' ) ): ?>
                                <h4><?php esc_html_e( 'Add note', 'dokan' ); ?></h4>
                                <form class="dokan-form-inline" id="add-order-note" role="form" method="post">
                                    <p>
                                        <textarea type="text" id="add-note-content" name="note" class="form-control" cols="19" rows="3"></textarea>
                                    </p>
                                    <div class="clearfix">
                                        <div class="order_note_type dokan-form-group">
                                            <select name="note_type" id="order_note_type" class="dokan-form-control">
                                                <option value="customer"><?php esc_html_e( 'Customer note', 'dokan' ); ?></option>
                                                <option value=""><?php esc_html_e( 'Private note', 'dokan' ); ?></option>
                                            </select>
                                        </div>

                                        <input type="hidden" name="security" value="<?php echo esc_attr( wp_create_nonce( 'add-order-note' ) ); ?>">
                                        <input type="hidden" name="delete-note-security" id="delete-note-security" value="<?php echo esc_attr( wp_create_nonce('delete-order-note') ); ?>">
                                        <input type="hidden" name="post_id" value="<?php echo esc_attr( $subscription_id ); ?>">
                                        <input type="hidden" name="action" value="dokan_add_order_note">
                                        <input type="submit" name="add_order_note" class="add_note btn btn-sm btn-theme" value="<?php esc_attr_e( 'Add Note', 'dokan' ); ?>">
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div> <!-- .add_note -->
                    </div> <!-- .dokan-panel-body -->
                </div> <!-- .dokan-panel -->
            </div>


          </div>

        </div>
      </div>


    </article>

    <?php

    /**
    *  dokan_user_subscription_content_inside_after hook
    *
    *  @since 1.0
    */
    do_action( 'dokan_user_subscription_content_inside_after' );
    ?>

  </div><!-- .dokan-dashboard-content -->


  <?php

  /**
  *  dokan_dashboard_content_after hook
  *  dokan_dashboard_user_subscription_content_after hook
  *
  *  @since 1.0
  */
  do_action( 'dokan_dashboard_content_after' );
  do_action( 'dokan_dashboard_user_subscription_content_after' );
  ?>
</div><!-- .dokan-dashboard-wrap -->

<?php do_action( 'dokan_dashboard_wrap_end' ); ?>
