<?php

namespace WeDevs\DokanPro;

use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use WeDevs\DokanPro\Storage\Session;

class SocialLogin {

    private $callback;
    private $config;

    /**
     * Load automatically when class instantiated
     *
     * @since 2.4
     *
     * @uses actions|filter hooks
     */
    public function __construct() {
        $this->callback = dokan_get_page_url( 'myaccount', 'woocommerce' );
        $this->init_hooks();
    }

    /**
     * Call actions and hooks
     */
    public function init_hooks() {
        //add settings menu page
        add_filter( 'dokan_settings_sections', array( $this, 'dokan_social_api_settings' ) );
        add_filter( 'dokan_settings_fields', array( $this, 'dokan_social_settings_fields' ) );

        if ( 'on' !== dokan_get_option( 'enabled', 'dokan_social_api' ) ) {
            return;
        }

        //Hybrid auth action
        add_action( 'template_redirect', array( $this, 'monitor_autheticate_requests' ), 1 );

        // add social buttons on registration form and login form
        add_action( 'woocommerce_register_form_end', array( $this, 'render_social_logins' ) );
        add_action( 'woocommerce_login_form_end', array( $this, 'render_social_logins' ) );
        add_action( 'dokan_vendor_reg_form_end', array( $this, 'render_social_logins' ) );
        add_action( 'dokan_vendor_reg_form_end', array( $this, 'enqueue_style' ) );

        //add custom my account end-point
        add_filter( 'dokan_query_var_filter', array( $this, 'register_support_queryvar' ) );
        add_action( 'dokan_load_custom_template', array( $this, 'load_template_from_plugin' ) );

        // load providers config
        $this->config = $this->get_providers_config();
    }

    /**
     * Get configuration values for HybridAuth
     *
     * @return array
     */
    private function get_providers_config() {
        $config = [
            'callback' => $this->callback,
            'providers' => [
                'Google' => [
                    'enabled' => false,
                    'keys'    => [
                        'id'     => dokan_get_option( 'google_app_id', 'dokan_social_api' ),
                        'secret' => dokan_get_option( 'google_app_secret', 'dokan_social_api' ),
                    ],
                ],
                'Facebook' => [
                    'enabled'        => false,
                    'trustForwarded' => false,
                    'scope'          => 'email, public_profile',
                    'keys'           => [
                        'id'     => dokan_get_option( 'fb_app_id', 'dokan_social_api' ),
                        'secret' => dokan_get_option( 'fb_app_secret', 'dokan_social_api' ),
                    ],
                ],
                'Twitter' => [
                    'enabled'      => false,
                    'includeEmail' => true,
                    'keys'         => [
                        'key'    => dokan_get_option( 'twitter_app_id', 'dokan_social_api' ),
                        'secret' => dokan_get_option( 'twitter_app_secret', 'dokan_social_api' ),
                    ],
                ],
                'LinkedIn' => [
                    'enabled' => false,
                    'keys'    => [
                        'id' => dokan_get_option( 'linkedin_app_id', 'dokan_social_api' ),
                        'secret' => dokan_get_option( 'linkedin_app_secret', 'dokan_social_api' ),
                    ],
                ],
                'Apple' => [
                    'enabled' => false,
                    'scope'   => 'name email',
                    'keys'    => [
                        'id'          => dokan_get_option( 'apple_service_id', 'dokan_social_api' ),
                        'team_id'     => dokan_get_option( 'apple_team_id', 'dokan_social_api' ),
                        'key_id'      => dokan_get_option( 'apple_key_id', 'dokan_social_api' ),
                        'key_content' => dokan_get_option( 'apple_key_content', 'dokan_social_api' ),
                    ],
                    'verifyTokenSignature' => false,
                    'authorize_url_parameters' => [
                        'response_mode' => 'form_post',
                    ],
                ],
            ],
        ];

        //facebook config from admin
        if ( $config['providers']['Facebook']['keys']['id'] !== '' && $config['providers']['Facebook']['keys']['secret'] !== '' ) {
            $config['providers']['Facebook']['enabled'] = true;
        }

        //google config from admin
        if ( $config['providers']['Google']['keys']['id'] !== '' && $config['providers']['Google']['keys']['secret'] !== '' ) {
            $config['providers']['Google']['enabled'] = true;
        }

        //linkedin config from admin
        if ( $config['providers']['LinkedIn']['keys']['id'] !== '' && $config['providers']['LinkedIn']['keys']['secret'] !== '' ) {
            $config['providers']['LinkedIn']['enabled'] = true;
        }

        //Twitter config from admin
        if ( $config['providers']['Twitter']['keys']['key'] !== '' && $config['providers']['Twitter']['keys']['secret'] !== '' ) {
            $config['providers']['Twitter']['enabled'] = true;
        }

        // apple config from the admin
        if ( $config['providers']['Apple']['keys']['id'] !== '' &&
            $config['providers']['Apple']['keys']['team_id'] !== '' &&
            $config['providers']['Apple']['keys']['key_id'] !== '' &&
            $config['providers']['Apple']['keys']['key_content'] !== ''
        ) {
            $config['providers']['Apple']['enabled'] = true;
        }

        /**
         * Filter the Config array of Hybridauth
         *
         * @since 1.0.0
         *
         * @param array $config
         */
        $config = apply_filters( 'dokan_social_providers_config', $config );

        return $config;
    }

