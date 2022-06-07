<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Cookie_Notice_Frontend class.
 * 
 * @class Cookie_Notice_Frontend
 */
class Cookie_Notice_Frontend {
	private $widget_url = '';
	private $is_bot = false;
	private $hide_banner = false;
	
	public function __construct() {
		// actions
		add_action( 'init', array( $this, 'init' ) );

		$this->widget_url = '//cdn.hu-manity.co/hu-banner.min.js';
		$this->app_url = 'https://app.hu-manity.co';
	}

	/**
	 * Init frontend.
	 */
	public function init() {
		// purge cache
		if ( isset( $_GET['hu_purge_cache'] ) )
			$this->purge_cache();

		// is it preview mode?
		$this->preview_mode = isset( $_GET['cn_preview_mode'] );

		// is it a bot?
		$this->is_bot = Cookie_Notice()->bot_detect->is_crawler();
		
		// is user logged in and hiding the banner is enabled
		$this->hide_banner = is_user_logged_in() && Cookie_Notice()->options['general']['hide_banner'] === true;

		global $pagenow;

		// bail if in preview mode or it's a bot request
		if ( ! $this->preview_mode && ! $this->is_bot && ! $this->hide_banner && ! ( is_admin() && $pagenow === 'widgets.php' && isset( $_GET['legacy-widget-preview'] ) ) ) {
			// init cookie compliance
			if ( Cookie_Notice()->get_status() === 'active' ) {
				add_action( 'send_headers', array( $this, 'add_compliance_http_header' ) );
				add_action( 'wp_head', array( $this, 'add_cookie_compliance' ), 0 );
			// init cookie notice
			} else {
				// actions
				add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_notice_scripts' ) );
				add_filter( 'script_loader_tag', array( $this, 'wp_enqueue_script_async' ), 10, 3 );
				add_action( 'wp_head', array( $this, 'wp_print_header_scripts' ) );
				add_action( 'wp_print_footer_scripts', array( $this, 'wp_print_footer_scripts' ) );
				add_action( 'wp_footer', array( $this, 'add_cookie_notice' ), 1000 );

				// filters
				add_filter( 'body_class', array( $this, 'change_body_class' ) );
			}
		}
	}

	/**
	 * Add CORS header for API requests and purge cache.
	 */
	public function add_compliance_http_header() {
		header( "Access-Control-Allow-Origin: $this->app_url" );
		header( 'Access-Control-Allow-Methods: GET' );
	}

	/**
	 * Run Cookie Compliance.
	 *
	 * @return void
	 */
	public function add_cookie_compliance() {
		// get site language
		$locale = get_locale();
		$locale_code = explode( '_', $locale );

		// exceptions, norwegian
		if ( in_array( $locale_code, array( 'nb', 'nn' ) ) )
			$locale_code = 'no';

		$options = apply_filters( 'cn_cookie_compliance_args', array(
			'appID' => Cookie_Notice()->options['general']['app_id'],
			'currentLanguage'	=> $locale_code[0],
			'blocking' => (bool) ( ! is_user_logged_in() ? Cookie_Notice()->options['general']['app_blocking'] : false )
		) );
		
		// message output
		$output = '
		<!-- Hu Banner -->
		<script type="text/javascript">
			var huOptions = ' . json_encode( $options ) . ';
		</script>
		<script type="text/javascript" src="' . $this->widget_url . '"></script>';
		
		echo apply_filters( 'cn_cookie_compliance_output', $output, $options );
	}

