<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Cookie_Notice_Settings class.
 * 
 * @class Cookie_Notice_Settings
 */
class Cookie_Notice_Settings {
	private $app_login_url = '';
	
	public $positions = array();
	public $styles = array();
	public $choices = array();
	public $links = array();
	public $link_targets = array();
	public $link_positions = array();
	public $colors = array();
	public $options = array();
	public $effects = array();
	public $times = array();
	public $notices = array();
	public $script_placements = array();
	public $countries = array();
	public $level_names = array();
	public $text_strings = array();
	
	public function __construct() {
		// actions
		add_action( 'admin_menu', array( $this, 'admin_menu_options' ) );
		add_action( 'after_setup_theme', array( $this, 'load_defaults' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
		add_action( 'wp_ajax_cn_purge_cache', array( $this, 'ajax_purge_cache' ) );
		add_action( 'admin_notices', array( $this, 'settings_errors' ) );
		
		$this->app_login_url = 'https://app.hu-manity.co/#/en/cc2/login';
	}
	
	/**
	 * Load plugin defaults
	 */
	public function load_defaults() {
		$this->positions = array(
			'top'	 			=> __( 'Top', 'cookie-notice' ),
			'bottom' 			=> __( 'Bottom', 'cookie-notice' )
		);

		$this->styles = array(
			'none'		 		=> __( 'None', 'cookie-notice' ),
			'wp-default' 		=> __( 'Light', 'cookie-notice' ),
			'bootstrap'	 		=> __( 'Dark', 'cookie-notice' )
		);
		
		$this->revoke_opts = array(
			'automatic'	 		=> __( 'Automatic', 'cookie-notice' ),
			'manual' 			=> __( 'Manual', 'cookie-notice' )
		);

		$this->links = array(
			'page'	 			=> __( 'Page link', 'cookie-notice' ),
			'custom' 			=> __( 'Custom link', 'cookie-notice' )
		);

		$this->link_targets = array(
			'_blank',
			'_self'
		);

		$this->link_positions = array(
			'banner'			=> __( 'Banner', 'cookie-notice' ),
			'message' 			=> __( 'Message', 'cookie-notice' )
		);

		$this->colors = array(
			'text'	 			=> __( 'Text color', 'cookie-notice' ),
			'button'	 		=> __( 'Button color', 'cookie-notice' ),
			'bar'	 			=> __( 'Bar color', 'cookie-notice' ),
		);

		$this->times = apply_filters(
			'cn_cookie_expiry',
			array(
				'hour'				=> array( __( 'An hour', 'cookie-notice' ), 3600 ),
				'day'		 		=> array( __( '1 day', 'cookie-notice' ), 86400 ),
				'week'		 		=> array( __( '1 week', 'cookie-notice' ), 604800 ),
				'month'		 		=> array( __( '1 month', 'cookie-notice' ), 2592000 ),
				'3months'	 		=> array( __( '3 months', 'cookie-notice' ), 7862400 ),
				'6months'	 		=> array( __( '6 months', 'cookie-notice' ), 15811200 ),
				'year'		 		=> array( __( '1 year', 'cookie-notice' ), 31536000 ),
				'infinity'	 		=> array( __( 'infinity', 'cookie-notice' ), 2147483647 )
			)
		);

		$this->effects = array(
			'none'	 			=> __( 'None', 'cookie-notice' ),
			'fade'	 			=> __( 'Fade', 'cookie-notice' ),
			'slide'	 			=> __( 'Slide', 'cookie-notice' )
		);

		$this->script_placements = array(
			'header' 			=> __( 'Header', 'cookie-notice' ),
			'footer' 			=> __( 'Footer', 'cookie-notice' ),
		);
		
		$this->level_names = array(
			1 => array(
				1 => __( 'Silver', 'cookie-notice' ),
				2 => __( 'Gold', 'cookie-notice' ),
				3 => __( 'Platinum', 'cookie-notice' )
			),
			2 => array(
				1 => __( 'Private', 'cookie-notice' ),
				2 => __( 'Balanced', 'cookie-notice' ),
				3 => __( 'Personalized', 'cookie-notice' )
			),
			3 => array(
				1 => __( 'Reject All', 'cookie-notice' ),
				2 => __( 'Accept Some', 'cookie-notice' ),
				3 => __( 'Accept All', 'cookie-notice' )
			)
		);
		
		$this->text_strings = array(
			'saveBtnText' => __( 'Save my preferences', 'cookie-notice' ),
			// 'acceptBtnText' => __( 'Accept', 'cookie-notice' ),
			// 'rejectBtnText' => __( 'Reject', 'cookie-notice' ),
			// 'revokeBtnText' => __( 'Revoke Cookies', 'cookie-notice' ),
			'privacyBtnText' => __( 'Privacy policy', 'cookie-notice' ),
			'dontSellBtnText' => __( 'Do Not Sell', 'cookie-notice' ),
			'customizeBtnText' => __( 'Preferences', 'cookie-notice' ),
			'headingText' => __( "We believe your data is your property and support your right to privacy and transparency.", 'cookie-notice' ),
			'bodyText' => __( "Select a Data Access Level and Duration to choose how we use and share your data.", 'cookie-notice' ),
			'levelBodyText_1' => __( 'Highest level of privacy. Data accessed for necessary site operations only. Data shared with 3rd parties to ensure the site is secure and works on your device.', 'cookie-notice' ),
			'levelBodyText_2' => __( 'Balanced experience. Data accessed for content personalisation and site optimisation. Data shared with 3rd parties may be used to track and store your preferences for this site.', 'cookie-notice' ),
			'levelBodyText_3' => __( 'Highest level of personalisation. Data accessed to make ads and media more relevant. Data shared with 3rd parties may be use to track you on this site and other sites you visit.', 'cookie-notice' ),
			'levelNameText_1' => $this->level_names[1][1],
			'levelNameText_2' => $this->level_names[1][2],
			'levelNameText_3' => $this->level_names[1][3],
			'monthText' => __( 'month', 'cookie-notice' ),
			'monthsText' => __( 'months', 'cookie-notice' )
		);
		
		// set default text strings
		Cookie_Notice()->defaults['general']['message_text'] = __( 'We use cookies to ensure that we give you the best experience on our website. If you continue to use this site we will assume that you are happy with it.', 'cookie-notice' );
		Cookie_Notice()->defaults['general']['accept_text'] = __( 'Ok', 'cookie-notice' );
		Cookie_Notice()->defaults['general']['refuse_text'] = __( 'No', 'cookie-notice' );
		Cookie_Notice()->defaults['general']['revoke_message_text'] = __( 'You can revoke your consent any time using the Revoke consent button.', 'cookie-notice' );
		Cookie_Notice()->defaults['general']['revoke_text'] = __( 'Revoke consent', 'cookie-notice' );
		Cookie_Notice()->defaults['general']['see_more_opt']['text'] = __( 'Privacy policy', 'cookie-notice' );

		// set translation strings on plugin activation
		if ( Cookie_Notice()->options['general']['translate'] === true ) {
			Cookie_Notice()->options['general']['translate'] = false;

			Cookie_Notice()->options['general']['message_text'] = Cookie_Notice()->defaults['general']['message_text'];
			Cookie_Notice()->options['general']['accept_text'] = Cookie_Notice()->defaults['general']['accept_text'];
			Cookie_Notice()->options['general']['refuse_text'] = Cookie_Notice()->defaults['general']['refuse_text'];
			Cookie_Notice()->options['general']['revoke_message_text'] = Cookie_Notice()->defaults['general']['revoke_message_text'];
			Cookie_Notice()->options['general']['revoke_text'] = Cookie_Notice()->defaults['general']['revoke_text'];
			Cookie_Notice()->options['general']['see_more_opt']['text'] = Cookie_Notice()->defaults['general']['see_more_opt']['text'];

			update_option( 'cookie_notice_options', Cookie_Notice()->options['general'] );
		}

		// WPML >= 3.2
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
			$this->register_wpml_strings();
		// WPML and Polylang compatibility
		} elseif ( function_exists( 'icl_register_string' ) ) {
			icl_register_string( 'Cookie Notice', 'Message in the notice', Cookie_Notice()->options['general']['message_text'] );
			icl_register_string( 'Cookie Notice', 'Button text', Cookie_Notice()->options['general']['accept_text'] );
			icl_register_string( 'Cookie Notice', 'Refuse button text', Cookie_Notice()->options['general']['refuse_text'] );
			icl_register_string( 'Cookie Notice', 'Revoke message text', Cookie_Notice()->options['general']['revoke_message_text'] );
			icl_register_string( 'Cookie Notice', 'Revoke button text', Cookie_Notice()->options['general']['revoke_text'] );
			icl_register_string( 'Cookie Notice', 'Privacy policy text', Cookie_Notice()->options['general']['see_more_opt']['text'] );
			icl_register_string( 'Cookie Notice', 'Custom link', Cookie_Notice()->options['general']['see_more_opt']['link'] );
		}
	}
	
	/**
	 * Add submenu.
	 */
	public function admin_menu_options() {
		add_menu_page( __( 'Cookie Notice', 'cookie-notice' ), __( 'Cookies', 'cookie-notice' ), apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ), 'cookie-notice', array( $this, 'options_page' ), 'none', '99.3' );
	}