    /**
     * Monitors Url for Hauth Request and process Hauth for authentication
     *
     * @return void
     */
    public function monitor_autheticate_requests() {

        // if not my account page, return early
        if ( ! is_account_page() ) {
            return;
        }

        try {
            /**
             * Feed the config array to Hybridauth
             *
             * @var Hybridauth
             */
            $hybridauth = new Hybridauth( $this->config );

            /**
             * Initialize session storage.
             *
             * @var Session
             */
            $storage = new Session( 'social_login', 5 * 60 );

            /**
             * Hold information about provider when user clicks on Sign In.
             */
            $provider = ! empty( $_GET['vendor_social_reg'] ) ? sanitize_text_field( wp_unslash( $_GET['vendor_social_reg'] ) ) : ''; //phpcs:ignore

            if ( $provider ) {
                $storage->set( 'provider', $provider );
            }

            if ( $provider = $storage->get( 'provider' ) ) { //phpcs:ignore
                $adapter = $hybridauth->getAdapter( $provider );
                $adapter->setStorage( $storage );
                $adapter->authenticate();
            }

            if ( ! isset( $adapter ) ) {
                return;
            }

            $user_profile = $adapter->getUserProfile();
            $storage->clear();

            if ( ! $user_profile ) {
                wc_add_notice( __( 'Something went wrong! please try again', 'dokan' ), 'error' );
                wp_safe_redirect( $this->callback );
            }

            if ( empty( $user_profile->email ) ) {
                wc_add_notice( __( 'User email is not found. Try again.', 'dokan' ), 'error' );
                wp_safe_redirect( $this->callback );
            }

            $wp_user = get_user_by( 'email', $user_profile->email );

            if ( ! $wp_user ) {
                try {
                    $this->register_new_user( $user_profile );
                } catch ( \Exception $exception ) {
                    wc_add_notice( $exception->getMessage(), 'error' );
                    wp_safe_redirect( $this->callback );
                }
            } else {
                $this->login_user( $wp_user );
            }
        } catch ( Exception $e ) {
            wc_add_notice( $e->getMessage(), 'error' );
        }
    }

    /**
     * Filter admin menu settings section
     *
     * @param array $sections
     *
     * @return array
     */
    public function dokan_social_api_settings( $sections ) {
        $sections[] = array(
            'id'    => 'dokan_social_api',
            'title' => __( 'Social API', 'dokan' ),
            'icon'  => 'dashicons-networking',
        );
        return $sections;
    }