	/**
	 * Cookie notice output.
	 * 
	 * @return mixed
	 */
	public function add_cookie_notice() {
		// WPML >= 3.2
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
			Cookie_Notice()->options['general']['message_text'] = apply_filters( 'wpml_translate_single_string', Cookie_Notice()->options['general']['message_text'], 'Cookie Notice', 'Message in the notice' );
			Cookie_Notice()->options['general']['accept_text'] = apply_filters( 'wpml_translate_single_string', Cookie_Notice()->options['general']['accept_text'], 'Cookie Notice', 'Button text' );
			Cookie_Notice()->options['general']['refuse_text'] = apply_filters( 'wpml_translate_single_string', Cookie_Notice()->options['general']['refuse_text'], 'Cookie Notice', 'Refuse button text' );
			Cookie_Notice()->options['general']['revoke_message_text'] = apply_filters( 'wpml_translate_single_string', Cookie_Notice()->options['general']['revoke_message_text'], 'Cookie Notice', 'Revoke message text' );
			Cookie_Notice()->options['general']['revoke_text'] = apply_filters( 'wpml_translate_single_string', Cookie_Notice()->options['general']['revoke_text'], 'Cookie Notice', 'Revoke button text' );
			Cookie_Notice()->options['general']['see_more_opt']['text'] = apply_filters( 'wpml_translate_single_string', Cookie_Notice()->options['general']['see_more_opt']['text'], 'Cookie Notice', 'Privacy policy text' );
			Cookie_Notice()->options['general']['see_more_opt']['link'] = apply_filters( 'wpml_translate_single_string', Cookie_Notice()->options['general']['see_more_opt']['link'], 'Cookie Notice', 'Custom link' );
		// WPML and Polylang compatibility
		} elseif ( function_exists( 'icl_t' ) ) {
			Cookie_Notice()->options['general']['message_text'] = icl_t( 'Cookie Notice', 'Message in the notice', Cookie_Notice()->options['general']['message_text'] );
			Cookie_Notice()->options['general']['accept_text'] = icl_t( 'Cookie Notice', 'Button text', Cookie_Notice()->options['general']['accept_text'] );
			Cookie_Notice()->options['general']['refuse_text'] = icl_t( 'Cookie Notice', 'Refuse button text', Cookie_Notice()->options['general']['refuse_text'] );
			Cookie_Notice()->options['general']['revoke_message_text'] = icl_t( 'Cookie Notice', 'Revoke message text', Cookie_Notice()->options['general']['revoke_message_text'] );
			Cookie_Notice()->options['general']['revoke_text'] = icl_t( 'Cookie Notice', 'Revoke button text', Cookie_Notice()->options['general']['revoke_text'] );
			Cookie_Notice()->options['general']['see_more_opt']['text'] = icl_t( 'Cookie Notice', 'Privacy policy text', Cookie_Notice()->options['general']['see_more_opt']['text'] );
			Cookie_Notice()->options['general']['see_more_opt']['link'] = icl_t( 'Cookie Notice', 'Custom link', Cookie_Notice()->options['general']['see_more_opt']['link'] );
		}

		if ( function_exists( 'icl_object_id' ) )
			Cookie_Notice()->options['general']['see_more_opt']['id'] = icl_object_id( Cookie_Notice()->options['general']['see_more_opt']['id'], 'page', true );

		// get cookie container args
		$options = apply_filters( 'cn_cookie_notice_args', array(
			'position'				=> Cookie_Notice()->options['general']['position'],
			'css_class'				=> Cookie_Notice()->options['general']['css_class'],
			'button_class'			=> 'cn-button',
			'colors'				=> Cookie_Notice()->options['general']['colors'],
			'message_text'			=> Cookie_Notice()->options['general']['message_text'],
			'accept_text'			=> Cookie_Notice()->options['general']['accept_text'],
			'refuse_text'			=> Cookie_Notice()->options['general']['refuse_text'],
			'revoke_message_text'	=> Cookie_Notice()->options['general']['revoke_message_text'],
			'revoke_text'			=> Cookie_Notice()->options['general']['revoke_text'],
			'refuse_opt'			=> Cookie_Notice()->options['general']['refuse_opt'],
			'revoke_cookies'		=> Cookie_Notice()->options['general']['revoke_cookies'],
			'see_more'				=> Cookie_Notice()->options['general']['see_more'],
			'see_more_opt'			=> Cookie_Notice()->options['general']['see_more_opt'],
			'link_target'			=> Cookie_Notice()->options['general']['link_target'],
			'link_position'			=> Cookie_Notice()->options['general']['link_position'],
			'aria_label'			=> 'Cookie Notice'
		) );

		// check legacy parameters
		$options = Cookie_Notice()->check_legacy_params( $options, array( 'refuse_opt', 'see_more' ) );

		if ( $options['see_more'] === true )
			$options['message_text'] = do_shortcode( wp_kses_post( $options['message_text'] ) );
		else
			$options['message_text'] = wp_kses_post( $options['message_text'] );

		$options['revoke_message_text'] = wp_kses_post( $options['revoke_message_text'] );

		// escape css classes
		$options['css_class'] = esc_attr( $options['css_class'] );
		$options['button_class'] = esc_attr( $options['button_class'] );

