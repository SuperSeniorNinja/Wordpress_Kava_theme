<?php

use WeDevs\Dokan\Cache;

/**
 *
 * @param int $user optional
 * @param string $field optional
 * @return boolean|array
 */
function dokan_get_verification_status( $user = '', $field = '' ) {

    $verified_fields = array();

    //check if user is given and user id exist
    if ( empty( $user ) ) {
        $user = get_current_user_id();
    } elseif ( get_userdata( $user ) == false ) {
        return $verified_fields;
    }

    $seller_profile = dokan_get_store_info( $user );

    //Return if no verification field is set
    if ( !isset( $seller_profile['dokan_verification'] ) ) {
        return $verified_fields;
    }

    if ( $seller_profile['dokan_verification'] == '' ) {
        return $verified_fields;
    }

    foreach ( $seller_profile['dokan_verification'] as $key => $value ) {
        if ( $key == 'info' || $key == 'verified_info' ) {
            continue;
        }
        $verified_fields[$key] = array(
            'displayName' => $value['displayName'],
            'profileURL'  => $value['profileURL'],
            'photoURL'    => $value['photoURL'],
        );
    }

    //Return if no info provided by seller or Verified by admin
    if ( isset( $seller_profile['dokan_verification']['info'] ) && isset( $seller_profile['dokan_verification']['verified_info'] ) ) {
        //check if photo id verified
        if ( $seller_profile['dokan_verification']['info']['photo_id'] == $seller_profile['dokan_verification']['verified_info']['photo_id'] ) {
            $verified_fields['photo_id'] = $seller_profile['dokan_verification']['verified_info']['photo_id'];
        }
        //check if address verified
        if ( $seller_profile['dokan_verification']['info']['address'] == $seller_profile['dokan_verification']['verified_info']['address'] ) {
            $verified_fields['address'] = $seller_profile['dokan_verification']['verified_info']['address'];
        }
    }

    //if no field is set return
    if ( $field == '' || empty( $field ) ) {
        /**
         * Filter the verified fields array
         *
         * @since 1.0.0
         *
         * @param array $verified_fields
         */
        return $verified_fields = apply_filters( 'dokan_verify_get_verified_fields', $verified_fields );
    }

    if ( array_key_exists( $field, $verified_fields ) ) {

        $verified_fields = $verified_fields[$field];
    } else {
        $verified_fields = false;
    }

    /**
     * Filter the verified fields array
     *
     * @since 1.0.0
     *
     * @param array $verified_fields
     */
    return $verified_fields = apply_filters( 'dokan_verify_get_verified_fields', $verified_fields );
}


/**
 * Send email to admin on new Verification request
 *
 * @return void
 */
function dokan_verification_request_submit_email() {
    $email      = Dokan_Email::init();
    $user       = get_current_user_id();
    $store      = dokan_get_store_info($user);
    $store_name = $store['store_name'];
    $admin_url  = admin_url( 'admin.php?page=dokan-seller-verifications' );
    $subject    = __( 'New Seller Verification Request submitted', 'dokan' );

    ob_start();
    ?>
Hi,

A new verification request has been made by <?php echo $store_name;  ?>.

You can approve or reject it by going <a href="<?php echo $admin_url ?>">here</a>

---
From <?php echo dokan()->email->get_from_name(); ?>
<?php echo home_url();?>

<?php

    $message = ob_get_clean();

    dokan()->email->send(dokan()->email->admin_email(), $subject, $message);
}

/**
 * Send email to Vendor on change Verification.
 *
 * @return void
 */
function dokan_verification_request_changed_by_admin_email( $seller_profile, $postdata ) {
    $user_data = get_userdata( $postdata['seller_id'] );
    if ( empty( $user_data ) && empty( $user_data->data->user_email ) ) {
        return;
    }

    $store_name = $seller_profile['store_name'];
    $home_url   = dokan_get_navigation_url( 'settings/verification' );
    /* translators: %s is verification request status */
    $subject    = sprintf( __( 'Verification Request %s', 'dokan' ), $postdata['status'] );
    $from_email = dokan()->email->admin_email();
    ob_start();
    ?>
    Hello <?php echo $store_name; ?>,

    Your <?php echo ucwords( str_replace( '_', ' ', $postdata['type'] ) ); ?> Verification request has been changed to <?php echo ucfirst( $postdata['status'] ); ?> by an admin.

    You can check out it by going <a href="<?php echo $home_url; ?>">here</a>

    ---
    From <?php echo $from_email; ?>
    <?php echo home_url(); ?>

    <?php

    $message = ob_get_clean();

    dokan()->email->send( $user_data->data->user_email, $subject, $message );
}

/**
 * Get Verification counts, used in admin area
 *
 * @global WPDB $wpdb
 * @return array
 */
function dokan_get_verification_status_count(){
    global $wpdb;

    $cache_group = 'verifications';
    $cache_key   = 'verifications_count';
    $counts      = Cache::get( $cache_key, $cache_group );

    if ( false === $counts ) {
        $counts = array( 'pending' => 0, 'approved' => 0, 'rejected' => 0 );

        $sql = "SELECT count('user_id') as 'approved' FROM {$wpdb->usermeta} WHERE `meta_key` = 'dokan_verification_status' and `meta_value` LIKE '%approved%'";
        $approved = $wpdb->get_results( $sql );

        $sql = "SELECT count('user_id') as 'pending' FROM {$wpdb->usermeta} WHERE `meta_key` = 'dokan_verification_status' and `meta_value` LIKE '%pending%'";
        $pending = $wpdb->get_results( $sql );

        $sql = "SELECT count('user_id') as 'rejected' FROM {$wpdb->usermeta} WHERE `meta_key` = 'dokan_verification_status' and `meta_value` LIKE '%rejected%'";
        $rejected = $wpdb->get_results( $sql );

        $counts['pending']  = $pending[0]->pending;
        $counts['approved'] = $approved[0]->approved;
        $counts['rejected'] = $rejected[0]->rejected;

        Cache::set( $cache_key, $counts, $cache_group );
    }

    return $counts;
}
