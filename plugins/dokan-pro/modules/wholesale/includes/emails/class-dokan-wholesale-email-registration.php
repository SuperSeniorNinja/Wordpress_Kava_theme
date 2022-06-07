<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Dokan_Email_Wholesale_Register' ) ) :

/**
 * New Wholesael customer registration Email to admin.
 *
 * An email sent to the admin when a a customer registration as wholesale.
 *
 * @class       Dokan_Email_Wholesale_Register
 * @extends     WC_Email
 */
class Dokan_Email_Wholesale_Register extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id          = 'Dokan_Email_Wholesale_Register';
        $this->title       = __( 'Dokan Wholesale Registration', 'dokan' );
        $this->description = __( 'This email is sent to admin when customer register as a wholesale customer', 'dokan' );

        $this->heading              = __( 'New wholesale customer', 'dokan' );
        $this->heading_confirmation = __( 'Confirm wholesale registration', 'dokan' );
        $this->subject              = __( '[{blogname}] New wholesale customer register', 'dokan' );
        $this->subject_confirmation = __( '[{blogname}] A new wholesale customer is awaiting for your approval', 'dokan' );

        $this->template_base  = DOKAN_WHOLESALE_DIR . '/templates/';
        $this->template_html  = 'emails/customer-wholesale-register.php';
        $this->template_plain = 'emails/plain/customer-wholesale-register.php';


        // Triggers for this email
        add_action( 'dokan_wholesale_customer_register', array( $this, 'trigger' ), 10, 2 );

        // Call parent constructor
        parent::__construct();

        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __( 'A customer for being wholesale has request', 'dokan' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'A wholesale request is awaiting for approval', 'dokan' );
    }

    /**
     * trigger function.
     *
     * @access public
     * @return void
     */
    public function trigger( $user, $request ) {
        $this->object = $user;
        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
                'user'       => $this->object,
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
                'user'       => $this->object,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'plain_text'    => false
            ), 'dokan-wholesale/', $this->template_base );

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

            'subject' => array(
                'title'         => __( 'Subject', 'dokan' ),
                'type'          => 'text',
                'desc_tip'      => true,
                /* translators: %s: list of placeholders */
                'description'   => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{blogname}</code>' ),
                'placeholder'   => $this->get_default_subject(),
                'default'       => '',
            ),
            'heading' => array(
                'title'         => __( 'Email heading', 'dokan' ),
                'type'          => 'text',
                'desc_tip'      => true,
                /* translators: %s: list of placeholders */
                'description'   => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{product_title}</code>' ),
                'placeholder'   => $this->get_default_heading(),
                'default'       => '',
            ),
            'email_type' => array(
                'title'         => __( 'Email type', 'dokan' ),
                'type'          => 'select',
                'description'   => __( 'Choose which format of email to send.', 'dokan' ),
                'default'       => 'html',
                'class'         => 'email_type wc-enhanced-select',
                'options'       => $this->get_email_type_options(),
                'desc_tip'      => true,
            ),
        );
    }
}

endif;

return new Dokan_Email_Wholesale_Register();