		// message output
		$output = '
		<!-- Cookie Notice plugin v' . Cookie_Notice()->defaults['version'] . ' by Hu-manity.co https://hu-manity.co/ -->
		<div id="cookie-notice" role="dialog" class="cookie-notice-hidden cookie-revoke-hidden cn-position-' . esc_attr( $options['position'] ) . '" aria-label="' . esc_attr( $options['aria_label'] ) . '" style="background-color: rgba(' . implode( ',', Cookie_Notice()->hex2rgb( $options['colors']['bar'] ) ) . ',' . ( (int) $options['colors']['bar_opacity'] ) * 0.01 . ');">'
			. '<div class="cookie-notice-container" style="color: ' . esc_attr( $options['colors']['text'] ) . ';">'
			. '<span id="cn-notice-text" class="cn-text-container">'. $options['message_text'] . '</span>'
			. '<span id="cn-notice-buttons" class="cn-buttons-container"><a href="#" id="cn-accept-cookie" data-cookie-set="accept" class="cn-set-cookie ' . $options['button_class'] . ( $options['css_class'] !== '' ? ' cn-button-custom ' . $options['css_class'] : '' ) . '" aria-label="' . esc_attr( $options['accept_text'] ) . '"' . ( $options['css_class'] == '' ? ' style="background-color: ' . esc_attr( $options['colors']['button'] ) . '"' : '' ) . '>' . esc_html( $options['accept_text'] ) . '</a>'
			. ( $options['refuse_opt'] === true ? '<a href="#" id="cn-refuse-cookie" data-cookie-set="refuse" class="cn-set-cookie ' . $options['button_class'] . ( $options['css_class'] !== '' ? ' cn-button-custom ' . $options['css_class'] : '' ) . '" aria-label="' . esc_attr( $options['refuse_text'] ) . '"' . ( $options['css_class'] == '' ? ' style="background-color: ' . esc_attr( $options['colors']['button'] ) . '"' : '' ) . '>' . esc_html( $options['refuse_text'] ) . '</a>' : '' )
			. ( $options['see_more'] === true && $options['link_position'] === 'banner' ? '<a href="' . ( $options['see_more_opt']['link_type'] === 'custom' ? esc_url( $options['see_more_opt']['link'] ) : get_permalink( $options['see_more_opt']['id'] ) ) . '" target="' . esc_attr( $options['link_target'] ) . '" id="cn-more-info" class="cn-more-info ' . $options['button_class'] . ( $options['css_class'] !== '' ? ' cn-button-custom ' . $options['css_class'] : '' ) . '" aria-label="' . esc_attr( $options['see_more_opt']['text'] ) . '"' . ( $options['css_class'] == '' ? ' style="background-color: ' . esc_attr( $options['colors']['button'] ) . '"' : '' ) . '>' . esc_html( $options['see_more_opt']['text'] ) . '</a>' : '' ) 
			. '</span><span id="cn-close-notice" data-cookie-set="accept" class="cn-close-icon" title="' . esc_attr( $options['refuse_text'] ) . '"></span>'
			. '</div>
			' . ( $options['refuse_opt'] === true && $options['revoke_cookies'] == true ? 
			'<div class="cookie-revoke-container" style="color: ' . esc_attr( $options['colors']['text'] ) . ';">'
			. ( ! empty( $options['revoke_message_text'] ) ? '<span id="cn-revoke-text" class="cn-text-container">' . $options['revoke_message_text'] . '</span>' : '' )
			. '<span id="cn-revoke-buttons" class="cn-buttons-container"><a href="#" class="cn-revoke-cookie ' . $options['button_class'] . ( $options['css_class'] !== '' ? ' cn-button-custom ' . $options['css_class'] : '' ) . '" aria-label="' . esc_attr( $options['revoke_text'] ) . '"' . ( $options['css_class'] == '' ? ' style="background-color: ' . esc_attr( $options['colors']['button'] ) . '"' : '' ) . '>' . esc_html( $options['revoke_text'] ) . '</a></span>
			</div>' : '' ) . '
		</div>
		<!-- / Cookie Notice plugin -->';