	/**
	 * Options page output.
	 * 
	 * @return mixed
	 */
	public function options_page() {
		echo '
		<div class="wrap">
			<h2>' . __( 'Cookie Notice & Compliance for GDPR/CCPA', 'cookie-notice' ) . '</h2>
			<div class="cookie-notice-settings">
				<div class="cookie-notice-sidebar">
					<div class="cookie-notice-credits">
						<div class="inside">
							<div class="inner">';

			// compliance enabled
			if ( Cookie_Notice()->get_status() === 'active' ) {
				echo '			<h2>We\'re Promoting Privacy&trade;</h2>
								<p>' . __( 'Promote the privacy of your website visitors without affecting how you do your business.', 'cookie-notice' ) . '</p>';
			} else {
				echo '			<h1><b>Cookie Compliance&trade;</b></h1>
								<h2>' . __( 'The next generation of Cookie Notice', 'cookie-notice' ) . '</h2>
								<div class="cn-lead">
									<p>' . __( 'A free web application to help you deliver better consent experiences and comply with GDPR, CCPA and other data privacy laws more effectively.', 'cookie-notice' ) . '</p>
								</div>
								<img src="//cns2-53eb.kxcdn.com/screen-dashboard-small.png">
								<a href="' . esc_url( admin_url( 'index.php?page=cookie-notice' ) ) . '" class="button button-primary button-hero cn-button cn-run-upgrade">' . __( 'Free Upgrade', 'cookie-notice' ) . '</a>';
			}

			echo '
							</div>
						</div>
					</div>';
			
			if ( Cookie_Notice()->get_status() !== 'active' ) {
				echo '
					<div class="cookie-notice-faq">
						<h2>' . __( 'F.A.Q.', 'cookie-notice' ) . '</h2>
						<div class="cn-toggle-container">
							<label for="cn-faq-1" class="cn-toggle-item">
								<input id="cn-faq-1" type="checkbox" checked />
								<span class="cn-toggle-heading">' . __( 'Does the Cookie Notice make my site fully compliant with GDPR?', 'cookie-notice' ) . '</span>
								<span class="cn-toggle-body">' . __( 'It is not possible to provide the required technical compliance features using only a WordPress plugin. Features like consent record storage, purpose categories and script blocking that bring your site into full compliance with GDPR are only available through the Cookie Compliance integration.', 'cookie-notice' ) . '
							</label>
							<label for="cn-faq-2" class="cn-toggle-item">
								<input id="cn-faq-2" type="checkbox" />
								<span class="cn-toggle-heading">' . __( 'Does the Cookie Compiance integration make my site fully compliant with GDPR?', 'cookie-notice' ) . '</span>
								<span class="cn-toggle-body">' . __( 'Yes! The plugin + web application version includes technical compliance features to meet requirements for over 100 countries and legal jurisdictions.', 'cookie-notice' ) . '</span>
							</label>
							<label for="cn-faq-3" class="cn-toggle-item">
								<input id="cn-faq-3" type="checkbox" />
								<span class="cn-toggle-heading">' . __( 'Is Cookie Compliance free?', 'cookie-notice' ) . '</span>
								<span class="cn-toggle-body">' . __( 'Yes, but with limits. Cookie Compliance includes both free and paid plans to choose from depending on your needs and your website monthly traffic.', 'cookie-notice' ) . '</span>
							</label>
						</div>
					</div>';
			}
			
			echo '
				</div>
				<form action="options.php" method="post">';

		settings_fields( 'cookie_notice_options' );
		do_settings_sections( 'cookie_notice_options' );
		
		echo '
				<p class="submit">';
		submit_button( '', 'primary', 'save_cookie_notice_options', false );
		echo ' ';
		submit_button( __( 'Reset to defaults', 'cookie-notice' ), 'secondary', 'reset_cookie_notice_options', false );
		echo '
				</p>
				</form>
			</div>
			<div class="clear"></div>
		</div>';
	}

