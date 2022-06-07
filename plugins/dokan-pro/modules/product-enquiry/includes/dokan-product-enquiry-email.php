<?php

use WeDevs\Dokan\Vendor\Vendor;

/**
 * Dokan product enquiry email
 */
class Dokan_Product_Enquiry_Email extends WC_Email {
    /**
     * IP place holder
     *
     * @var null
     */
    public $i_p = null;

    /**
     * Vendor place holder
     *
     * @var null
     */
    public $vendor = null;

    /**
     * Product placeholder
     *
     * @var null
     */
    public $product = null;

    /**
     * User agent placeholder
     *
     * @var null
     */
    public $user_agent = null;

    /**
     * Customer name placeholder
     *
     * @var null
     */
    public $customer_name = null;

    /**
     * Customer email placeholder
     *
     * @var null
     */
    public $customer_email = null;

    /**
     * Constructor Method
     */
    public function __construct() {
        $this->id             = 'dokan_product_enquiry_email';
        $this->title          = __( 'Dokan Product Enquiry', 'dokan' );
        $this->description    = __( 'Send email to vendor on product enquiry.', 'dokan' );
        $this->template_html  = 'product-enquiry-email-html.php';
        $this->template_base  = DOKAN_ENQUIRY_VIEWS . '/';
        $this->placeholders   = array(
            '{site_title}'    => $this->get_blogname(),
            '{product_title}' => '',
        );

        // Call parent constructor
        parent::__construct();

        // Set the email content type text/html
        $this->email_type = 'html';
        $this->recipient  = 'vendor@ofthe.product';

        add_action( 'dokan_send_enquiry_email', array( $this, 'trigger' ), 15, 7 );
    }

    /**
     * Email settings
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_form_fields() {
        $available_placeholders = '{site_title}, {product_title}';

        $this->form_fields = array(
            'enabled' => array(
                'title'         => __( 'Enable/Disable', 'dokan' ),
                'type'          => 'checkbox',
                'label'         => __( 'Enable this email', 'dokan' ),
                'default'       => 'yes',
            ),

            'subject' => array(
                'title'         => __( 'Subject', 'dokan' ),
                'type'          => 'text',
                'desc_tip'      => true,
                // translators: %s: Available placeholders
                'description'   => sprintf( __( 'Available placeholders: %s', 'dokan' ), $available_placeholders ),
                'placeholder'   => $this->get_default_subject(),
                'default'       => $this->get_default_subject(),
            ),

            'heading' => array(
                'title'         => __( 'Email heading', 'dokan' ),
                'type'          => 'text',
                'desc_tip'      => true,
                // translators: %s: Available placeholders
                'description'   => sprintf( __( 'Available placeholders: %s', 'dokan' ), $available_placeholders ),
                'placeholder'   => $this->get_default_heading(),
                'default'       => $this->get_default_heading(),
            ),
        );
    }

    /**
     * Email default subject
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_default_subject() {
        // translators: %s: Site title
        return sprintf( __( 'You have got a new product enquiry email from %s', 'dokan' ), '{site_title}' );
    }

    /**
     * Email default heading
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_default_heading() {
        // translators: %s: Product title
        return sprintf( __( 'A new product enquiry posted for your product %s', 'dokan' ), '{product_title}' );
    }

    /**
     * Send email
     *
     * @since 1.0.0
     *
     * @param \WeDevs\Dokan\Vendor\Vendor $vendor
     * @param WC_Product                  $product
     * @param int                         $ip
     * @param string                      $user_agent
     * @param string                      $customer_name
     * @param string                      $customer_email
     * @param string                      $message
     *
     * @return void
     */
    public function trigger( $vendor, $product, $ip, $user_agent, $customer_name, $customer_email, $message ) {
        $this->setup_locale();

        if ( ! $this->is_enabled() && ! $this->get_email_recipient() ) {
            return;
        }

        $this->vendor         = $vendor;
        $this->product        = $product;
        $this->i_p             = $ip;
        $this->user_agent     = $user_agent;
        $this->customer_name  = $customer_name;
        $this->customer_email = $customer_email;
        $this->message        = $message;

        $this->placeholders['{product_title}'] = $product->get_title();

        $get_headers  = $this->get_headers();
        $get_headers .= 'Reply-to: ' . $customer_name . ' <' . $customer_email . ">\r\n";

        $this->send( $this->get_email_recipient(), $this->get_subject(), $this->get_content(), $get_headers, $this->get_attachments() );

        $this->restore_locale();
    }

    /**
     * Get the name for outgoing emails.
     *
     * @sience 3.3.9
     *
     * @return string
     */
    public function get_from_name( $from_name = '' ) {
        return $this->customer_name;
    }

    /**
     * Get the from address for outgoing emails.
     *
     * @since 3.3.9
     *
     * @return string|null
     */
    public function get_from_address( $from_email = '' ) {
        return $this->customer_email;
    }

    /**
     * Follower email
     *
     * @since 1.0.0
     *
     * @return string|null
     */
    public function get_email_recipient() {
        if ( $this->vendor instanceof Vendor && is_email( $this->vendor->get_email() ) ) {
            return $this->vendor->get_email();
        }

        return null;
    }

    /**
     * Email content
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_content() {
        ob_start();
        wc_get_template(
            $this->template_html, array(
				'email_heading'  => $this->get_heading(),
				'sent_to_admin'  => false,
				'plain_text'     => false,
				'email'          => $this,
				'vendor'         => $this->vendor,
				'product'        => $this->product,
				'message'        => $this->message,
				'IP'             => $this->i_p,
				'user_agent'     => $this->user_agent,
				'customer_name'  => $this->customer_name,
				'customer_email' => $this->customer_email,
            ), 'dokan/', $this->template_base
        );
        return ob_get_clean();
    }
}
