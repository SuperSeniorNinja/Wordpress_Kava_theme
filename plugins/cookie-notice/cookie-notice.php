<?php
/*
Plugin Name: Cookie Notice & Compliance for GDPR / CCPA
Description: Cookie Notice allows you to you elegantly inform users that your site uses cookies and helps you comply with GDPR, CCPA and other data privacy laws.
Version: 2.2.3
Author: Hu-manity.co
Author URI: https://hu-manity.co/
Plugin URI: https://hu-manity.co/
License: MIT License
License URI: https://opensource.org/licenses/MIT
Text Domain: cookie-notice
Domain Path: /languages

Cookie Notice
Copyright (C) 2022, Hu-manity.co - info@hu-manity.co

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Cookie Notice class.
 *
 * @class Cookie_Notice
 * @version	2.2.3
 */
class Cookie_Notice {

	private $status = '';

	/**
	 * @var $defaults
	 */
	public $defaults = array(
		'general' => array(
			'app_id'				=> '',
			'app_key'				=> '',
			'app_blocking'			=> true,
			'hide_banner'			=> false,
			'position'				=> 'bottom',
			'message_text'			=> '',
			'css_class'				=> '',
			'accept_text'			=> '',
			'refuse_text'			=> '',
			'refuse_opt'			=> false,
			'refuse_code'			=> '',
			'refuse_code_head'		=> '',
			'revoke_cookies'		=> false,
			'revoke_cookies_opt'	=> 'automatic',
			'revoke_message_text'	=> '',
			'revoke_text'			=> '',
			'redirection'			=> false,
			'see_more'				=> false,
			'link_target'			=> '_blank',
			'link_position'			=> 'banner',
			'time'					=> 'month',
			'time_rejected'			=> 'month',
			'hide_effect'			=> 'fade',
			'on_scroll'				=> false,
			'on_scroll_offset'		=> 100,
			'on_click'				=> false,
			'colors' => array(
				'text'			=> '#fff',
				'button'		=> '#00a99d',
				'bar'			=> '#32323a',
				'bar_opacity'	=> 100
			),
			'see_more_opt' => array(
				'text'		=> '',
				'link_type'	=> 'page',
				'id'		=> 0,
				'link'		=> '',
				'sync'		=> false
			),
			'script_placement'			=> 'header',
			'translate'					=> true,
			'deactivation_delete'		=> false,
			'update_version'			=> 5,
			'update_notice'				=> true,
			'update_delay_date'			=> 0
		),
		'version'	=> '2.2.3'
	);
	private $deactivaion_url = '';
	
	private static $_instance;

	/**
	 * Disable object cloning.
	 */
	public function __clone() {}

	/**
	 * Disable unserializing of the class.
	 */
	public function __wakeup() {}

	/**
	 * Main plugin instance.
	 * 
	 * @return object
	 */
	public static function instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self();

			add_action( 'plugins_loaded', array( self::$_instance, 'load_textdomain' ) );

			self::$_instance->includes();

			self::$_instance->bot_detect = new Cookie_Notice_Bot_Detect();
			self::$_instance->dashboard = new Cookie_Notice_Dashboard();
			self::$_instance->frontend = new Cookie_Notice_Frontend();
			self::$_instance->settings = new Cookie_Notice_Settings();
			self::$_instance->welcome = new Cookie_Notice_Welcome();
			self::$_instance->welcome_api = new Cookie_Notice_Welcome_API();
			self::$_instance->welcome_frontend = new Cookie_Notice_Welcome_Frontend();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		
		// get options
		$options = get_option( 'cookie_notice_options', $this->defaults['general'] );

		// check legacy parameters
		$options = $this->check_legacy_params( $options, array( 'refuse_opt', 'on_scroll', 'on_click', 'deactivation_delete', 'see_more' ) );

		// merge old options with new ones
		$this->options = array(
			'general' => $this->multi_array_merge( $this->defaults['general'], $options )
		);

		if ( ! isset( $this->options['general']['see_more_opt']['sync'] ) )
			$this->options['general']['see_more_opt']['sync'] = $this->defaults['general']['see_more_opt']['sync'];
		