    /**
     * Render settings fields for admin settings section
     *
     * @param array $settings_fields
     *
     * @return array
     */
    public function dokan_social_settings_fields( $settings_fields ) {
        $settings_fields['dokan_social_api'] = array(
            'sectio_title' => array(
                'name'  => 'sectio_title',
                'label' => __( 'Social API', 'dokan' ),
                'type'  => 'sub_section',
            ),
            'enabled' => array(
                'name'    => 'enabled',
                'label'   => __( 'Enable Social Login', 'dokan' ),
                'type'    => 'checkbox',
                'desc'    => __( 'Enabling this will add Social Icons under registration form to allow users to login or register using Social Profiles.', 'dokan' ),
                'tooltip' => __( 'Check this to allow social login/signup for customers and vendors.', 'dokan' ),
            ),
            'facebook_details' => array(
                'name'  => 'facebook_details',
                'label' => __( 'Facebook', 'dokan' ),
                'type'  => 'sub_section',
            ),
            'facebook_app_label'  => array(
                'name'  => 'fb_app_label',
                'label' => __( 'Facebook App Settings', 'dokan' ),
                'type'  => 'html',
                'desc'  => '<a target="_blank" href="https://developers.facebook.com/apps/">' . __( 'Create an App', 'dokan' ) . '</a> if you don\'t have one and fill App ID and Secret below. <a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-facebook/" target="_blank">Get Help</a>',
            ),
            'facebook_app_url'    => array(
                'name'    => 'fb_app_url',
                'label'   => __( 'Site URL', 'dokan' ),
                'type'    => 'html',
                'desc'    => "<input class='regular-text' type='text' disabled value='{$this->callback}'>",
                'tooltip' => __( 'Your store URL, which will be required in creating the App.', 'dokan' ),
            ),
            'facebook_app_id'     => array(
                'name'    => 'fb_app_id',
                'label'   => __( 'App ID', 'dokan' ),
                'type'    => 'text',
                'tooltip' => __( 'You can get it from Facebook Developer platform -> Login -> Select  "Add A New App" -> Collect App ID.', 'dokan' ),
            ),
            'facebook_app_secret' => array(
                'name'    => 'fb_app_secret',
                'label'   => __( 'App Secret', 'dokan' ),
                'type'    => 'text',
                'tooltip' => __( 'You can get it from Facebook Developer platform -> Login -> Select  "Add A New App" -> Collect App secret.', 'dokan' ),
            ),
            'twitter_details' => array(
                'name'  => 'twitter_details',
                'label' => __( 'Twitter', 'dokan' ),
                'type'  => 'sub_section',
            ),
            'twitter_app_label'   => array(
                'name'  => 'twitter_app_label',
                'label' => __( 'Twitter App Settings', 'dokan' ),
                'type'  => 'html',
                'desc'  => '<a target="_blank" href="https://apps.twitter.com/">' . __( 'Create an App', 'dokan' ) . '</a> if you don\'t have one and fill Consumer key and Secret below. <a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-twitter/" target="_blank">Get Help</a>',
            ),
            'twitter_app_url'     => array(
                'name'    => 'twitter_app_url',
                'label'   => __( 'Callback URL', 'dokan' ),
                'type'    => 'html',
                'desc'    => "<input class='regular-text' type='text' disabled value='{$this->callback}'>",
                'tooltip' => __( 'Your store URL, which will be required in creating the App.', 'dokan' ),
            ),
            'twitter_app_id'      => array(
                'name'    => 'twitter_app_id',
                'label'   => __( 'Consumer Key', 'dokan' ),
                'type'    => 'text',
                'tooltip' => __( 'You can get it from Twitter Developer platform -> Login -> Select  "Create an App" -> Input URL & integrate Twitter with Dokan. Go to "Keys and Tokens" -> View Keys -> Collect API key and use as Consumer Key.', 'dokan' ),
            ),
            'twitter_app_secret'  => array(
                'name'    => 'twitter_app_secret',
                'label'   => __( 'Consumer Secret', 'dokan' ),
                'type'    => 'text',
                'tooltip' => __( 'You can get it from Twitter Developer platform -> Login -> Select  "Create an App" -> Input URL & integrate Twitter with Dokan. Go to "Keys and Tokens" -> View Keys -> Collect API secret and use as Consumer secret.', 'dokan' ),
            ),
            'google_details' => array(
                'name'  => 'google_details',
                'label' => __( 'Google', 'dokan' ),
                'type'  => 'sub_section',
            ),
            'google_app_label'    => array(
                'name'  => 'google_app_label',
                'label' => __( 'Google App Settings', 'dokan' ),
                'type'  => 'html',
                'desc'  => '<a target="_blank" href="https://console.developers.google.com/project">' . __( 'Create an App', 'dokan' ) . '</a> if you don\'t have one and fill Client ID and Secret below. <a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-google/" target="_blank">Get Help</a>',
            ),
            'google_app_url'      => array(
                'name'    => 'google_app_url',
                'label'   => __( 'Redirect URL', 'dokan' ),
                'type'    => 'html',
                'desc'    => "<input class='regular-text' type='text' disabled value='{$this->callback}'>",
                'tooltip' => __( 'Your store URL, which will be required in syncing with Google API.', 'dokan' ),
            ),
            'google_app_id'       => array(
                'name'    => 'google_app_id',
                'label'   => __( 'Client ID', 'dokan' ),
                'type'    => 'text',
                'tooltip' => __( 'You can get it from Google Console Platform -> Google+API -> Enable -> Manage -> Credentials -> Create Credentials -> OAuth client ID -> Web Application -> Fill in the information & click Create. A pop up will show "Client ID".', 'dokan' ),
            ),
            'google_app_secret'   => array(
                'name'    => 'google_app_secret',
                'label'   => __( 'Client secret', 'dokan' ),
                'type'    => 'text',
                'tooltip' => __( 'You can get it from Google Console Platform -> Google+API -> Enable -> Manage -> Credentials -> Create Credentials -> OAuth client ID -> Web Application -> Fill in the information & click Create. A pop up will show "Client Credentials".', 'dokan' ),
            ),
            'linkedin_details' => array(
                'name'  => 'linkedin_details',
                'label' => __( 'Linkedin', 'dokan' ),
                'type'  => 'sub_section',
            ),
            'linkedin_app_label'  => array(
                'name'  => 'linkedin_app_label',
                'label' => __( 'Linkedin App Settings', 'dokan' ),
                'type'  => 'html',
                'desc'  => '<a target="_blank" href="https://www.linkedin.com/developer/apps">' . __( 'Create an App', 'dokan' ) . '</a> if you don\'t have one and fill Client ID and Secret below. <a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-linkedin/" target="_blank">Get Help</a>',
            ),
            'linkedin_app_url'    => array(
                'name'    => 'linkedin_app_url',
                'label'   => __( 'Redirect URL', 'dokan' ),
                'type'    => 'html',
                'desc'    => "<input class='regular-text' type='text' disabled value='{$this->callback}'>",
                'tooltip' => __( 'Your store URL, which will be required in creating the App.', 'dokan' ),
            ),
            'linkedin_app_id'     => array(
                'name'    => 'linkedin_app_id',
                'label'   => __( 'Client ID', 'dokan' ),
                'type'    => 'text',
                'tooltip' => __( 'You can get it from LinkedIn Developers platform -> Create an App -> Fill necessary info -> Click "Create app" -> "Auth" section -> Collect Client ID.', 'dokan' ),
            ),
            'linkedin_app_secret' => array(
                'name'    => 'linkedin_app_secret',
                'label'   => __( 'Client Secret', 'dokan' ),
                'type'    => 'text',
                'tooltip' => __( 'You can get it from LinkedIn Developers platform -> Create an App -> Fill necessary info -> Click "Create app" -> "Auth" section -> Collect Client Secret.', 'dokan' ),
            ),
            'apple_details' => array(
                'name'  => 'apple_details',
                'label' => __( 'Apple', 'dokan' ),
                'type'  => 'sub_section',
            ),
            'apple_app_label'  => array(
                'name'  => 'apple_app_label',
                'label' => __( 'Apple App Settings', 'dokan' ),
                'type'  => 'html',
                'desc'  => '<a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-apple/" target="_blank">' . __( 'Get Help', 'dokan' ) . '</a>',
            ),
            'apple_redirect_url'    => array(
                'name'    => 'apple_redirect_url',
                'label'   => __( 'Redirect URL', 'dokan' ),
                'type'    => 'html',
                'desc'    => "<input class='regular-text' type='text' disabled value='{$this->callback}'>",
                'tooltip' => __( 'Your store URL, which will be required in creating the app.', 'dokan' ),
            ),
            'apple_service_id'    => array(
                'name'    => 'apple_service_id',
                'label'   => __( 'Apple Service ID', 'dokan' ),
                'type'    => 'text',
                'tooltip' => __( 'You can get it from Apple Developer platform -> login -> Certificates, IDs & Profiles -> Indentifiers -> Service IDs (drop down) -> Register for Service ID -> Collect Service ID.', 'dokan' ),
            ),
            'apple_team_id'     => array(
                'name'    => 'apple_team_id',
                'label'   => __( 'Apple Team ID', 'dokan' ),
                'type'    => 'text',
                'tooltip' => __( 'You can get it from Apple Developer platform -> login -> Membership ->  Collect Team ID.', 'dokan' ),
            ),
            'apple_key_id' => array(
                'name'    => 'apple_key_id',
                'label'   => __( 'Apple Key ID', 'dokan' ),
                'type'    => 'text',
                'tooltip' => __( 'You can get it from Apple Developer platform -> login -> Certificates, IDs & Profiles -> Keys -> Click " + " -> Register for new Key -> Download "Apple Key Content" -> Collect Key ID.', 'dokan' ),
            ),
            'apple_key_content' => array(
                'name'    => 'apple_key_content',
                'label'   => __( 'Apple Key Content (including BEGIN and END lines)', 'dokan' ),
                'type'    => 'textarea',
                'tooltip' => __( 'You can get it from Apple Developer platform -> login -> Certificates, IDs & Profiles -> Keys -> Click " + " -> Register for new Key -> Download "Apple Key Content" -> Collect Key Content.', 'dokan' ),
            ),
        );

        return $settings_fields;
    }

