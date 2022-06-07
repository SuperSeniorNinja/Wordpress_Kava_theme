<?php
/**
 * Class Dokan_New_Support_Ticket file
 *
 * @package Dokan/Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Dokan_New_Support_Ticket' ) ) :

    /**
     * New Support Ticket.
     *
     * An email sent to the seller when a new support ticket submit.
     *
     * @class       Dokan_New_Support_Ticket
     * @version     3.3.4
     * @package     Dokan/Classes/Emails
     * @extends     WC_Email
     */
    class Dokan_New_Support_Ticket extends WC_Email {

        /**
         * Constructor.
         */
        public function __construct() {
            $this->id             = 'dokan_new_support_ticket';
            $this->title          = __( 'Dokan New Support Ticket', 'dokan' );
            $this->description    = __( 'New support ticket emails are sent to chosen recipient(s) when a new ticket is received.', 'dokan' );
            $this->template_html  = 'emails/new-support-ticket.php';
            $this->template_plain = 'emails/plain/new-support-ticket.php';
            $this->template_base  = DOKAN_STORE_SUPPORT_DIR . '/templates/';

            $this->placeholders = array(
                '{site_title}'   => $this->get_blogname(),
            );

            // Triggers for this email.
            add_action( 'dokan_new_ticket_created_notify', array( $this, 'trigger' ), 10, 2 );

            // Call parent constructor.
            parent::__construct();

            // Other settings.
            $this->recipient = 'vendor@email.com';
        }

        /**
         * Get email subject.
         *
         * @since  3.3.4
         * @return string
         */
        public function get_default_subject() {
            return __( '[{site_title}] A New Support Ticket', 'dokan' );
        }

        /**
         * Get email heading.
         *
         * @since  3.3.4
         * @return string
         */
        public function get_default_heading() {
            return __( 'A New Support Ticket', 'dokan' );
        }

        /**
         * Trigger the sending of this email.
         *
         * @param int            $order_id The order ID.
         * @param WC_Order|false $order Order object.
         */
        public function trigger( $store_id, $topic_id ) {
            if ( ! $this->is_enabled() ) {
                return;
            }

            $email            = get_userdata( $store_id )->user_email;
            $this->store_info = dokan_get_store_info( $store_id );
            $this->topic_id   = $topic_id;

            $this->setup_locale();
            $this->send( $email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

            $this->restore_locale();
        }

        /**
         * Get content html.
         *
         * @access public
         * @return string
         */
        public function get_content_html() {
            return wc_get_template_html(
                $this->template_html, array(
                    'email_heading' => $this->get_heading(),
                    'plain_text'    => false,
                    'email'         => $this,
                    'store_info'    => $this->store_info,
                    'topic_id'      => $this->topic_id,
                ), 'dokan/', $this->template_base
            );
        }

        /**
         * Get content plain.
         *
         * @access public
         * @return string
         */
        public function get_content_plain() {
            return wc_get_template_html(
                $this->template_plain, array(
                    'email_heading' => $this->get_heading(),
                    'plain_text'    => true,
                    'email'         => $this,
                    'store_info'    => $this->store_info,
                    'topic_id'      => $this->topic_id,
                ), 'dokan/', $this->template_base
            );
        }

        /**
         * Initialise settings form fields.
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled'    => array(
                    'title'   => __( 'Enable/Disable', 'dokan' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable this email notification', 'dokan' ),
                    'default' => 'yes',
                ),
                'subject'    => array(
                    'title'       => __( 'Subject', 'dokan' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    /* translators: %s: list of placeholders */
                    'description' => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{site_title}</code>' ),
                    'placeholder' => $this->get_default_subject(),
                    'default'     => '',
                ),
                'heading'    => array(
                    'title'       => __( 'Email heading', 'dokan' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    /* translators: %s: list of placeholders */
                    'description' => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{site_title}</code>' ),
                    'placeholder' => $this->get_default_heading(),
                    'default'     => '',
                ),
                'email_type' => array(
                    'title'       => __( 'Email type', 'dokan' ),
                    'type'        => 'select',
                    'description' => __( 'Choose which format of email to send.', 'dokan' ),
                    'default'     => 'html',
                    'class'       => 'email_type wc-enhanced-select',
                    'options'     => $this->get_email_type_options(),
                    'desc_tip'    => true,
                ),
            );
        }
    }

endif;