		echo apply_filters( 'cn_cookie_notice_output', $output, $options );
	}
	
	/**
	 * Load notice scripts and styles - frontend.
	 */
	public function wp_enqueue_notice_scripts() {
		wp_enqueue_script( 'cookie-notice-front', plugins_url( '../js/front' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', __FILE__ ), array(), Cookie_Notice()->defaults['version'], isset( Cookie_Notice()->options['general']['script_placement'] ) && Cookie_Notice()->options['general']['script_placement'] === 'footer' );

		wp_localize_script(
			'cookie-notice-front',
			'cnArgs',
			array(
				'ajaxUrl'				=> admin_url( 'admin-ajax.php' ),
				'nonce'					=> wp_create_nonce( 'cn_save_cases' ),
				'hideEffect'			=> Cookie_Notice()->options['general']['hide_effect'],
				'position'				=> Cookie_Notice()->options['general']['position'],
				'onScroll'				=> (int) Cookie_Notice()->options['general']['on_scroll'],
				'onScrollOffset'		=> (int) Cookie_Notice()->options['general']['on_scroll_offset'],
				'onClick'				=> (int) Cookie_Notice()->options['general']['on_click'],
				'cookieName'			=> 'cookie_notice_accepted',
				'cookieTime'			=> Cookie_Notice()->settings->times[Cookie_Notice()->options['general']['time']][1],
				'cookieTimeRejected'	=> Cookie_Notice()->settings->times[Cookie_Notice()->options['general']['time_rejected']][1],
				'cookiePath'			=> ( defined( 'COOKIEPATH' ) ? (string) COOKIEPATH : '' ),
				'cookieDomain'			=> ( defined( 'COOKIE_DOMAIN' ) ? (string) COOKIE_DOMAIN : '' ),
				'redirection'			=> (int) Cookie_Notice()->options['general']['redirection'],
				'cache'					=> (int) ( defined( 'WP_CACHE' ) && WP_CACHE ),
				'refuse'				=> (int) Cookie_Notice()->options['general']['refuse_opt'],
				'revokeCookies'			=> (int) Cookie_Notice()->options['general']['revoke_cookies'],
				'revokeCookiesOpt'		=> Cookie_Notice()->options['general']['revoke_cookies_opt'],
				'secure'				=> (int) is_ssl()
			)
		);

		wp_enqueue_style( 'cookie-notice-front', plugins_url( '../css/front' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.css', __FILE__ ) );
	}
	
	/**
	 * Make a JavaScript Asynchronous
	 * 
	 * @param string $tag The original enqueued script tag
	 * @param string $handle The registered unique name of the script
	 * @param string $src
	 * @return string $tag The modified script tag
	 */
	public function wp_enqueue_script_async( $tag, $handle, $src ) {
		if ( 'cookie-notice-front' === $handle ) {
			$tag = str_replace( '<script', '<script async', $tag );
		}
		return $tag;
	}

	/**
	 * Print non functional JavaScript in body.
	 *
	 * @return mixed
	 */
	public function wp_print_footer_scripts() {
		if ( Cookie_Notice()->cookies_accepted() ) {
			$scripts = apply_filters( 'cn_refuse_code_scripts_html', html_entity_decode( trim( wp_kses( Cookie_Notice()->options['general']['refuse_code'], Cookie_Notice()->get_allowed_html() ) ) ) );

			if ( ! empty( $scripts ) )
				echo $scripts;
		}
	}

	/**
	 * Print non functional JavaScript in header.
	 *
	 * @return mixed
	 */
	public function wp_print_header_scripts() {
		if ( Cookie_Notice()->cookies_accepted() ) {
			$scripts = apply_filters( 'cn_refuse_code_scripts_html', html_entity_decode( trim( wp_kses( Cookie_Notice()->options['general']['refuse_code_head'], Cookie_Notice()->get_allowed_html() ) ) ) );

			if ( ! empty( $scripts ) )
				echo $scripts;
		}	
	}
	
	/**
	 * Add new body classes.
	 *
	 * @param array $classes Body classes
	 * @return array
	 */
	public function change_body_class( $classes ) {
		if ( is_admin() )
			return $classes;

		if ( Cookie_Notice()->cookies_set() ) {
			$classes[] = 'cookies-set';

			if ( Cookie_Notice()->cookies_accepted() )
				$classes[] = 'cookies-accepted';
			else
				$classes[] = 'cookies-refused';
		} else
			$classes[] = 'cookies-not-set';

		return $classes;
	}

	/**
	 * Purge config cache.
	 */
	public function purge_cache() {
		delete_transient( 'cookie_notice_compliance_cache' );
	}
}