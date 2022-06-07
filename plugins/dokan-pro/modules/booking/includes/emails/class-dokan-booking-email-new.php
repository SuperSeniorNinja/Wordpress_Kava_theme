<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Dokan_Email_Booking_New' ) ) :

/**
 * New Booking Email to vendor.
 *
 * An email sent to the vendor when a new booking request for confirmation.
 *
 * @class       Dokan_Email_Booking_New
 *
 * @extends     WC_Email
 */
class Dokan_Email_Booking_New extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id          = 'Dokan_Email_Booking_New';
        $this->title       = __( 'Dokan New Booking', 'dokan' );
        $this->description = __( 'New booking emails are sent to the vendor when a new booking is created and paid. This email is also received when a Pending confirmation booking is created.', 'dokan' );

        $this->heading              = __( 'New booking', 'woocommerce-bookings' );
        $this->heading_confirmation = __( 'Confirm booking', 'woocommerce-bookings' );
        $this->subject              = __( '[{blogname}] New booking for {product_title} (Order {order_number}) - {order_date}', 'woocommerce-bookings' );
        $this->subject_confirmation = __( '[{blogname}] A new booking for {product_title} (Order {order_number}) is awaiting your approval - {order_date}', 'woocommerce-bookings' );

        $this->recipient     = "vendor@ofthe.product";

        $this->template_html  = 'emails/dokan-admin-new-booking.php';
        $this->template_plain = 'emails/plain/dokan-admin-new-booking.php';
        $this->template_base  = DOKAN_WC_BOOKING_TEMPLATE_PATH;


        // Triggers for this email
        add_action( 'woocommerce_admin_new_booking_notification', array( $this, 'trigger' ) );

        // Call parent constructor
        parent::__construct();

    }
    public function get_subject() {

        if ( wc_booking_order_requires_confirmation( $this->object->get_order() ) && $this->object->get_status() == 'pending-confirmation' ) {
            $subject = $this->get_option( 'subject_confirmation', $this->subject_confirmation );
        } else {
            $subject = $this->get_option( 'subject', $this->subject );
        }

        return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $subject ), $this->object );

    }

    /**
     * get_heading function.
     *
     * @return string
     */
    public function get_heading() {
        if ( wc_booking_order_requires_confirmation( $this->object->get_order() ) && $this->object->get_status() == 'pending-confirmation' ) {
            return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->heading_confirmation ), $this->object );
        } else {
            return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->heading ), $this->object );
        }
    }
    /**
     * trigger function.
     *
     * @access public
     * @return void
     */
    public function trigger( $booking_id ) {
        if ( ! $booking_id || ! $this->is_enabled() ) {
            return;
        }

        // Only send the booking email for booking post types, not orders, etc
        if ( 'wc_booking' !== get_post_type( $booking_id ) ) {
            return;
        }

        $this->object = get_wc_booking( $booking_id );

        if ( ! is_object( $this->object ) || ! $this->object->get_order() ) {
            return;
        }

        foreach ( array( '{product_title}', '{order_date}', '{order_number}' ) as $key ) {
            $key = array_search( $key, $this->find );
            if ( false !== $key ) {
                unset( $this->find[ $key ] );
                unset( $this->replace[ $key ] );
            }
        }

        if ( $this->object->get_product() ) {
            $this->find[]    = '{product_title}';
            $this->replace[] = $this->object->get_product()->get_title();
        }

        $vendor_id    = dokan_get_seller_id_by_order( $this->object->get_order_id() );
        $vendor       = dokan()->vendor->get( $vendor_id );
        $vendor_email = $vendor->get_email();

        $this->recipient = $vendor_email;
        if ( $this->object->get_order() ) {
            if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
                $billing_email = $this->object->get_order()->billing_email;
                $order_date    = $this->object->get_order()->order_date;
            } else {
                $billing_email = $this->object->get_order()->get_billing_email();
                $order_date    = $this->object->get_order()->get_date_created() ? $this->object->get_order()->get_date_created()->date( 'Y-m-d H:i:s' ) : '';
            }

            $this->find[]    = '{order_date}';
            $this->replace[] = date_i18n( wc_date_format(), strtotime( $order_date ) );

            $this->find[]    = '{order_number}';
            $this->replace[] = $this->object->get_order()->get_order_number();

            $this->recipient = get_bloginfo( 'admin_email' ) . ',' . $vendor_email . ',' . $billing_email;
        } else {
            $this->find[]    = '{order_date}';
            $this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->booking_date ) );

            $this->find[]    = '{order_number}';
            $this->replace[] = __( 'N/A', 'woocommerce-bookings' );

            if ( $this->object->customer_id && ( $customer = get_user_by( 'id', $this->object->customer_id ) ) ) {
                $this->recipient = get_bloginfo( 'admin_email' ) . ',' . $vendor_email . ',' . $customer->user_email;
            }
        }

        if ( ! $this->get_recipient() )
            return;

        $this->send( $vendor_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }


    /**
     * Get content html.
     *
     * @access public
     * @return string
     */
    public function get_content_html() {
        ob_start();
            wc_get_template( $this->template_html, array(
                'booking'       => $this->object,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'plain_text'    => false
            ), 'dokan/', $this->template_base );

        return ob_get_clean();
    }

    /**
     * Get content plain.
     *
     * @access public
     * @return string
     */
    public function get_content_plain() {
        ob_start();
            wc_get_template( $this->template_html, array(
                'booking'       => $this->object,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'plain_text'    => false
            ), 'dokan/', $this->template_base );

        return ob_get_clean();
    }

    /**
     * Initialise settings form fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'         => __( 'Enable/Disable', 'dokan' ),
                'type'          => 'checkbox',
                'label'         => __( 'Enable this email notification', 'dokan' ),
                'default'       => 'yes',
            ),
            'recipient' => array(
                'title'         => __( 'Recipient(s)', 'dokan' ),
                'type'          => 'text',
                /* translators: %s: admin email */
                'description'   => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>vendor@ofthe.product</code>.', 'dokan' ) ),
                'placeholder'   => '',
                'default'       => '',
            ),
            'subject' => array(
                'title'         => __( 'Subject', 'dokan' ),
                'type'          => 'text',
                /* translators: %s: subject */
                'description'   => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'dokan' ), $this->subject ),
                'placeholder'   => '',
                'default'       => '',
            ),
            'subject_confirmation' => array(
                'title'         => __( 'Subject (Pending confirmation)', 'dokan' ),
                'type'          => 'text',
                /* translators: %s: subject confirmation */
                'description'   => sprintf( __( 'This controls the email subject line for Pending confirmation bookings. Leave blank to use the default subject: <code>%s</code>.', 'dokan' ), $this->subject_confirmation ),
                'placeholder'   => '',
                'default'       => '',
            ),
            'heading' => array(
                'title'         => __( 'Email Heading', 'dokan' ),
                'type'          => 'text',
                /* translators: %s: heading */
                'description'   => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'dokan' ), $this->heading ),
                'placeholder'   => '',
                'default'       => '',
            ),
            'heading_confirmation' => array(
                'title'         => __( 'Email Heading (Pending confirmation)', 'dokan' ),
                'type'          => 'text',
                /* translators: %s: heading confirmation */
                'description'   => sprintf( __( 'This controls the main heading contained within the email notification for Pending confirmation bookings. Leave blank to use the default heading: <code>%s</code>.', 'dokan' ), $this->heading_confirmation ),
                'placeholder'   => '',
                'default'       => '',
            ),
            'email_type' => array(
                'title'         => __( 'Email type', 'dokan' ),
                'type'          => 'select',
                'description'   => __( 'Choose which format of email to send.', 'dokan' ),
                'default'       => 'html',
                'class'         => 'email_type',
                'options'       => array(
                    'plain'         => __( 'Plain text', 'dokan' ),
                    'html'          => __( 'HTML', 'dokan' ),
                    'multipart'     => __( 'Multipart', 'dokan' ),
                ),
            ),
        );
    }
}

endif;

return new Dokan_Email_Booking_New();
