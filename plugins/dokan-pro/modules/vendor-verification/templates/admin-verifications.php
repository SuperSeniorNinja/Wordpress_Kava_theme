<?php

use WeDevs\Dokan\Cache;

$counts = dokan_get_verification_status_count();

$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'pending';

$country_obj = new WC_Countries();
$countries   = $country_obj->countries;
$states      = $country_obj->states;
?>
<div class="wrap">
    <h2><?php esc_html_e( 'Verification Requests', 'dokan' ); ?></h2>

    <ul class="subsubsub" style="float: none;">
        <li>
            <a href="admin.php?page=dokan-seller-verifications&amp;status=pending" <?php if ( $status === 'pending' ) echo 'class="current"'; ?>>
                <?php esc_html_e( 'Pending', 'dokan' ); ?> <span class="count">(<?php echo $counts['pending']; ?>)</span>
            </a> |
        </li>
        <li>
            <a href="admin.php?page=dokan-seller-verifications&amp;status=approved" <?php if ( $status === 'approved' ) echo 'class="current"'; ?>>
                <?php esc_html_e( 'Approved', 'dokan' ); ?> <span class="count">(<?php echo $counts['approved']; ?>)</span>
            </a> |
        </li>
        <li>
            <a href="admin.php?page=dokan-seller-verifications&amp;status=rejected" <?php if ( $status === 'rejected' ) echo 'class="current"'; ?>>
                <?php esc_html_e( 'Rejected', 'dokan' ); ?> <span class="count">(<?php echo $counts['rejected']; ?>)</span>
            </a>
        </li>
    </ul>
    <?php
    if ( isset( $_GET['message'] ) ) {
        $message = '';

        switch ( $_GET['message'] ) {
            case 'trashed':
                $message = __( 'Requests deleted!', 'dokan' );
                break;

            case 'cancelled':
                $message = __( 'Requests cancelled!', 'dokan' );
                break;

            case 'approved':
                $message = __( 'Requests approved!', 'dokan' );
                break;
        }

        if ( ! empty( $message ) ) {
            ?>
            <div class="updated">
                <p><strong><?php echo $message; ?></strong></p>
            </div>
            <?php
        }
    }
    ?>
    <?php

    $args = array(
        //'role'    => 'seller',
        'meta_key'     => 'dokan_verification_status',
        'meta_compare' => 'LIKE',
        'meta_value'   => $status,
    );

    $cache_group = 'verifications';
    $cache_key   = 'verification_data_' . md5( wp_json_encode( $args ) );
    $query       = Cache::get( $cache_key, $cache_group );

    if ( false === $query ) {
        $query = new WP_User_Query( $args );

        Cache::set( $cache_key, $query, $cache_group );
    }

    $sellers = $query->get_results();
    $result  = null;

    foreach ( $sellers as $seller ) {
        $seller_profile = dokan_get_store_info( $seller->ID );

        if ( isset( $seller_profile['dokan_verification']['info'] ) ) {
            $seller_v = array(
                'store_name' => $seller_profile['store_name'],
                'seller_id'  => $seller->ID,
            );

            if ( isset( $seller_profile['dokan_verification']['info']['dokan_v_id_status'] ) && $seller_profile['dokan_verification']['info']['dokan_v_id_status'] === $status ) {
                $seller_v['id_info'] = array(
                    'photo_id' => $seller_profile['dokan_verification']['info']['photo_id'],
                    'id_type'  => $seller_profile['dokan_verification']['info']['dokan_v_id_type'],
                );
            } else {
                $seller_v['alt_status'] = isset( $seller_profile['dokan_verification']['info']['dokan_v_id_status'] ) ? $seller_profile['dokan_verification']['info']['dokan_v_id_status'] : '';
            }

            if ( isset( $seller_profile['dokan_verification']['info']['store_address'] ) && $seller_profile['dokan_verification']['info']['store_address']['v_status'] === $status ) {
                $seller_v['store_address'] = $seller_profile['dokan_verification']['info']['store_address'];
            } else {
                $seller_v['address_status'] = isset( $seller_profile['dokan_verification']['info']['store_address']['v_status'] ) ? $seller_profile['dokan_verification']['info']['store_address']['v_status'] : '';
            }

            if ( isset( $seller_profile['dokan_verification']['info']['phone_status'] ) && $seller_profile['dokan_verification']['info']['phone_status'] === $status ) {
                $seller_v['phone']        = $seller_profile['dokan_verification']['info']['phone_no'];
                $seller_v['phone_status'] = $seller_profile['dokan_verification']['info']['phone_status'];
            } else {
                $seller_v['phone']        = isset( $seller_profile['dokan_verification']['info']['phone_no'] ) ? $seller_profile['dokan_verification']['info']['phone_no']: '';
                $seller_v['phone_status'] = isset( $seller_profile['dokan_verification']['info']['phone_status'] ) ? $seller_profile['dokan_verification']['info']['phone_status'] : '';
            }

            if ( isset( $seller_profile['dokan_verification']['info']['company_v_status'] ) && $seller_profile['dokan_verification']['info']['company_v_status'] === $status ) {
                $seller_v['company_verification_files'] = isset( $seller_profile['company_verification_files'] ) ? $seller_profile['company_verification_files'] : '';
                $seller_v['company_v_status'] = isset( $seller_profile['dokan_verification']['info']['company_v_status'] ) ? $seller_profile['dokan_verification']['info']['company_v_status'] : '';
            } else {
                $seller_v['company_v_status'] = isset( $seller_profile['dokan_verification']['info']['company_v_status'] ) ? $seller_profile['dokan_verification']['info']['company_v_status'] : '';
            }
        }
        if ( isset( $seller_v ) ) {
            $result[] = $seller_v;
        }
    }
    ?>
        <table class="widefat verification-table">
            <thead>
                <tr>
                    <th class="check-column">