    /**
     * Register dokan query vars
     *
     * @since 1.0
     *
     * @param array $vars
     *
     * @return array new $vars
     */
    public function register_support_queryvar( $vars ) {
        $vars[] = 'social-register';
        $vars[] = 'dokan-registration';

        return $vars;
    }

    /**
     * Register page templates
     *
     * @since 1.0
     *
     * @param array $query_vars
     *
     * @return array $query_vars
     */
    public function load_template_from_plugin( $query_vars ) {
        if ( isset( $query_vars['dokan-registration'] ) ) {
            $template = DOKAN_PRO_DIR . '/templates/global/social-register.php';
            include $template;
        }
    }

    /**
     * Render social login icons
     *
     * @return void
     */
    public function render_social_logins() {
        $configured_providers = [];

        if ( ! isset( $this->config['providers'] ) ) {
            return $configured_providers;
        }

        foreach ( $this->config['providers'] as $provider_name => $provider_settings ) {
            if ( true === $provider_settings['enabled'] ) {
                $configured_providers[] = strtolower( $provider_name );
            }
        }

        /**
         * Filter the list of Providers connect links to display
         *
         * @since 1.0.0
         *
         * @param array $providers
         */
        $providers = apply_filters( 'dokan_social_provider_list', $configured_providers );

        $data = array(
            'base_url'  => $this->callback,
            'providers' => $providers,
            'pro'       => true,
        );

        dokan_get_template_part( 'global/social-registration', '', $data );
    }