		// actions
		add_action( 'plugins_loaded', array( $this, 'set_status' ) );
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'init', array( $this, 'wpsc_add_cookie' ) );
		add_action( 'admin_init', array( $this, 'update_notice' ) );
		add_action( 'wp_ajax_cn_dismiss_notice', array( $this, 'ajax_dismiss_admin_notice' ) );
		add_action( 'admin_footer', array( $this, 'deactivate_plugin_template' ) );
		add_action( 'wp_ajax_cn-deactivate-plugin', array( $this, 'deactivate_plugin' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// filters
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
	}
	
	/**
	 * Set plugin status.
	 */
	public function set_status() {
		$status = get_option( 'cookie_notice_status', '' );
		
		$this->status = ! empty( $status ) && in_array( $status, array( 'active', 'pending' ), true ) ? $status : false;
	}

	/**
	 * Include required files.
	 *
	 * @return void
	 */
	private function includes() {
		include_once( plugin_dir_path( __FILE__ ) . 'includes/bot-detect.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'includes/dashboard.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'includes/frontend.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'includes/settings.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'includes/welcome.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'includes/welcome-api.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'includes/welcome-frontend.php' );
	}
	
	/**
	 * Load textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'cookie-notice', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	
	/**
	 * Activate the plugin.
	 */
	public function activation() {
		add_option( 'cookie_notice_options', $this->defaults['general'], '', 'no' );
	}

	/**
	 * Deactivate the plugin.
	 */
	public function deactivation() {
		if ( $this->options['general']['deactivation_delete'] === true ) {
			delete_option( 'cookie_notice_options' );
			delete_option( 'cookie_notice_version' );
			delete_option( 'cookie_notice_status' );
			
			delete_transient( 'cookie_notice_compliance_cache' );
		}
		
		// remove WP Super Cache cookie
		$this->wpsc_delete_cookie();
	}

	/**
	 * Update notice.
	 * 
	 * @return void
	 */
	public function update_notice() {
		if ( ! current_user_can( 'install_plugins' ) )
			return;
		
		// bail an ajax
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		
		$current_update = 6;
		
		// get current database version
		$current_db_version = get_option( 'cookie_notice_version', '1.0.0' );
		
		if ( version_compare( $current_db_version, $this->defaults['version'], '<' ) && $this->options['general']['update_version'] < $current_update ) {
			// check version, if update version is lower than plugin version, set update notice to true
			$this->options['general'] = wp_parse_args( array( 'update_version' => $current_update, 'update_notice' => true ), $this->options['general'] );

			update_option( 'cookie_notice_options', $this->options['general'] );
			
			// update plugin version
			update_option( 'cookie_notice_version', $this->defaults['version'], false );
		}
		
		// if visiting settings, mark notice as read
		if ( ! empty( $_GET['page'] ) && $_GET['page'] === 'cookie-notice' && ! empty( $_GET['welcome'] ) ) {
			$this->options['general'] = wp_parse_args( array( 'update_notice' => false ), $this->options['general'] );
			update_option( 'cookie_notice_options', $this->options['general'] );
		}
		
		// show notice, if no compliance only
		if ( $this->options['general']['update_notice'] === true && empty( $this->status ) ) {
			// set_transient( 'cn_show_welcome', 1 );
			$this->add_notice( '<div class="cn-notice-text"><h2>' . __( 'Compliance fines exceeded &euro;1.3 BILLION in 2021. Avoid the risk by making sure your website complies with the latest cookie consent laws.', 'cookie-notice' ) . '</h2><p>' . __( 'Run compliance check to learn if your website complies with the latest consent record storage and cookie blocking requirements.', 'cookie-notice' ) . '</p><p class="cn-notice-actions"><a href="' . admin_url( 'admin.php' ) . '?page=cookie-notice&welcome=1' . '" class="button button-primary cn-button">' . __( 'Run Compliance Check', 'cookie-notice' ) . '</a> <a href="#" class="button-link cn-notice-dismiss">' . __( 'Dismiss Notice', 'cookie-notice' ) . '</a></p></div>', 'error', 'div' );
		}
	}

	/**
	 * Add admin notices.
	 * 
	 * @param string $html
	 * @param string $status
	 * @param bool $paragraph
	 */
	private function add_notice( $html = '', $status = 'error', $container = '' ) {
		$this->notices[] = array(
			'html' 		=> $html,
			'status' 	=> $status,
			'container' => ( ! empty( $container ) && in_array( $container, array( 'p', 'div' ) ) ? $container : '' )
		);

		add_action( 'admin_notices', array( $this, 'display_notice'), 0 );
	}

	/**
	 * Print admin notices.
	 * 
	 * @return mixed
	 */
	public function display_notice() {
		foreach( $this->notices as $notice ) {
			echo '
			<div id="cn-admin-notice" class="cn-notice notice notice-info ' . $notice['status'] . '">
				' . ( ! empty( $notice['container'] ) ? '<' . $notice['container'] . ' class="cn-notice-container">' : '' ) . '
				' . $notice['html'] . '
				' . ( ! empty( $notice['container'] ) ? '</' . $notice['container'] . ' class="cn-notice-container">' : '' ) . '
			</div>';
		}
	}

	/**
	 * Dismiss admin notice.
	 */
	public function ajax_dismiss_admin_notice() {
		if ( ! current_user_can( 'install_plugins' ) )
			return;

		if ( wp_verify_nonce( $_REQUEST['nonce'], 'cn_dismiss_notice' ) ) {
			$notice_action = empty( $_REQUEST['notice_action'] ) || $_REQUEST['notice_action'] === 'dismiss' ? 'dismiss' : sanitize_text_string( $_REQUEST['notice_action'] );

			switch ( $notice_action ) {
				// delay notice
				case 'delay':
					// set delay period to 1 week from now
					$this->options['general'] = wp_parse_args( array( 'update_delay_date' => time() + 1209600 ), $this->options['general'] );
					update_option( 'cookie_notice_options', $this->options['general'] );
					break;
				
				// delay notice
				case 'approve':
					// hide notice
					$this->options['general'] = wp_parse_args( array( 'update_notice' => false ), $this->options['general'] );
					$this->options['general'] = wp_parse_args( array( 'update_delay_date' => 0 ), $this->options['general'] );
					// update options
					update_option( 'cookie_notice_options', $this->options['general'] );
					break;

				// hide notice
				default:
					$this->options['general'] = wp_parse_args( array( 'update_notice' => false ), $this->options['general'] );
					$this->options['general'] = wp_parse_args( array( 'update_delay_date' => 0 ), $this->options['general'] );

					update_option( 'cookie_notice_options', $this->options['general'] );
			}
		}

		exit;
	}

	/**
	 * Register shortcode.
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'cookies_accepted', array( $this, 'cookies_accepted_shortcode' ) );
		add_shortcode( 'cookies_revoke', array( $this, 'cookies_revoke_shortcode' ) );
		add_shortcode( 'cookies_policy_link', array( $this, 'cookies_policy_link_shortcode' ) );
	}

	/**
	 * Register cookies accepted shortcode.
	 *
	 * @param array $args
	 * @param mixed $content
	 * @return mixed
	 */
	public function cookies_accepted_shortcode( $args, $content ) {
		if ( $this->cookies_accepted() ) {
			$scripts = html_entity_decode( trim( wp_kses( $content, $this->get_allowed_html() ) ) );

			if ( ! empty( $scripts ) ) {
				if ( preg_match_all( '/' . get_shortcode_regex() . '/', $content ) )
					$scripts = do_shortcode( $scripts );

				return $scripts;
			}
		}

		return '';
	}

	/**
	 * Register cookies accepted shortcode.
	 *
	 * @param array $args
	 * @param mixed $content
	 * @return mixed
	 */
	public function cookies_revoke_shortcode( $args, $content ) {
		// get options
		$options = $this->options['general'];

		// defaults
		$defaults = array(
			'title'	=> $options['revoke_text'],
			'class'	=> $options['css_class']
		);

		// combine shortcode arguments
		$args = shortcode_atts( $defaults, $args );

		// escape class(es)
		$args['class'] = esc_attr( $args['class'] );

		if ( Cookie_Notice()->get_status() === 'active' )
			$shortcode = '<a href="#" class="cn-revoke-cookie cn-button-inline cn-revoke-inline' . ( $args['class'] !== '' ? ' ' . $args['class'] : '' ) . '" title="' . esc_html( $args['title'] ) . '" data-hu-action="cookies-notice-revoke">' . esc_html( $args['title'] ) . '</a>';
		else
			$shortcode = '<a href="#" class="cn-revoke-cookie cn-button-inline cn-revoke-inline' . ( $args['class'] !== '' ? ' ' . $args['class'] : '' ) . '" title="' . esc_html( $args['title'] ) . '">' . esc_html( $args['title'] ) . '</a>';

		return $shortcode;
	}

	/**
	 * Register cookies policy link shortcode.
	 *
	 * @param array $args
	 * @param string $content
	 * @return string
	 */
	public function cookies_policy_link_shortcode( $args, $content ) {
		// get options
		$options = $this->options['general'];

		// defaults
		$defaults = array(
			'title'	=> esc_html( $options['see_more_opt']['text'] !== '' ? $options['see_more_opt']['text'] : '&#x279c;' ),
			'link'	=> ( $options['see_more_opt']['link_type'] === 'custom' ? esc_url( $options['see_more_opt']['link'] ) : get_permalink( $options['see_more_opt']['id'] ) ),
			'class'	=> esc_attr( $options['css_class'] )
		);

		// combine shortcode arguments
		$args = shortcode_atts( $defaults, $args );

		$shortcode = '<a href="' . $args['link'] . '" target="' . $options['link_target'] . '" id="cn-more-info" class="cn-privacy-policy-link cn-link' . ( $args['class'] !== '' ? ' ' . $args['class'] : '' ) . '">' . esc_html( $args['title'] ) . '</a>';

		return $shortcode;
	}

	/**
	 * Check if cookies are accepted.
	 * 
	 * @return bool
	 */
	public static function cookies_accepted() {
		if ( Cookie_Notice()->get_status() === 'active' ) {
			$cookies = isset( $_COOKIE['hu-consent'] ) ? json_decode( stripslashes( $_COOKIE['hu-consent'] ), true ) : array();
			
			if ( ! empty( $cookies ) && is_array( $cookies ) ) {
				foreach( $cookies as $cookie_name => $cookie_value ) {
					switch ( $cookie_name ) {
						case 'consent':
							$cookies[$cookie_name] = (bool) $cookie_value;
							break;
						default:
							$cookies[$cookie_name] = is_array( $cookie_value ) ? array_map( 'sanitize_text_field', $cookie_value ) : sanitize_text_field( $cookie_value );
					}
				}
			}

			$result = ( is_array( $cookies ) && json_last_error() === JSON_ERROR_NONE && ! empty( $cookies['consent'] ) ) ? true : false;
		} else
			$result = isset( $_COOKIE['cookie_notice_accepted'] ) && $_COOKIE['cookie_notice_accepted'] === 'true';

		return apply_filters( 'cn_is_cookie_accepted', $result );
	}

	/**
	 * Check if cookies are set.
	 *
	 * @return boolean Whether cookies are set
	 */
	public function cookies_set() {
		if ( Cookie_Notice()->get_status() === 'active' )
			$result = isset( $_COOKIE['hu-consent'] );
		else
			$result = isset( $_COOKIE['cookie_notice_accepted'] );

		return apply_filters( 'cn_is_cookie_set', $result );
	}

	/**
	 * Add WP Super Cache cookie.
	 */
	public function wpsc_add_cookie() {
		if ( Cookie_Notice()->get_status() === 'active' )
			do_action( 'wpsc_add_cookie', 'hu-consent' );
		else
			do_action( 'wpsc_add_cookie', 'cookie_notice_accepted' );
	}

	/**
	 * Delete WP Super Cache cookie.
	 */
	public function wpsc_delete_cookie() {
		if ( Cookie_Notice()->get_status() === 'active' )
			do_action( 'wpsc_delete_cookie', 'hu-consent' );
		else
			do_action( 'wpsc_delete_cookie', 'cookie_notice_accepted' );
	}
	
	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $page
	 * @return void
	 */
	public function admin_enqueue_scripts( $page ) {
		// plugins?
		if ( $page === 'plugins.php' ) {
			add_thickbox();

			wp_enqueue_script( 'cookie-notice-admin-plugins', plugins_url( '/js/admin-plugins.js', __FILE__ ), array( 'jquery' ), $this->defaults['version'] );

			wp_enqueue_style( 'cookie-notice-admin-plugins', plugins_url( '/css/admin-plugins.css', __FILE__ ), array(), $this->defaults['version'] );

			wp_localize_script(
				'cookie-notice-admin-plugins',
				'cnArgsPlugins',
				array(
					'deactivate'	=> __( 'Cookie Notice & Compliance - Deactivation survey', 'cookie-notice' ),
					'nonce'			=> wp_create_nonce( 'cn-deactivate-plugin' )
				)
			);
		}
		
		// load notice, if no compliance only
		if ( $this->options['general']['update_notice'] === true && empty( $this->status ) ) {
			wp_enqueue_script(
				'cookie-notice-admin-notice', plugins_url( '/js/admin-notice.js', __FILE__ ), array( 'jquery' ), Cookie_Notice()->defaults['version']
			);

			wp_localize_script(
				'cookie-notice-admin-notice', 'cnArgsNotice', array(
					'ajaxURL'				=> admin_url( 'admin-ajax.php' ),
					'nonce'					=> wp_create_nonce( 'cn_dismiss_notice' ),
				)
			);
			
			wp_enqueue_style(
				'cookie-notice-admin-notice', plugins_url( '/css/admin-notice.css', __FILE__ ), array(), Cookie_Notice()->defaults['version']
			);
		}
	}

	/**
	 * Add links to settings page.
	 * 
	 * @param array $links
	 * @param string $file
	 * @return array
	 */
	public function plugin_action_links( $links, $file ) {
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return $links;
		
		static $plugin;

		$plugin = plugin_basename( __FILE__ );

		if ( $file == $plugin ) {
			if ( ! empty( $links['deactivate'] ) ) {
				// link already contains class attribute?
				if ( preg_match( '/<a.*?class=(\'|")(.*?)(\'|").*?>/is', $links['deactivate'], $result ) === 1 )
					$links['deactivate'] = preg_replace( '/(<a.*?class=(?:\'|").*?)((?:\'|").*?>)/s', '$1 cn-deactivate-plugin-modal$2', $links['deactivate'] );
				else
					$links['deactivate'] = preg_replace( '/(<a.*?)>/s', '$1 class="cn-deactivate-plugin-modal">', $links['deactivate'] );

				// link already contains href attribute?
				if ( preg_match( '/<a.*?href=(\'|")(.*?)(\'|").*?>/is', $links['deactivate'], $result ) === 1 ) {
					if ( ! empty( $result[2] ) )
						$this->deactivaion_url = $result[2];
				}
			}

			// put settings link at start
			array_unshift( $links, sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php' ) . '?page=cookie-notice', __( 'Settings', 'cookie-notice' ) ) );

			// add add-ons link
			if ( empty( $this->status ) )
				$links[] = sprintf( '<a href="%s" style="color: #20C19E; font-weight: bold;">%s</a>', admin_url( 'admin.php' ) . '?page=cookie-notice&welcome=1', __( 'Free Upgrade', 'cookie-notice' ) );
		}

		return $links;
	}
	
	/**
	 * Deactivation modal HTML template.
	 *
	 * @return void
	 */
	public function deactivate_plugin_template() {
		global $pagenow;

		// display only for plugins page
		if ( $pagenow !== 'plugins.php' )
			return;

		echo '
		<div id="cn-deactivation-modal" style="display: none;">
			<div id="cn-deactivation-container">
				<div id="cn-deactivation-body">
					<div class="cn-deactivation-options">
						<p><em>' . __( "We're sorry to see you go. Could you please tell us what happened?", 'cookie-notice' ) . '</em></p>
						<ul>';

			foreach ( array(
				'1'	=> __( "I couldn't figure out how to make it work.", 'cookie-notice' ),
				'2'	=> __( 'I found another plugin to use for the same task.', 'cookie-notice' ),
				'3'	=> __( 'The Cookie Compliance banner is too big.', 'cookie-notice' ),
				'4'	=> __( 'The Cookie Compliance consent choices (Silver, Gold, Platinum) are confusing.', 'cookie-notice' ),
				'5'	=> __( 'The Cookie Compliance default settings are too strict.', 'cookie-notice' ),
				'6'	=> __( 'The web application user interface is not clear to me.', 'cookie-notice' ),
				'7'	=> __( "Support isn't timely.", 'cookie-notice' ),
				'8'	=> __( 'Other', 'cookie-notice' )
			) as $option => $text ) {
				echo '
							<li><label><input type="radio" name="cn_deactivation_option" value="' . $option . '" ' . checked( '8', $option, false ) . ' />' . esc_html( $text ) . '</label></li>';
			}

			echo '
						</ul>
					</div>
					<div class="cn-deactivation-textarea">
						<textarea name="cn_deactivation_other"></textarea>
					</div>
				</div>
				<div id="cn-deactivation-footer">
					<a href="" class="button cn-deactivate-plugin-cancel">' . __( 'Cancel', 'cookie-notice' ) . '</a>
					<a href="' . $this->deactivaion_url . '" class="button button-secondary cn-deactivate-plugin-simple">' . __( 'Deactivate', 'cookie-notice' ) . '</a>
					<a href="' . $this->deactivaion_url . '" class="button button-primary right cn-deactivate-plugin-data">' . __( 'Deactivate & Submit', 'cookie-notice' ) . '</a>
					<span class="spinner"></span>
				</div>
			</div>
		</div>';
	}

	/**
	 * Send data about deactivation of the plugin.
	 *
	 * @return void
	 */
	public function deactivate_plugin() {
		// check permissions
		if ( ! current_user_can( 'install_plugins' ) || wp_verify_nonce( $_POST['nonce'], 'cn-deactivate-plugin' ) === false )
			return;

		if ( isset( $_POST['option_id'] ) ) {
			$option_id = (int) $_POST['option_id'];
			$other = esc_html( $_POST['other'] );
			
			// avoid fake submissions
			if ( $option_id == 8 && $other == '' )
				wp_send_json_success();

			wp_remote_post(
			'https://hu-manity.co/wp-json/api/v1/forms/', array(
				'timeout'		=> 15,
				'blocking'		=> true,
				'headers'		=> array(),
				'body'			=> array(
					'id'		=> 1,
					'option'	=> $option_id,
					'other'		=> $other,
					'referrer'	=> get_site_url()
				)
			)
			);

			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Get allowed script blocking HTML.
	 *
	 * @return array
	 */
	public function get_allowed_html() {
		return apply_filters(
			'cn_refuse_code_allowed_html',
			array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'script' => array(
						'type' => array(),
						'src' => array(),
						'charset' => array(),
						'async' => array()
					),
					'noscript' => array(),
					'style' => array(
						'type' => array()
					),
					'iframe' => array(
						'src' => array(),
						'height' => array(),
						'width' => array(),
						'frameborder' => array(),
						'allowfullscreen' => array()
					)
				)
			)
		);
	}

	/**
	 * Helper: convert hex color to rgb color.
	 * 
	 * @param type $color
	 * @return array
	 */
	public function hex2rgb( $color ) {
		if ( $color[0] == '#' )
			$color = substr( $color, 1 );

		if ( strlen( $color ) == 6 )
			list( $r, $g, $b ) = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		elseif ( strlen( $color ) == 3 )
			list( $r, $g, $b ) = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		else
			return false;

		$r = hexdec( $r );
		$g = hexdec( $g );
		$b = hexdec( $b );

		return array( $r, $g, $b );
	}
	
	/**
	 * Helper: Convert undersocores to CamelCase/
	 * 
	 * @param type $string
	 * @param bool $capitalize_first_char
	 * @return string
	 */
	public function underscores_to_camelcase( $string, $capitalize_first_char = false ) {
		$str = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $string ) ) );

		if ( ! $capitalize_first_char ) {
			$str[0] = strtolower( $str[0] );
		}

		return $str;
	}
	
	/**
	 * Check legacy parameters that were yes/no strings.
	 *
	 * @param array $options
	 * @param array $params
	 * @return array
	 */
	public function check_legacy_params( $options, $params ) {
		foreach ( $params as $param ) {
			if ( array_key_exists( $param, $options ) && ! is_bool( $options[$param] ) )
				$options[$param] = $options[$param] === 'yes';
		}

		return $options;
	}

	/**
	 * Merge multidimensional associative arrays.
	 * Works only with strings, integers and arrays as keys. Values can be any type but they have to have same type to be kept in the final array.
	 * Every array should have the same type of elements. Only keys from $defaults array will be kept in the final array unless $siblings are not empty.
	 * $siblings examples: array( '=>', 'only_first_level', 'first_level=>second_level', 'first_key=>next_key=>sibling' ) and so on.
	 * Single '=>' means that all siblings of the highest level will be kept in the final array.
	 *
	 * @param array	$default Array with defaults values
	 * @param array	$array Array to merge
	 * @param boolean|array	$siblings Whether to allow "string" siblings to copy from $array if they do not exist in $defaults, false otherwise
	 * @return array Merged arrays
	 */
	public function multi_array_merge( $defaults, $array, $siblings = false ) {
		// make a copy for better performance and to prevent $default override in foreach
		$copy = $defaults;

		// prepare siblings for recursive deeper level
		$new_siblings = array();

		// allow siblings?
		if ( ! empty( $siblings ) && is_array( $siblings ) ) {
			foreach ( $siblings as $sibling ) {
				// highest level siblings
				if ( $sibling === '=>' ) {
					// copy all non-existent string siblings
					foreach( $array as $key => $value ) {
						if ( is_string( $key ) && ! array_key_exists( $key, $defaults ) ) {
							$defaults[$key] = null;
						}
					}
				// sublevel siblings
				} else {
					// explode siblings
					$ex = explode( '=>', $sibling );

					// copy all non-existent siblings
					foreach ( array_keys( $array[$ex[0]] ) as $key ) {
						if ( ! array_key_exists( $key, $defaults[$ex[0]] ) )
							$defaults[$ex[0]][$key] = null;
					}

					// more than one sibling child?
					if ( count( $ex ) > 1 )
						$new_siblings[$ex[0]] = array( substr_replace( $sibling, '', 0, strlen( $ex[0] . '=>' ) ) );
					// no more sibling children
					else
						$new_siblings[$ex[0]] = false;
				}
			}
		}

		// loop through first array
		foreach ( $defaults as $key => $value ) {
			// integer key?
			if ( is_int( $key ) ) {
				$copy = array_unique( array_merge( $defaults, $array ), SORT_REGULAR );

				break;
			// string key?
			} elseif ( is_string( $key ) && isset( $array[$key] ) ) {
				// string, boolean, integer or null values?
				if ( ( is_string( $value ) && is_string( $array[$key] ) ) || ( is_bool( $value ) && is_bool( $array[$key] ) ) || ( is_int( $value ) && is_int( $array[$key] ) ) || is_null( $value ) )
					$copy[$key] = $array[$key];
				// arrays
				elseif ( is_array( $value ) && isset( $array[$key] ) && is_array( $array[$key] ) ) {
					if ( empty( $value ) )
						$copy[$key] = $array[$key];
					else
						$copy[$key] = $this->multi_array_merge( $defaults[$key], $array[$key], ( isset( $new_siblings[$key] ) ? $new_siblings[$key] : false ) );
				}
			}
		}

		return $copy;
	}
	
	/**
	 * Get plugin mode
	 * 
	 * @return type
	 */
	public function get_status() {
		return $this->status; // notice, active, pending etc.
	}

	/**
	 * Indicate if current page is the Cookie Policy page
	 *
	 * @return bool
	 */
	public function is_cookie_policy_page() {
		$see_more = $this->options['general']['see_more_opt'];
		
		if ( $see_more['link_type'] !== 'page' )
			return false;

		$cp_id = $see_more['id'];
		$cp_slug = get_post_field( 'post_name', $cp_id );

		$current_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );

		return $current_page->post_name === $cp_slug;
	}

}

/**
 * Initialize Cookie Notice.
 */
function Cookie_Notice() {
	static $instance;

	// first call to instance() initializes the plugin
	if ( $instance === null || ! ( $instance instanceof Cookie_Notice ) )
		$instance = Cookie_Notice::instance();

	return $instance;
}

$cookie_notice = Cookie_Notice();