	/**
	 * Regiseter plugin settings.
	 */
	public function register_settings() {
		register_setting( 'cookie_notice_options', 'cookie_notice_options', array( $this, 'validate_options' ) );

		// compliance enabled
		if ( Cookie_Notice()->get_status() === 'active' ) {
			// configuration
			add_settings_section( 'cookie_notice_compliance', __( 'Compliance Settings', 'cookie-notice' ), array( $this, 'cn_section_compliance' ), 'cookie_notice_options' );
			add_settings_field( 'cn_app_status', __( 'Compliance status', 'cookie-notice' ), array( $this, 'cn_app_status' ), 'cookie_notice_options', 'cookie_notice_compliance' );
			add_settings_field( 'cn_app_id', __( 'App ID', 'cookie-notice' ), array( $this, 'cn_app_id' ), 'cookie_notice_options', 'cookie_notice_compliance' );
			add_settings_field( 'cn_app_key', __( 'App Key', 'cookie-notice' ), array( $this, 'cn_app_key' ), 'cookie_notice_options', 'cookie_notice_compliance' );
			
			add_settings_section( 'cookie_notice_configuration', __( 'Miscellaneous Settings', 'cookie-notice' ), array( $this, 'cn_section_configuration' ), 'cookie_notice_options' );
			add_settings_field( 'cn_app_blocking', __( 'Autoblocking', 'cookie-notice' ), array( $this, 'cn_app_blocking' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_hide_banner', __( 'Hide for logged in', 'cookie-notice' ), array( $this, 'cn_hide_banner' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_app_purge_cache', __( 'Cache', 'cookie-notice' ), array( $this, 'cn_app_purge_cache' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_script_placement', __( 'Script placement', 'cookie-notice' ), array( $this, 'cn_script_placement' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_deactivation_delete', __( 'Deactivation', 'cookie-notice' ), array( $this, 'cn_deactivation_delete' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		// compliance disabled
		} else {
			// configuration
			add_settings_section( 'cookie_notice_compliance', __( 'Compliance Settings', 'cookie-notice' ), array( $this, 'cn_section_compliance' ), 'cookie_notice_options' );
			add_settings_field( 'cn_app_status', __( 'Compliance status', 'cookie-notice' ), array( $this, 'cn_app_status' ), 'cookie_notice_options', 'cookie_notice_compliance' );
			add_settings_field( 'cn_app_id', __( 'App ID', 'cookie-notice' ), array( $this, 'cn_app_id' ), 'cookie_notice_options', 'cookie_notice_compliance' );
			add_settings_field( 'cn_app_key', __( 'App Key', 'cookie-notice' ), array( $this, 'cn_app_key' ), 'cookie_notice_options', 'cookie_notice_compliance' );
			
			add_settings_section( 'cookie_notice_configuration', __( 'Notice Settings', 'cookie-notice' ), array( $this, 'cn_section_configuration' ), 'cookie_notice_options' );
			add_settings_field( 'cn_message_text', __( 'Message', 'cookie-notice' ), array( $this, 'cn_message_text' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_accept_text', __( 'Button text', 'cookie-notice' ), array( $this, 'cn_accept_text' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_see_more', __( 'Privacy policy', 'cookie-notice' ), array( $this, 'cn_see_more' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_refuse_opt', __( 'Refuse consent', 'cookie-notice' ), array( $this, 'cn_refuse_opt' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_revoke_opt', __( 'Revoke consent', 'cookie-notice' ), array( $this, 'cn_revoke_opt' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_refuse_code', __( 'Script blocking', 'cookie-notice' ), array( $this, 'cn_refuse_code' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_redirection', __( 'Reloading', 'cookie-notice' ), array( $this, 'cn_redirection' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_on_scroll', __( 'On scroll', 'cookie-notice' ), array( $this, 'cn_on_scroll' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_on_click', __( 'On click', 'cookie-notice' ), array( $this, 'cn_on_click' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_time', __( 'Accepted expiry', 'cookie-notice' ), array( $this, 'cn_time' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_time_rejected', __( 'Rejected expiry', 'cookie-notice' ), array( $this, 'cn_time_rejected' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_script_placement', __( 'Script placement', 'cookie-notice' ), array( $this, 'cn_script_placement' ), 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_deactivation_delete', __( 'Deactivation', 'cookie-notice' ), array( $this, 'cn_deactivation_delete' ), 'cookie_notice_options', 'cookie_notice_configuration' );

			// design
			add_settings_section( 'cookie_notice_design', __( 'Notice Design', 'cookie-notice' ), array( $this, 'cn_section_design' ), 'cookie_notice_options' );
			add_settings_field( 'cn_position', __( 'Position', 'cookie-notice' ), array( $this, 'cn_position' ), 'cookie_notice_options', 'cookie_notice_design' );
			add_settings_field( 'cn_hide_effect', __( 'Animation', 'cookie-notice' ), array( $this, 'cn_hide_effect' ), 'cookie_notice_options', 'cookie_notice_design' );
			add_settings_field( 'cn_colors', __( 'Colors', 'cookie-notice' ), array( $this, 'cn_colors' ), 'cookie_notice_options', 'cookie_notice_design' );
			add_settings_field( 'cn_css_class', __( 'Button class', 'cookie-notice' ), array( $this, 'cn_css_class' ), 'cookie_notice_options', 'cookie_notice_design' );
		}
	}

	/**
	 * Section callback: fix for WP < 3.3
	 */
	public function cn_section_configuration() {}
	public function cn_section_compliance() {}
	public function cn_section_design() {}
	
	/**
	 * Compliance status.
	 */
	public function cn_app_status() {
		$app_status = Cookie_Notice()->get_status();
		
		switch ( $app_status ) {
			case 'active':
				echo '
				<fieldset>
					<div id="cn_app_status">
						<span class="cn_compliance_status">' . __( 'Notice', 'cookie-notice' ) . ': <label class="cn-active">' . __( 'Active', 'cookie-notice' ) . '</label></span>
						<span class="cn_compliance_status">' . __( 'Autoblocking', 'cookie-notice' ) . ': <label class="cn-active">' . __( 'Active', 'cookie-notice' ) . '</label></span>
						<span class="cn_compliance_status">' . __( 'Cookie Categories', 'cookie-notice' ) . ': <label class="cn-active">' . __( 'Active', 'cookie-notice' ) . '</label></span>
						<span class="cn_compliance_status">' . __( 'Proof-of-Consent', 'cookie-notice' ) . ': <label class="cn-active">' . __( 'Active', 'cookie-notice' ) . '</label></span>
					</div>
					<div id="cn_app_actions">
						<a href="' . $this->app_login_url . '" class="button button-primary button-hero cn-button" target="_blank">' . __( 'Log in & Configure', 'cookie-notice' ) . '</a>
						<p class="description">' . __( 'Log into the Cookie Compliance&trade; web application and configure your Privacy Experience.', 'cookie-notice' ) . '</p>
					</div>
				</fieldset>';
				break;
			case 'pending':
				echo '
				<fieldset>
					<div id="cn_app_status">
						<span class="cn_compliance_status">' . __( 'Notice', 'cookie-notice' ) . ': <label class="cn-active">' . __( 'Active', 'cookie-notice' ) . '</label></span>
						<span class="cn_compliance_status">' . __( 'Autoblocking', 'cookie-notice' ) . ': <label class="cn-pending">' . __( 'Pending', 'cookie-notice' ) . '</label></span>
						<span class="cn_compliance_status">' . __( 'Cookie Categories', 'cookie-notice' ) . ': <label class="cn-pending">' . __( 'Pending', 'cookie-notice' ) . '</label></span>
						<span class="cn_compliance_status">' . __( 'Proof-of-Consent', 'cookie-notice' ) . ': <label class="cn-pending">' . __( 'Pending', 'cookie-notice' ) . '</label></span>
					</div>
					<div id="cn_app_actions">
						<a href="' . $this->app_login_url . '" class="button button-primary button-hero cn-button" target="_blank">' . __( 'Log in & configure', 'cookie-notice' ) . '</a>
						<p class="description">' . __( 'Log into the Cookie Compliance&trade; web application and complete the setup process.', 'cookie-notice' ) . '</p>
					</div>
				</fieldset>';
				break;
			default:
				echo '
				<fieldset>
					<div id="cn_app_status">
						<span class="cn_compliance_status">' . __( 'Notice', 'cookie-notice' ) . ': <label class="cn-active">' . __( 'Active', 'cookie-notice' ) . '</label></span>
						<span class="cn_compliance_status">' . __( 'Autoblocking', 'cookie-notice' ) . ': <label class="cn-inactive">' . __( 'Inactive', 'cookie-notice' ) . '</label></span>
						<span class="cn_compliance_status">' . __( 'Cookie Categories', 'cookie-notice' ) . ': <label class="cn-inactive">' . __( 'Inactive', 'cookie-notice' ) . '</label></span>
						<span class="cn_compliance_status">' . __( 'Proof-of-Consent', 'cookie-notice' ) . ': <label class="cn-inactive">' . __( 'Inactive', 'cookie-notice' ) . '</label></span>
					</div>
					<div id="cn_app_actions">
						<a href="' . admin_url( 'index.php?page=cookie-notice' ) . '" class="button button-primary button-hero cn-button cn-run-welcome">' . __( 'Add Compliance features', 'cookie-notice' ) . '</a>
						<p class="description">' . __( 'Sign up to Cookie Compliance&trade; and add GDPR, CCPA and other international data privacy laws compliance features.', 'cookie-notice' ) . '</p>
					</div>
				</fieldset>';
				break;
		}
	}
	
	/**
	 * App welcome.
	 */
	public function cn_app_id() {
		echo '
		<fieldset>
			<div id="cn_app_id">
				<input type="text" class="regular-text" name="cookie_notice_options[app_id]" value="' . esc_attr( Cookie_Notice()->options['general']['app_id'] ) . '" />
				<p class="description">' . __( 'Enter your Cookie Compliance&trade; application ID.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}
	
	/**
	 * App ID option.
	 */
	public function cn_app_key() {
		echo '
		<div id="cn_app_key">
			<input type="password" class="regular-text" name="cookie_notice_options[app_key]" value="' . esc_attr( Cookie_Notice()->options['general']['app_key'] ) . '" />
			<p class="description">' . __( 'Enter your Cookie Compliance&trade; application secret key.', 'cookie-notice' ) . '</p>
		</div>';
	}
	
	/**
	 * App autoblocking.
	 */
	public function cn_app_blocking() {
		echo '
		<fieldset>
			<label for="cn_app_blocking"><input id="cn_app_blocking" type="checkbox" name="cookie_notice_options[app_blocking]" value="1" ' . checked( true, Cookie_Notice()->options['general']['app_blocking'], false ) . ' />' . __( 'Enable to automatically block 3rd party scripts before user consent.', 'cookie-notice' ) . '</label>
			<p class="description">' . __( "In case you're experiencing issues with your site disable that feature temporarily.", 'cookie-notice' ) . '</p>
		</fieldset>';
	}

	/**
	 * App ID option.
	 */
	public function cn_app_purge_cache() {
		echo '
		<div id="cn_app_purge_cache">
			<div class="cn-button-container">
				<a href="#" class="button button-secondary">' . __( 'Purge Cache', 'cookie-notice' ) . '</a>
			</div>
			<p class="description">' . __( 'Click the Purge Cache button to refresh the app configuration.', 'cookie-notice' ) . '</p>
		</div>';
	}
	
	/**
	 * Hide banner.
	 */
	public function cn_hide_banner() {
		echo '
		<fieldset>
			<label for="cn_hide_banner"><input id="cn_hide_banner" type="checkbox" name="cookie_notice_options[hide_banner]" value="1" ' . checked( true, Cookie_Notice()->options['general']['hide_banner'], false ) . ' />' . __( 'Enable to hide the consent banner for logged in users.', 'cookie-notice' ) . '</label>
		</fieldset>';
	}

	/**
	 * App Key option.
	 */
	public function cn_message_text() {
		echo '
		<div id="cn_message_text">
			<textarea name="cookie_notice_options[message_text]" class="large-text" cols="50" rows="5">' . esc_textarea( Cookie_Notice()->options['general']['message_text'] ) . '</textarea>
			<p class="description">' . __( 'Enter the cookie notice message.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Accept cookie label option.
	 */
	public function cn_accept_text() {
		echo '
		<div id="cn_accept_text">
			<input type="text" class="regular-text" name="cookie_notice_options[accept_text]" value="' . esc_attr( Cookie_Notice()->options['general']['accept_text'] ) . '" />
		<p class="description">' . __( 'The text of the option to accept the notice and make it disappear.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Enable/Disable third party non functional cookies option.
	 */
	public function cn_refuse_opt() {
		echo '
		<fieldset>
			<label><input id="cn_refuse_opt" type="checkbox" name="cookie_notice_options[refuse_opt]" value="1" ' . checked( true, Cookie_Notice()->options['general']['refuse_opt'], false ) . ' />' . __( 'Enable to give to the user the possibility to refuse third party non functional cookies.', 'cookie-notice' ) . '</label>
			<div id="cn_refuse_opt_container"' . ( Cookie_Notice()->options['general']['refuse_opt'] === false ? ' style="display: none;"' : '' ) . '>
				<div id="cn_refuse_text">
					<input type="text" class="regular-text" name="cookie_notice_options[refuse_text]" value="' . esc_attr( Cookie_Notice()->options['general']['refuse_text'] ) . '" />
					<p class="description">' . __( 'The text of the button to refuse the consent.', 'cookie-notice' ) . '</p>
				</div>
			</div>
		</fieldset>';
	}

	/**
	 * Non functional cookies code.
	 */
	public function cn_refuse_code() {
		$allowed_html = Cookie_Notice()->get_allowed_html();
		$active = ! empty( Cookie_Notice()->options['general']['refuse_code'] ) && empty( Cookie_Notice()->options['general']['refuse_code_head'] ) ? 'body' : 'head';

		echo '
		<fieldset>
			<div id="cn_refuse_code">
				<div id="cn_refuse_code_fields">
					<h2 class="nav-tab-wrapper">
						<a id="refuse_head-tab" class="nav-tab' . ( $active === 'head' ? ' nav-tab-active' : '' ) . '" href="#refuse_head">' . __( 'Head', 'cookie-notice' ) . '</a>
						<a id="refuse_body-tab" class="nav-tab' . ( $active === 'body' ? ' nav-tab-active' : '' ) . '" href="#refuse_body">' . __( 'Body', 'cookie-notice' ) . '</a>
					</h2>
					<div id="refuse_head" class="refuse-code-tab' . ( $active === 'head' ? ' active' : '' ) . '">
						<p class="description">' . __( 'The code to be used in your site header, before the closing head tag.', 'cookie-notice' ) . '</p>
						<textarea name="cookie_notice_options[refuse_code_head]" class="large-text" cols="50" rows="8">' . html_entity_decode( trim( wp_kses( Cookie_Notice()->options['general']['refuse_code_head'], $allowed_html ) ) ) . '</textarea>
					</div>
					<div id="refuse_body" class="refuse-code-tab' . ( $active === 'body' ? ' active' : '' ) . '">
						<p class="description">' . __( 'The code to be used in your site footer, before the closing body tag.', 'cookie-notice' ) . '</p>
						<textarea name="cookie_notice_options[refuse_code]" class="large-text" cols="50" rows="8">' . html_entity_decode( trim( wp_kses( Cookie_Notice()->options['general']['refuse_code'], $allowed_html ) ) ) . '</textarea>
					</div>
				</div>
				<p class="description">' . __( 'Enter non functional cookies Javascript code here (for e.g. Google Analitycs) to be used after the notice is accepted.', 'cookie-notice' ) . '</br>' . __( 'To get the user consent status use the <code>cn_cookies_accepted()</code> function.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Revoke cookies option.
	 */
	public function cn_revoke_opt() {
		echo '
		<fieldset>
			<label><input id="cn_revoke_cookies" type="checkbox" name="cookie_notice_options[revoke_cookies]" value="1" ' . checked( true, Cookie_Notice()->options['general']['revoke_cookies'], false ) . ' />' . __( 'Enable to give to the user the possibility to revoke their consent <i>(requires "Refuse consent" option enabled)</i>.', 'cookie-notice' ) . '</label>
			<div id="cn_revoke_opt_container"' . ( Cookie_Notice()->options['general']['revoke_cookies'] ? '' : ' style="display: none;"' ) . '>
				<textarea name="cookie_notice_options[revoke_message_text]" class="large-text" cols="50" rows="2">' . esc_textarea( Cookie_Notice()->options['general']['revoke_message_text'] ) . '</textarea>
				<p class="description">' . __( 'Enter the revoke message.', 'cookie-notice' ) . '</p>
				<input type="text" class="regular-text" name="cookie_notice_options[revoke_text]" value="' . esc_attr( Cookie_Notice()->options['general']['revoke_text'] ) . '" />
				<p class="description">' . __( 'The text of the button to revoke the consent.', 'cookie-notice' ) . '</p>';

		foreach ( $this->revoke_opts as $value => $label ) {
			echo '
				<label><input id="cn_revoke_cookies-' . $value . '" type="radio" name="cookie_notice_options[revoke_cookies_opt]" value="' . $value . '" ' . checked( $value, Cookie_Notice()->options['general']['revoke_cookies_opt'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select the method for displaying the revoke button - automatic (in the banner) or manual using <code>[cookies_revoke]</code> shortcode.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Redirection on cookie accept.
	 */
	public function cn_redirection() {
		echo '
		<fieldset>
			<label><input id="cn_redirection" type="checkbox" name="cookie_notice_options[redirection]" value="1" ' . checked( true, Cookie_Notice()->options['general']['redirection'], false ) . ' />' . __( 'Enable to reload the page after the notice is accepted.', 'cookie-notice' ) . '</label>
		</fieldset>';
	}

	/**
	 * Privacy policy link option.
	 */
	public function cn_see_more() {
		$pages = get_pages(
			array(
				'sort_order'	=> 'ASC',
				'sort_column'	=> 'post_title',
				'hierarchical'	=> 0,
				'child_of'		=> 0,
				'parent'		=> -1,
				'offset'		=> 0,
				'post_type'		=> 'page',
				'post_status'	=> 'publish'
			)
		);

		echo '
		<fieldset>
			<label><input id="cn_see_more" type="checkbox" name="cookie_notice_options[see_more]" value="1" ' . checked( true, Cookie_Notice()->options['general']['see_more'], false ) . ' />' . __( 'Enable privacy policy link.', 'cookie-notice' ) . '</label>
			<div id="cn_see_more_opt"' . (Cookie_Notice()->options['general']['see_more'] === false ? ' style="display: none;"' : '') . '>
				<input type="text" class="regular-text" name="cookie_notice_options[see_more_opt][text]" value="' . esc_attr( Cookie_Notice()->options['general']['see_more_opt']['text'] ) . '" />
				<p class="description">' . __( 'The text of the privacy policy button.', 'cookie-notice' ) . '</p>
				<div id="cn_see_more_opt_custom_link">';

		foreach ( $this->links as $value => $label ) {
			$value = esc_attr( $value );

			echo '
					<label><input id="cn_see_more_link-' . $value . '" type="radio" name="cookie_notice_options[see_more_opt][link_type]" value="' . $value . '" ' . checked( $value, Cookie_Notice()->options['general']['see_more_opt']['link_type'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				</div>
				<p class="description">' . __( 'Select where to redirect user for more information.', 'cookie-notice' ) . '</p>
				<div id="cn_see_more_opt_page"' . (Cookie_Notice()->options['general']['see_more_opt']['link_type'] === 'custom' ? ' style="display: none;"' : '') . '>
					<select name="cookie_notice_options[see_more_opt][id]">
						<option value="0" ' . selected( 0, Cookie_Notice()->options['general']['see_more_opt']['id'], false ) . '>' . __( '-- select page --', 'cookie-notice' ) . '</option>';

		if ( $pages ) {
			foreach ( $pages as $page ) {
				echo '
						<option value="' . $page->ID . '" ' . selected( $page->ID, Cookie_Notice()->options['general']['see_more_opt']['id'], false ) . '>' . esc_html( $page->post_title ) . '</option>';
			}
		}

		echo '
					</select>
					<p class="description">' . __( 'Select from one of your site\'s pages.', 'cookie-notice' ) . '</p>';

		global $wp_version;

		if ( version_compare( $wp_version, '4.9.6', '>=' ) ) {
			echo '
						<label><input id="cn_see_more_opt_sync" type="checkbox" name="cookie_notice_options[see_more_opt][sync]" value="1" ' . checked( true, Cookie_Notice()->options['general']['see_more_opt']['sync'], false ) . ' />' . __( 'Synchronize with WordPress Privacy Policy page.', 'cookie-notice' ) . '</label>';
		}

		echo '
				</div>
				<div id="cn_see_more_opt_link"' . (Cookie_Notice()->options['general']['see_more_opt']['link_type'] === 'page' ? ' style="display: none;"' : '') . '>
					<input type="text" class="regular-text" name="cookie_notice_options[see_more_opt][link]" value="' . esc_attr( Cookie_Notice()->options['general']['see_more_opt']['link'] ) . '" />
					<p class="description">' . __( 'Enter the full URL starting with http(s)://', 'cookie-notice' ) . '</p>
				</div>
				<div id="cn_see_more_link_target">';

		foreach ( $this->link_targets as $target ) {
			echo '
					<label><input id="cn_see_more_link_target-' . $target . '" type="radio" name="cookie_notice_options[link_target]" value="' . $target . '" ' . checked( $target, Cookie_Notice()->options['general']['link_target'], false ) . ' />' . $target . '</label>';
		}

		echo '
					<p class="description">' . esc_html__( 'Select the privacy policy link target.', 'cookie-notice' ) . '</p>
				</div>
				<div id="cn_see_more_link_position">';

		foreach ( $this->link_positions as $position => $label ) {
			echo '
					<label><input id="cn_see_more_link_position-' . $position . '" type="radio" name="cookie_notice_options[link_position]" value="' . $position . '" ' . checked( $position, Cookie_Notice()->options['general']['link_position'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
					<p class="description">' . esc_html__( 'Select the privacy policy link position.', 'cookie-notice' ) . '</p>
				</div></div>
		</fieldset>';
	}

	/**
	 * Expiration time option.
	 */
	public function cn_time() {
		echo '
		<fieldset>
			<div id="cn_time">
				<select name="cookie_notice_options[time]">';

		foreach ( $this->times as $time => $arr ) {
			$time = esc_attr( $time );

			echo '
					<option value="' . $time . '" ' . selected( $time, Cookie_Notice()->options['general']['time'] ) . '>' . esc_html( $arr[0] ) . '</option>';
		}

		echo '
				</select>
				<p class="description">' . __( 'The amount of time that the cookie should be stored for when user accepts the notice.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Expiration time option.
	 */
	public function cn_time_rejected() {
		echo '
		<fieldset>
			<div id="cn_time_rejected">
				<select name="cookie_notice_options[time_rejected]">';

		foreach ( $this->times as $time => $arr ) {
			$time = esc_attr( $time );

			echo '
					<option value="' . $time . '" ' . selected( $time, Cookie_Notice()->options['general']['time_rejected'] ) . '>' . esc_html( $arr[0] ) . '</option>';
		}

		echo '
				</select>
				<p class="description">' . __( 'The amount of time that the cookie should be stored for when the user doesn\'t accept the notice.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Script placement option.
	 */
	public function cn_script_placement() {
		echo '
		<fieldset>';

		foreach ( $this->script_placements as $value => $label ) {
			echo '
			<label><input id="cn_script_placement-' . $value . '" type="radio" name="cookie_notice_options[script_placement]" value="' . esc_attr( $value ) . '" ' . checked( $value, Cookie_Notice()->options['general']['script_placement'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
			<p class="description">' . __( 'Select where all the plugin scripts should be placed.', 'cookie-notice' ) . '</p>
		</fieldset>';
	}

	/**
	 * Position option.
	 */
	public function cn_position() {
		echo '
		<fieldset>
			<div id="cn_position">';

		foreach ( $this->positions as $value => $label ) {
			$value = esc_attr( $value );

			echo '
				<label><input id="cn_position-' . $value . '" type="radio" name="cookie_notice_options[position]" value="' . $value . '" ' . checked( $value, Cookie_Notice()->options['general']['position'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select location for the notice.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Animation effect option.
	 */
	public function cn_hide_effect() {
		echo '
		<fieldset>
			<div id="cn_hide_effect">';

		foreach ( $this->effects as $value => $label ) {
			$value = esc_attr( $value );

			echo '
				<label><input id="cn_hide_effect-' . $value . '" type="radio" name="cookie_notice_options[hide_effect]" value="' . $value . '" ' . checked( $value, Cookie_Notice()->options['general']['hide_effect'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select the animation style.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * On scroll option.
	 */
	public function cn_on_scroll() {
		echo '
		<fieldset>
			<label><input id="cn_on_scroll" type="checkbox" name="cookie_notice_options[on_scroll]" value="1" ' . checked( true, Cookie_Notice()->options['general']['on_scroll'], false ) . ' />' . __( 'Enable to accept the notice when user scrolls.', 'cookie-notice' ) . '</label>
			<div id="cn_on_scroll_offset"' . ( Cookie_Notice()->options['general']['on_scroll'] === false || Cookie_Notice()->options['general']['on_scroll'] == false ? ' style="display: none;"' : '' ) . '>
				<input type="text" class="text" name="cookie_notice_options[on_scroll_offset]" value="' . esc_attr( Cookie_Notice()->options['general']['on_scroll_offset'] ) . '" /> <span>px</span>
				<p class="description">' . __( 'Number of pixels user has to scroll to accept the notice and make it disappear.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}
	
	/**
	 * On click option.
	 */
	public function cn_on_click() {
		echo '
		<fieldset>
			<label><input id="cn_on_click" type="checkbox" name="cookie_notice_options[on_click]" value="1" ' . checked( true, Cookie_Notice()->options['general']['on_click'], false ) . ' />' . __( 'Enable to accept the notice on any click on the page.', 'cookie-notice' ) . '</label>
		</fieldset>';
	}
	
	/**
	 * Delete plugin data on deactivation.
	 */
	public function cn_deactivation_delete() {
		echo '
		<fieldset>
			<label><input id="cn_deactivation_delete" type="checkbox" name="cookie_notice_options[deactivation_delete]" value="1" ' . checked( true, Cookie_Notice()->options['general']['deactivation_delete'], false ) . '/>' . __( 'Enable if you want all plugin data to be deleted on deactivation.', 'cookie-notice' ) . '</label>
		</fieldset>';
	}

	/**
	 * CSS style option.
	 */
	public function cn_css_class() {
		echo '
		<fieldset>
			<div id="cn_css_class">
				<input type="text" class="regular-text" name="cookie_notice_options[css_class]" value="' . esc_attr( Cookie_Notice()->options['general']['css_class'] ) . '" />
				<p class="description">' . __( 'Enter additional button CSS classes separated by spaces.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Colors option.
	 */
	public function cn_colors() {
		echo '
		<fieldset>';
		
		foreach ( $this->colors as $value => $label ) {
			$value = esc_attr( $value );

			echo '
			<div id="cn_colors-' . $value . '"><label>' . esc_html( $label ) . '</label><br />
				<input class="cn_color" type="text" name="cookie_notice_options[colors][' . $value . ']" value="' . esc_attr( Cookie_Notice()->options['general']['colors'][$value] ) . '" />' .
			'</div>';
		}
		
		// print_r( Cookie_Notice()->options['general']['colors'] );
		
		echo '
			<div id="cn_colors-bar_opacity"><label>' . __( 'Bar opacity', 'cookie-notice' ) . '</label><br />
				<div><input id="cn_colors_bar_opacity_range" class="cn_range" type="range" min="50" max="100" step="1" name="cookie_notice_options[colors][bar_opacity]" value="' . absint( Cookie_Notice()->options['general']['colors']['bar_opacity'] ) . '" onchange="cn_colors_bar_opacity_text.value = cn_colors_bar_opacity_range.value" /><input id="cn_colors_bar_opacity_text" class="small-text" type="number" onchange="cn_colors_bar_opacity_range.value = cn_colors_bar_opacity_text.value" min="50" max="100" value="' . absint( Cookie_Notice()->options['general']['colors']['bar_opacity'] ) . '" /></div>' .
			'</div>';
		
		echo '
		</fieldset>';
	}

	/**
	 * Validate options.
	 * 
	 * @param array $input
	 * @return array
	 */
	public function validate_options( $input ) {
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return $input;

		// get main instance
		$cn = Cookie_Notice();

		if ( isset( $_POST['save_cookie_notice_options'] ) ) {
			// app id
			$input['app_id'] = sanitize_text_field( isset( $input['app_id'] ) ? $input['app_id'] : $cn->defaults['general']['app_id'] );
			
			// app key
			$input['app_key'] = sanitize_text_field( isset( $input['app_key'] ) ? $input['app_key'] : $cn->defaults['general']['app_key'] );
			
			// set app status
			if ( ! empty( $input['app_id'] ) && ! empty( $input['app_key'] ) ) {
				$app_status = esc_attr( $cn->welcome_api->get_app_status( $input['app_id'] ) );
				
				update_option( 'cookie_notice_status', $app_status );
			} else {
				update_option( 'cookie_notice_status', '' );
			}
			
			// app blocking
			$input['app_blocking'] = (bool) isset( $input['app_blocking'] );
			
			// hide banner
			$input['hide_banner'] = (bool) isset( $input['hide_banner'] );
			
			// position
			$input['position'] = sanitize_text_field( isset( $input['position'] ) && in_array( $input['position'], array_keys( $this->positions ) ) ? $input['position'] : $cn->defaults['general']['position'] );

			// colors
			$input['colors']['text'] = sanitize_text_field( isset( $input['colors']['text'] ) && $input['colors']['text'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['text'] ) === 1 ? $input['colors']['text'] : $cn->defaults['general']['colors']['text'] );
			$input['colors']['button'] = sanitize_text_field( isset( $input['colors']['button'] ) && $input['colors']['button'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['button'] ) === 1 ? $input['colors']['button'] : $cn->defaults['general']['colors']['button'] );
			$input['colors']['bar'] = sanitize_text_field( isset( $input['colors']['bar'] ) && $input['colors']['bar'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['bar'] ) === 1 ? $input['colors']['bar'] : $cn->defaults['general']['colors']['bar'] );
			$input['colors']['bar_opacity'] = absint( isset( $input['colors']['bar_opacity'] ) && $input['colors']['bar_opacity'] >= 50 ? $input['colors']['bar_opacity'] : $cn->defaults['general']['colors']['bar_opacity'] );

			// texts
			$input['message_text'] = wp_kses_post( isset( $input['message_text'] ) && $input['message_text'] !== '' ? $input['message_text'] : $cn->defaults['general']['message_text'] );
			$input['accept_text'] = sanitize_text_field( isset( $input['accept_text'] ) && $input['accept_text'] !== '' ? $input['accept_text'] : $cn->defaults['general']['accept_text'] );
			$input['refuse_text'] = sanitize_text_field( isset( $input['refuse_text'] ) && $input['refuse_text'] !== '' ? $input['refuse_text'] : $cn->defaults['general']['refuse_text'] );
			$input['revoke_message_text'] = wp_kses_post( isset( $input['revoke_message_text'] ) && $input['revoke_message_text'] !== '' ? $input['revoke_message_text'] : $cn->defaults['general']['revoke_message_text'] );
			$input['revoke_text'] = sanitize_text_field( isset( $input['revoke_text'] ) && $input['revoke_text'] !== '' ? $input['revoke_text'] : $cn->defaults['general']['revoke_text'] );
			$input['refuse_opt'] = (bool) isset( $input['refuse_opt'] );
			$input['revoke_cookies'] = isset( $input['revoke_cookies'] );
			$input['revoke_cookies_opt'] = isset( $input['revoke_cookies_opt'] ) && array_key_exists( $input['revoke_cookies_opt'], $this->revoke_opts ) ? $input['revoke_cookies_opt'] : $cn->defaults['general']['revoke_cookies_opt'];

			// get allowed HTML
			$allowed_html = $cn->get_allowed_html();

			// body refuse code
			$input['refuse_code'] = wp_kses( isset( $input['refuse_code'] ) && $input['refuse_code'] !== '' ? $input['refuse_code'] : $cn->defaults['general']['refuse_code'], $allowed_html );

			// head refuse code
			$input['refuse_code_head'] = wp_kses( isset( $input['refuse_code_head'] ) && $input['refuse_code_head'] !== '' ? $input['refuse_code_head'] : $cn->defaults['general']['refuse_code_head'], $allowed_html );

			// css button class
			$input['css_class'] = sanitize_text_field( isset( $input['css_class'] ) ? $input['css_class'] : $cn->defaults['general']['css_class'] );

			// link target
			$input['link_target'] = sanitize_text_field( isset( $input['link_target'] ) && in_array( $input['link_target'], array_keys( $this->link_targets ) ) ? $input['link_target'] : $cn->defaults['general']['link_target'] );

			// time
			$input['time'] = sanitize_text_field( isset( $input['time'] ) && in_array( $input['time'], array_keys( $this->times ) ) ? $input['time'] : $cn->defaults['general']['time'] );
			$input['time_rejected'] = sanitize_text_field( isset( $input['time_rejected'] ) && in_array( $input['time_rejected'], array_keys( $this->times ) ) ? $input['time_rejected'] : $cn->defaults['general']['time_rejected'] );

			// script placement
			$input['script_placement'] = sanitize_text_field( isset( $input['script_placement'] ) && in_array( $input['script_placement'], array_keys( $this->script_placements ) ) ? $input['script_placement'] : $cn->defaults['general']['script_placement'] );

			// hide effect
			$input['hide_effect'] = sanitize_text_field( isset( $input['hide_effect'] ) && in_array( $input['hide_effect'], array_keys( $this->effects ) ) ? $input['hide_effect'] : $cn->defaults['general']['hide_effect'] );
			
			// redirection
			$input['redirection'] = isset( $input['redirection'] );
			
			// on scroll
			$input['on_scroll'] = isset( $input['on_scroll'] );

			// on scroll offset
			$input['on_scroll_offset'] = absint( isset( $input['on_scroll_offset'] ) && $input['on_scroll_offset'] !== '' ? $input['on_scroll_offset'] : $cn->defaults['general']['on_scroll_offset'] );
			
			// on click
			$input['on_click'] = isset( $input['on_click'] );

			// deactivation
			$input['deactivation_delete'] = isset( $input['deactivation_delete'] );

			// privacy policy
			$input['see_more'] = isset( $input['see_more'] );
			$input['see_more_opt']['text'] = sanitize_text_field( isset( $input['see_more_opt']['text'] ) && $input['see_more_opt']['text'] !== '' ? $input['see_more_opt']['text'] : $cn->defaults['general']['see_more_opt']['text'] );
			$input['see_more_opt']['link_type'] = sanitize_text_field( isset( $input['see_more_opt']['link_type'] ) && in_array( $input['see_more_opt']['link_type'], array_keys( $this->links ) ) ? $input['see_more_opt']['link_type'] : $cn->defaults['general']['see_more_opt']['link_type'] );

			if ( $input['see_more_opt']['link_type'] === 'custom' )
				$input['see_more_opt']['link'] = ( $input['see_more'] === true ? esc_url( $input['see_more_opt']['link'] ) : '' );
			elseif ( $input['see_more_opt']['link_type'] === 'page' ) {
				$input['see_more_opt']['id'] = ( $input['see_more'] === true ? (int) $input['see_more_opt']['id'] : 0 );
				$input['see_more_opt']['sync'] = isset( $input['see_more_opt']['sync'] );

				if ( $input['see_more_opt']['sync'] )
					update_option( 'wp_page_for_privacy_policy', $input['see_more_opt']['id'] );
			}
			
			// policy link position
			$input['link_position'] = sanitize_text_field( isset( $input['link_position'] ) && in_array( $input['link_position'], array_keys( $this->link_positions ) ) ? $input['link_position'] : $cn->defaults['general']['link_position'] );

			// message link position?
			if ( $input['see_more'] === true && $input['link_position'] === 'message' && strpos( $input['message_text'], '[cookies_policy_link' ) === false )
				$input['message_text'] .= ' [cookies_policy_link]';
			
			$input['update_version'] = $cn->options['general']['update_version'];
			$input['update_notice'] = $cn->options['general']['update_notice'];

			$input['translate'] = false;

			// WPML >= 3.2
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Message in the notice', $input['message_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Button text', $input['accept_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Refuse button text', $input['refuse_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Revoke message text', $input['revoke_message_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Revoke button text', $input['revoke_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Privacy policy text', $input['see_more_opt']['text'] );

				if ( $input['see_more_opt']['link_type'] === 'custom' )
					do_action( 'wpml_register_single_string', 'Cookie Notice', 'Custom link', $input['see_more_opt']['link'] );
			}
			
			add_settings_error( 'cn_cookie_notice_options', 'save_cookie_notice_options', __( 'Settings saved.', 'iubenda' ), 'updated' );
			
			// purge cache on save
			delete_transient( 'cookie_notice_compliance_cache' );
		} elseif ( isset( $_POST['reset_cookie_notice_options'] ) ) {
			$input = $cn->defaults['general'];

			add_settings_error( 'cn_cookie_notice_options', 'reset_cookie_notice_options', __( 'Settings restored to defaults.', 'cookie-notice' ), 'updated' );
			
			// set app status
			update_option( 'cookie_notice_status', '' );
			
			// purge cache on save
			delete_transient( 'cookie_notice_compliance_cache' );
		}

		return $input;
	}
	
	/**
	 * Load scripts and styles - admin.
	 */
	public function admin_enqueue_scripts( $page ) {
		if ( $page === 'toplevel_page_cookie-notice' ) {
			wp_enqueue_script(
				'cookie-notice-admin', plugins_url( '../js/admin' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), Cookie_Notice()->defaults['version']
			);

			wp_localize_script(
				'cookie-notice-admin', 'cnArgs', array(
					'ajaxURL'				=> admin_url( 'admin-ajax.php' ),
					'nonce'					=> wp_create_nonce( 'cn-purge-cache' ),
					'resetToDefaults'	=> __( 'Are you sure you want to reset these settings to defaults?', 'cookie-notice' )
				)
			);

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'cookie-notice-admin', plugins_url( '../css/admin' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.css', __FILE__ ) );
		}
	}
	
	/**
	 * Load admin style inline, for menu icon only.
	 *
	 * @return mixed
	 */
	public function admin_print_styles() {
		echo '
		<style>
			a.toplevel_page_cookie-notice .wp-menu-image {
				background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj48c3ZnIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIHZpZXdCb3g9IjAgMCAzMjEgMzIxIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zOnNlcmlmPSJodHRwOi8vd3d3LnNlcmlmLmNvbS8iIHN0eWxlPSJmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtzdHJva2UtbGluZWpvaW46cm91bmQ7c3Ryb2tlLW1pdGVybGltaXQ6MjsiPjxwYXRoIGQ9Ik0zMTcuMjc4LDEzMC40NTFjLTAuODEyLC00LjMwMiAtNC4zMDEsLTcuNTYyIC04LjY0MiwtOC4wODFjLTQuMzU0LC0wLjUyMiAtOC41MDYsMS44MjkgLTEwLjMwNyw1LjgyMmMtMy4xNyw3LjAwMyAtMTAuMTMzLDExLjg3MyAtMTguMjA1LDExLjg2NGMtOC45NTUsMC4wMjIgLTE2LjUxNywtNi4wMjEgLTE5LjAzOCwtMTQuMzE1Yy0xLjUyMSwtNS4wNjMgLTYuNzI0LC04LjA2NCAtMTEuODY1LC02Ljg2M2MtMy4xNjMsMC43NDEgLTYuMTU0LDEuMTcyIC05LjEyNSwxLjE3MmMtMjIuMDM5LC0wLjA0MyAtMzkuOTc2LC0xNy45NzkgLTQwLjAxNSwtNDAuMDE5Yy0wLC0yLjk3IDAuNDMsLTUuOTYyIDEuMTY5LC05LjExM2MxLjIxMiwtNS4xNDEgLTEuNzk5LC0xMC4zNTMgLTYuODYsLTExLjg3M2MtOC4yOTUsLTIuNTEzIC0xNC4zMzcsLTEwLjA3NSAtMTQuMzE5LC0xOS4wMjljLTAuMDA5LC04LjA4MiA0Ljg2NCwtMTUuMDM2IDExLjg2NywtMTguMjA4YzMuOTkxLC0xLjc5OCA2LjM0MSwtNS45NjMgNS44MjIsLTEwLjMwNGMtMC41MjIsLTQuMzUxIC0zLjc4MywtNy44NDMgLTguMDg0LC04LjY1MmMtOS41NDMsLTEuNzkyIC0xOS40MjYsLTIuODUyIC0yOS42MTEsLTIuODUyYy04OC4yOTUsMC4wMjIgLTE2MC4wNDMsNzEuNzcgLTE2MC4wNjUsMTYwLjA2NWMwLjAyMiw4OC4yOTUgNzEuNzcsMTYwLjA0MyAxNjAuMDY1LDE2MC4wNjVjODguMjk1LC0wLjAyMiAxNjAuMDQzLC03MS43NyAxNjAuMDY1LC0xNjAuMDY1Yy0wLC0xMC4xODQgLTEuMDYzLC0yMC4wNjcgLTIuODUyLC0yOS42MTRabS01OC4yMjMsMTI4LjYwNGMtMjUuNDAxLDI1LjM4IC02MC4zNTUsNDEuMDY2IC05OC45OSw0MS4wNjZjLTM4LjYzNSwwIC03My41ODgsLTE1LjY4NiAtOTguOTg5LC00MS4wNjZjLTI1LjM4LC0yNS40MDEgLTQxLjA2NiwtNjAuMzU1IC00MS4wNjYsLTk4Ljk5Yy0wLC0zOC42MzUgMTUuNjg2LC03My41ODggNDEuMDY2LC05OC45ODljMjUuNDAxLC0yNS4zOCA2MC4zNTQsLTQxLjA2NiA5OC45ODksLTQxLjA2NmMxLjgwMSwwIDMuNTYsMC4xODkgNS4zNTIsMC4yNjhjLTMuMzQzLDUuODIzIC01LjM0MywxMi41MjcgLTUuMzUyLDE5LjczOGMwLjAxOCwxNC45MzUgOC4zMDQsMjcuNzQyIDIwLjM3OSwzNC41NzVjLTAuMTkyLDEuNzggLTAuMzczLDMuNTYgLTAuMzczLDUuNDRjMC4wMjIsMzMuMTI1IDI2LjkwMyw2MC4wMDcgNjAuMDI1LDYwLjAyNWMxLjg4LDAgMy42NjQsLTAuMTggNS40NDMsLTAuMzY5YzYuODMzLDEyLjA2NSAxOS42MjgsMjAuMzU2IDM0LjU3MiwyMC4zNzhjNy4yMTUsLTAuMDA5IDEzLjkxNiwtMi4wMTEgMTkuNzQxLC01LjM1MmMwLjA4LDEuNzggMC4yNjksMy41NTEgMC4yNjksNS4zNTJjLTAsMzguNjM1IC0xNS42ODYsNzMuNTg5IC00MS4wNjYsOTguOTlabS01OC45NzQsLTE4Ljk1OWMtMCwxMS4wNTIgLTguOTU4LDIwLjAxIC0yMC4wMSwyMC4wMWMtMTEuMDQ4LC0wIC0yMC4wMDUsLTguOTU4IC0yMC4wMDUsLTIwLjAxYy0wLC0xMS4wNDkgOC45NTcsLTIwLjAwNiAyMC4wMDUsLTIwLjAwNmMxMS4wNTIsLTAgMjAuMDEsOC45NTcgMjAuMDEsMjAuMDA2Wm0tODAuMDMxLC0xMC4wMDVjMCw1LjUyNiAtNC40NzksMTAuMDA1IC0xMC4wMDUsMTAuMDA1Yy01LjUyNiwtMCAtMTAuMDA1LC00LjQ3OSAtMTAuMDA1LC0xMC4wMDVjMCwtNS41MjMgNC40NzksLTEwLjAwMSAxMC4wMDUsLTEwLjAwMWM1LjUyNiwtMCAxMC4wMDUsNC40NzggMTAuMDA1LDEwLjAwMVptMTQwLjA1NSwtMjAuMDA2YzAsNS41MjYgLTQuNDc5LDEwLjAwNSAtMTAuMDA1LDEwLjAwNWMtNS41MjUsMCAtMTAuMDA1LC00LjQ3OSAtMTAuMDA1LC0xMC4wMDVjMCwtNS41MjYgNC40OCwtMTAuMDA1IDEwLjAwNSwtMTAuMDA1YzUuNTI2LDAgMTAuMDA1LDQuNDc5IDEwLjAwNSwxMC4wMDVabS0xNjAuMDY0LC01MC4wMmMtMCwxMS4wNDggLTguOTU3LDIwLjAwNiAtMjAuMDEsMjAuMDA2Yy0xMS4wNDgsMCAtMjAuMDA1LC04Ljk1OCAtMjAuMDA1LC0yMC4wMDZjLTAsLTExLjA1MiA4Ljk1NywtMjAuMDEgMjAuMDA1LC0yMC4wMWMxMS4wNTMsMCAyMC4wMSw4Ljk1OCAyMC4wMSwyMC4wMVptODAuMDMsMTAuMDA1YzAsNS41MjMgLTQuNDc4LDEwLjAwMSAtMTAuMDAxLDEwLjAwMWMtNS41MjYsMCAtMTAuMDA1LC00LjQ3OCAtMTAuMDA1LC0xMC4wMDFjMCwtNS41MjYgNC40NzksLTEwLjAwNSAxMC4wMDUsLTEwLjAwNWM1LjUyMywwIDEwLjAwMSw0LjQ3OSAxMC4wMDEsMTAuMDA1Wm0xMTUuNDkzLC02OS40MDZjMCw1LjUyNiAtNC40NzksMTAuMDA1IC0xMC4wMDUsMTAuMDA1Yy01LjUyNiwtMCAtMTAuMDA1LC00LjQ3OSAtMTAuMDA1LC0xMC4wMDVjMCwtNS41MjYgNC40NzksLTEwLjAwNSAxMC4wMDUsLTEwLjAwNWM1LjUyNiwtMCAxMC4wMDUsNC40NzkgMTAuMDA1LDEwLjAwNVptLTM1LjUyMywtMTkuODc0Yy0wLDExLjUwMyAtOS4zMjUsMjAuODI4IC0yMC44MjgsMjAuODI4Yy0xMS41MDQsLTAgLTIwLjgyOSwtOS4zMjUgLTIwLjgyOSwtMjAuODI4Yy0wLC0xMS41MDMgOS4zMjUsLTIwLjgyOCAyMC44MjksLTIwLjgyOGMxMS41MDMsLTAgMjAuODI4LDkuMzI1IDIwLjgyOCwyMC44MjhabS0xMTkuOTg1LC0wLjc1OWMtMCwxMS4wNTIgLTguOTU3LDIwLjAxIC0yMC4wMDYsMjAuMDFjLTExLjA1MiwtMCAtMjAuMDA5LC04Ljk1OCAtMjAuMDA5LC0yMC4wMWMtMCwtMTEuMDQ4IDguOTU3LC0yMC4wMDYgMjAuMDA5LC0yMC4wMDZjMTEuMDQ5LC0wIDIwLjAwNiw4Ljk1OCAyMC4wMDYsMjAuMDA2WiIgc3R5bGU9ImZpbGw6I2ZmZjtmaWxsLXJ1bGU6bm9uemVybzsiLz48L3N2Zz4=);
				background-position: center center;
				background-repeat: no-repeat;
				background-size: 18px auto;
			}
		</style>
		';
	}
	
	/**
	 * Register WPML (>= 3.2) strings if needed.
	 *
	 * @return	void
	 */
	private function register_wpml_strings() {
		// get main instance
		$cn = Cookie_Notice();

		global $wpdb;

		// prepare strings
		$strings = array(
			'Message in the notice'	=> $cn->options['general']['message_text'],
			'Button text'			=> $cn->options['general']['accept_text'],
			'Refuse button text'	=> $cn->options['general']['refuse_text'],
			'Revoke message text'	=> $cn->options['general']['revoke_message_text'],
			'Revoke button text'	=> $cn->options['general']['revoke_text'],
			'Privacy policy text'	=> $cn->options['general']['see_more_opt']['text'],
			'Custom link'			=> $cn->options['general']['see_more_opt']['link']
		);

		// get query results
		$results = $wpdb->get_col( $wpdb->prepare( "SELECT name FROM " . $wpdb->prefix . "icl_strings WHERE context = %s", 'Cookie Notice' ) );

		// check results
		foreach( $strings as $string => $value ) {
			// string does not exist?
			if ( ! in_array( $string, $results, true ) ) {
				// register string
				do_action( 'wpml_register_single_string', 'Cookie Notice', $string, $value );
			}
		}
	}
	
	/**
	 * Display errors and notices.
	 *
	 * @global string $pagenow
	 */
	public function settings_errors() {
		global $pagenow;

		// force display notices in top menu settings page
		if ( $pagenow == 'options-general.php' )
			return;

		settings_errors( 'cn_cookie_notice_options' );
	}
	
	/**
	 * Save compliance config caching.
	 */
	public function ajax_purge_cache() {	
		if ( ! check_ajax_referer( 'cn-purge-cache', 'nonce' ) )
			echo false;
		
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			echo false;
		
		delete_transient( 'cookie_notice_compliance_cache' );
		
		echo true;
		exit;
	}
}