    /**
     * Register a new user
     *
     * @param object $data
     *
     * @param string $provider
     *
     * @return void
     * @throws Exception
     */
    private function register_new_user( $data ) {
        // @codingStandardsIgnoreStart
        $userdata = array(
            'user_login' => dokan_generate_username( ! empty( $data->displayName ) ? $data->displayName : 'user' ),
            'user_email' => $data->email,
            'user_pass'  => wp_generate_password(),
            'first_name' => ! empty( $data->firstName ) ? $data->firstName : 'name1',
            'last_name'  => ! empty( $data->lastName ) ? $data->lastName : 'name2',
            'role'       => 'customer',
        );
        // @codingStandardsIgnoreEnd

        $user_id = wp_insert_user( $userdata );

        if ( is_wp_error( $user_id ) ) {
            throw new Exception( $user_id->get_error_message() );
        }

        $this->login_user( get_userdata( $user_id ) );
    }

    /**
     * Log in existing users
     *
     * @param WP_User $wp_user
     *
     * return void
     */
    private function login_user( $wp_user ) {
        clean_user_cache( $wp_user->ID );
        wp_clear_auth_cookie();
        wp_set_current_user( $wp_user->ID );

        if ( is_ssl() === true ) {
            wp_set_auth_cookie( $wp_user->ID, true, true );
        } else {
            wp_set_auth_cookie( $wp_user->ID, true, false );
        }

        update_user_caches( $wp_user );
        wp_safe_redirect( dokan_get_page_url( 'myaccount', 'woocommerce' ) );
        exit;
    }

    /**
     * Enqueue social style on vendor registration page created via [dokan-vendor-registration] shortcode
     *
     * @since 2.9.13
     *
     * @return void
     */
    public function enqueue_style() {
        wp_enqueue_style( 'dokan-social-style' );
        wp_enqueue_style( 'dokan-social-theme-flat' );
    }
}
