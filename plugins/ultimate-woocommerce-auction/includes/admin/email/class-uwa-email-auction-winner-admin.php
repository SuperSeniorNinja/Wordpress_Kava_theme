<?php
if (!defined('ABSPATH')) {
	exit;
}
/**
 *
 *
 * @class UWA_Email_Auction_Winner_Admin
 * @author Nitesh Singh 
 * @since 1.0  
 *
 */

if ( ! class_exists( 'UWA_Email_Auction_Winner_Admin' ) ) {
    /**
     * Class UWA_Email_Auction_Winner_Admin
     *
     * @author Nitesh Singh 
     */
    class UWA_Email_Auction_Winner_Admin extends WC_Email {

        /**
         * Construct
         *
         * @author Nitesh Singh 
         * @since 1.0
         */
        public function __construct() {
           
            $this->id = 'woo_ua_email_auction_winner_admin';            
            $this->title = __('​Ultimate Auction - Auction Sold', 'ultimate-woocommerce-auction');			
            $this->description = __('​Can be sent to admin when the auction ends and has a winner', 'ultimate-woocommerce-auction');
            $this->heading = __('Auction Product Won by User on {site_title}', 'ultimate-woocommerce-auction');
            $this->subject = __('Auction Product Won by User on {site_title}', 'ultimate-woocommerce-auction');          
			$this->template_html = 'emails/auction-winner-admin.php';
            $this->template_plain = 'emails/plain/auction-winner-admin.php';
            // Trigger on bidder won the auction
            add_action( 'uwa_won_email_admin', array( $this, 'trigger' ), 10, 2 );

            // Call parent constructor to load any other defaults not explicity defined here
            parent::__construct();
				// Other settings
			$this->recipient = $this->get_option( 'recipient' );

			if ( ! $this->recipient )
				$this->recipient = get_option( 'admin_email' );
			}
       

        public function trigger( $product_id, $winneruser ) {
           
            if ( !$this->is_enabled() ) {
                return;
            }
			$user = get_user_by('id',$winneruser);
            $url_product = get_edit_post_link($product_id);
            
            $this->object = array(
                'user_id'    => $user->ID,
                'user_name'     => $user->user_login,                 
				'product_id'    => $product_id,				
                'url_product'   => $url_product,
            );

            $this->send( $this->get_recipient(),
                $this->get_subject(),
                $this->get_content(),
                $this->get_headers(),
                $this->get_attachments() );
        }


        public function get_content_html() {
            return wc_get_template_html( $this->template_html, array(
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text'    => false,
                'email'         => $this
            ),
                '',
                WOO_UA_WC_TEMPLATE );
        }


        public function get_content_plain() {
            return wc_get_template_html( $this->template_plain, array(
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text'    => true,
                'email'         => $this
            ),
                '',
                WOO_UA_WC_TEMPLATE );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled'    => array(
                    'title'   => __( 'Enable/Disable', 'woocommerce' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable this email notification', 'woocommerce' ),
                    'default' => 'yes'
                ),
				
				'recipient' => array(

				'title' 		=> __( 'Recipient(s)', 'woocommerce' ),

				'type' 			=> 'text',

				'description' 	=> sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'woocommerce' ), esc_attr( get_option('admin_email') ) ),

				'placeholder' 	=> '',

				'default' 		=> ''

			),
				

                'subject'    => array(
                    'title'       => __( 'Subject', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'ultimate-woocommerce-auction' ), $this->subject ),
                    'placeholder' => '',
                    'default'     => '',
                    
                ),
                'heading'    => array(
                    'title'       => __( 'Email Heading', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'ultimate-woocommerce-auction' ), $this->heading ),
                    'placeholder' => '',
                    'default'     => '',
                    
                ),
                'email_type' => array(
                    'title'       => __( 'Email type', 'woocommerce' ),
                    'type'        => 'select',
                    'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
                    'default'     => 'html',
                    'class'       => 'email_type wc-enhanced-select',
                    'options'     => $this->get_email_type_options(),
                    
                )
            );
        }


    }

}
return new UWA_Email_Auction_Winner_Admin();