<!--                        <input type="checkbox" class="dokan-withdraw-allcheck">-->
                    </th>
                    <th width="20%"><?php esc_html_e( 'Store Name', 'dokan' ); ?></th>
                    <th width="20%"><?php esc_html_e( 'Photo ID', 'dokan' ); ?></th>
                    <th width="20%"><?php esc_html_e( 'Address', 'dokan' ); ?></th>
                    <th width="20%"><?php esc_html_e( 'Phone Number', 'dokan' ); ?></th>
                    <th width="20%"><?php esc_html_e( 'Company', 'dokan' ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="check-column">
<!--                        <input type="checkbox" class="dokan-withdraw-allcheck">-->
                    </th>
                    <th><?php esc_html_e( 'Store Name', 'dokan' ); ?></th>
                    <th><?php esc_html_e( 'Photo ID ', 'dokan' ); ?></th>
                    <th><?php esc_html_e( 'Address ', 'dokan' ); ?></th>
                    <th><?php esc_html_e( 'Phone Number ', 'dokan' ); ?></th>
                    <th><?php esc_html_e( 'Company ', 'dokan' ); ?></th>
                </tr>
            </tfoot>

            <?php
            if ( $result ) {
                $count = 0;
                foreach ( $result as $key => $val ) {
                    $user_data = get_userdata( $val['seller_id'] );

                    //$store_info = dokan_get_store_info($val['seller_id'] );
                    ?>
                    <tr class="<?php echo ( $count % 2 ) === 0 ? 'alternate' : 'odd'; ?>">

                        <th class="check-column">
<!--                            <input type="checkbox" name="id[<?php echo $val['seller_id']; ?>]" value="<?php echo $val['seller_id']; ?>">-->

                        </th>
                        <td>
                            <strong><a href="<?php echo admin_url( 'user-edit.php?user_id=' . $user_data->ID ); ?>"><?php echo $user_data->user_login; ?></a></strong>

                        </td>
                        <td>
                            <?php if ( isset( $val['id_info'] ) ) { ?>
                            <form method="post" action="" class="dokan-admin-sv-action">
                                <?php wp_nonce_field( 'dokan_sv_nonce_action', 'dokan_sv_nonce' ); ?>
                                <div class="id_type_wrapper">

                                    <span class="id_type">
                                            <?php
                                            //echo _e( 'Type : ', 'dokan' );
                                            echo ucwords( preg_replace( '/_/', ' ', $val['id_info']['id_type'] ) );
                                            ?>
                                    </span>
                                    <input type="hidden" class="dokan-file-field" value="<?php echo $val['id_info']['id_type']; ?>" name="dokan_v_id_type">


                                </div>
                                <?php $gravatar_url = $val['id_info']['photo_id'] ? wp_get_attachment_url( $val['id_info']['photo_id'] ) : ''; ?>
                                <input type="hidden" class="dokan-file-field" value="<?php echo $val['id_info']['photo_id']; ?>" name="dokan_gravatar">
                                <a href="<?php echo esc_url( $gravatar_url ); ?>" target="_blank">
                                <img class="dokan_v_id dokan-gravatar-img" src="<?php echo esc_url( $gravatar_url ); ?>"><br/>
                                </a>
                                <p></p>



                                <div class="row-actions">
                                    <?php if ( $status === 'pending' ) { ?>

                                        <span class="edit"><a href="#" class="dokan-sv-action" data-type="id" data-status="approved" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Approve', 'dokan' ); ?></a> | </span>
                                        <span class="edit"><a href="#" class="dokan-sv-action" data-type="id" data-status="rejected" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Reject', 'dokan' ); ?></a></span>

                                    <?php } elseif ( $status === 'approved' ) { ?>

                                        <span class="trash"><a href="#" class="dokan-sv-action" data-type="id" data-status="disapproved" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Disapprove', 'dokan' ); ?></a> | </span>
    <!--                                    <span class="edit"><a href="#" class="dokan-sv-action" data-type="id" data-status="pending" data-seller_id = "<?php //echo $val['seller_id']; ?>"><?php //_e( 'Pending', 'dokan' ); ?></a></span>-->

                                    <?php } elseif ( $status === 'rejected' ) { ?>

                                        <span class="edit"><a href="#" class="dokan-sv-action" data-type="id" data-status="approved" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Approve', 'dokan' ); ?></a> | </span>
                                        <span class="edit"><a href="#" class="dokan-sv-action" data-type="id" data-status="pending" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Pending', 'dokan' ); ?></a></span>

                                    <?php } ?>
                                </div>
                            </form>
                                    <?php } elseif ( isset( $val['alt_status'] ) && $val['alt_status'] !== '' ) { ?>

                                        <span title="<?php echo $val['alt_status']; ?>" class="dashicons dashicons-<?php echo $val['alt_status']; ?>"></span>
                                        <p class="status-text"><?php echo $val['alt_status']; ?></p>

                                    <?php } else { ?>

                                        <span title="<?php esc_html_e( 'Not Available', 'dokan' ); ?>" class="text-info"><b><?php esc_html_e( 'Not Available', 'dokan' ); ?></b></span>

                                    <?php } ?>
                        </td>
                        <td>
                            <?php if ( isset( $val['store_address'] ) ) { ?>
                            <form method="post" action="" class="dokan-admin-sv-action">
                                <?php wp_nonce_field( 'dokan_sv_nonce_action', 'dokan_sv_nonce' ); ?>
                                <div class="d_v_address">
                                    <p><?php echo $val['store_address']['street_1']; ?></p>
                                    <p><?php echo $val['store_address']['street_2']; ?></p>
                                    <p>
                                        <?php echo $val['store_address']['city'] . ', '; ?>
                                        <?php echo $val['store_address']['zip']; ?>
                                    </p>
                                    <?php
                                    $country_code = $val['store_address']['country'];
                                    $state_code   = isset( $val['store_address']['state'] ) ? $val['store_address']['state'] : '';

                                    //current country and state

                                    $c_country = isset( $countries[ $country_code ] ) ? $countries[ $country_code ] : '';
                                    $c_state   = isset( $states[ $country_code ][ $state_code ] ) ? $states[ $country_code ][ $state_code ] : $state_code;
                                    $proof_url = ! empty( $val['store_address']['proof'] ) ? $val['store_address']['proof'] : '';
                                    ?>
                                    <p>
                                        <?php echo ! empty( $c_state ) ? $c_state . ', ' : $c_state; ?>
                                        <?php echo $c_country; ?>
                                    </p>

                                    <input type="hidden" class="dokan-file-field" value="<?php echo $val['store_address']['street_1']; ?>" name="street_1">
                                    <input type="hidden" class="dokan-file-field" value="<?php echo $val['store_address']['street_2']; ?>" name="street_2">
                                    <input type="hidden" class="dokan-file-field" value="<?php echo $val['store_address']['city']; ?>" name="store_city">
                                    <input type="hidden" class="dokan-file-field" value="<?php echo $val['store_address']['zip']; ?>" name="store_zip">
                                    <input type="hidden" class="dokan-file-field" value="<?php echo $state_code; ?>" name="store_state">
                                    <input type="hidden" class="dokan-file-field" value="<?php echo $country_code; ?>" name="store_country">

                                    <p class="proof-of-residence" style="text-decoration: underline;">
                                        <a target="_blank" href="<?php echo esc_url( $proof_url ); ?>">
                                            <?php esc_html_e( 'Proof of residence', 'dokan' ); ?>
                                        </a>
                                        <input
                                            type="hidden"
                                            id="proof_of_residence"
                                            name="proof_of_residence"
                                            value="<?php echo esc_attr( $proof_url ); ?>"
                                        />
                                    </p>
                                </div>
                                <div class="row-actions">
                                    <?php if ( $status === 'pending' ) { ?>

                                        <span class="edit"><a href="#" class="dokan-sv-action" data-type="address" data-status="approved" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Approve', 'dokan' ); ?></a> | </span>
                                        <span class="trash"><a href="#" class="dokan-sv-action" data-type="address" data-status="rejected" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Reject', 'dokan' ); ?></a></span>

                                    <?php } elseif ( $status === 'approved' ) { ?>

                                        <span class="trash"><a href="#" class="dokan-sv-action" data-type="address" data-status="disapproved" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Disapprove', 'dokan' ); ?></a> | </span>

                                    <?php } elseif ( $status === 'rejected' ) { ?>

                                        <span class="edit"><a href="#" class="dokan-sv-action" data-type="address" data-status="approved" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Approve', 'dokan' ); ?></a> | </span>
                                        <span class="edit"><a href="#" class="dokan-sv-action" data-type="address" data-status="pending" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Pending', 'dokan' ); ?></a></span>

                                    <?php } ?>
                                </div>
                            </form>
                            <?php } elseif ( isset( $val['address_status'] ) && $val['address_status'] !== '' ) { ?>

                            <span title="<?php echo $val['address_status']; ?>" class="dashicons dashicons-<?php echo $val['address_status']; ?>"></span>
                            <p class="status-text"><?php echo $val['address_status']; ?></p>

                            <?php } else { ?>

                            <span title="<?php esc_html_e( 'Not Available', 'dokan' ); ?>" class="text-info"><b><?php echo _e( 'Not Available', 'dokan' ); ?></b></span>


                            <?php } ?>
                        </td>
                        <td>
                            <?php if ( $val['phone_status'] === 'verified' ) : ?>
                                <span title="pending" class="dashicons dashicons-approved"></span>
                                <p class="status-text"><?php echo $val['phone']; ?></p>
                            <?php elseif ( $val['phone_status'] === 'pending' ) : ?>
                                <span title="pending" class="dashicons dashicons-pending"></span>
                                <p class="status-text"><?php echo $val['phone']; ?></p>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( isset( $val['company_verification_files'] ) && is_array( $val['company_verification_files'] ) ) { ?>
                            <form method="post" action="" class="dokan-admin-sv-action">
                                <?php wp_nonce_field( 'dokan_sv_nonce_action', 'dokan_sv_nonce' ); ?>
                                <div class="d_v_address">
                                    <?php foreach ( $val['company_verification_files'] as $key => $single_file_id ) { ?>
                                        <P><a href="<?php echo wp_get_attachment_url( $single_file_id ) ?>" target="_blank" rel="noopener noreferrer"><?php echo get_the_title( $single_file_id ) ?></a></P>
                                    <?php } ?>

                                    <?php foreach ( $val['company_verification_files'] as $key => $single_file_id ) { ?>
                                        <input type="hidden" class="dokan-file-field" value="<?php echo $single_file_id; ?>" name="company_verification_files_ids[]">
                                    <?php } ?>

                                </div>
                                <div class="row-actions">
                                    <?php if ( $status === 'pending' ) { ?>

                                        <span class="edit"><a href="#" class="dokan-sv-action" data-type="company_verification_files" data-status="approved" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Approve', 'dokan' ); ?></a> | </span>
                                        <span class="trash"><a href="#" class="dokan-sv-action" data-type="company_verification_files" data-status="rejected" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Reject', 'dokan' ); ?></a></span>

                                    <?php } elseif ( $status === 'approved' ) { ?>

                                        <span class="trash"><a href="#" class="dokan-sv-action" data-type="company_verification_files" data-status="disapproved" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Disapprove', 'dokan' ); ?></a> | </span>

                                    <?php } elseif ( $status === 'rejected' ) { ?>

                                        <span class="edit"><a href="#" class="dokan-sv-action" data-type="company_verification_files" data-status="approved" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Approve', 'dokan' ); ?></a> | </span>
                                        <span class="edit"><a href="#" class="dokan-sv-action" data-type="company_verification_files" data-status="pending" data-seller_id = "<?php echo $val['seller_id']; ?>"><?php esc_html_e( 'Pending', 'dokan' ); ?></a></span>

                                    <?php } ?>
                                </div>
                            </form>
                            <?php } elseif ( isset( $val['company_v_status'] ) && $val['company_v_status'] !== '' ) { ?>

                            <span title="<?php echo $val['company_v_status']; ?>" class="dashicons dashicons-<?php echo $val['company_v_status']; ?>"></span>
                            <p class="status-text"><?php echo $val['company_v_status']; ?></p>

                            <?php } else { ?>

                            <span title="Not Available" class="text-info"><b><?php echo _e( 'Not Available', 'dokan' ); ?></b></span>


                            <?php } ?>
                        </td>


                    </tr>
                    <?php
                    $count++;
                }
            } else {
                ?>
                <tr>
                    <td colspan="8">
                        <?php esc_html_e( 'No result found', 'dokan' ); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>

</div>
<style type="text/css">
            .verification-table {
                margin-top: 10px;
            }

            .verification-table td, .verification-table th {
                //vertical-align: top;
            }

            img.dokan_v_id.dokan-gravatar-img{
                max-width: 100px;
                max-height: 100px;
            }

            .id_type{
                font-weight: bold;
            }
            .id_type_wrapper{
                margin-bottom: 10px;
            }
            .dashicons-approved:before {
                content: "\f147";
                font-size: 50px;
                color : #0f0;

            }
            .dashicons-pending:before {
                content: "\f469";
                font-size: 50px;
                color : #fddb5a;
            }
            .dashicons-rejected:before {
                content: "\f335";
                font-size: 50px;
                color : #a00;
            }
            span.dashicons{
                padding-top: 20px;
            }
            .status-text{
                margin-top: -6px !important;
                margin-left: 50px !important;
                font-weight: bold;
            }

</style>
<script>
            (function($){
                $(document).ready(function(){
                    var url = "<?php echo admin_url( 'admin-ajax.php' ); ?>";

                    $('.verification-table').on('click', 'a.dokan-sv-action', function(e) {
                        e.preventDefault();
                        var self = $(this);

                        data = {
                            action: 'dokan_sv_form_action',
                            formData : self.closest( 'form.dokan-admin-sv-action' ).serialize(),
                            status: self.data('status') ,
                            type : self.data('type'),
                            seller_id : self.data( 'seller_id' )
                        };

                        $.post(url, data, function( resp ) {

                            if( resp.success ) {
                                self.closest( 'tr' ).removeClass('custom-spinner');
                                location.reload();
                            } else {
                                self.closest( 'tr' ).removeClass('custom-spinner');
                                dokan_sweetalert( dokan.i18n_invalid, {
                                    icon: 'error',
                                } );
                            }
                        });

                    });
                });
            })(jQuery)
        </script>

