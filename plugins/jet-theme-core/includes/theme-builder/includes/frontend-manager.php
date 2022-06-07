<?php
namespace Jet_Theme_Core\Theme_Builder;
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Frontend_Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Jet_Theme_Core
	 */
	private static $instance = null;

	/**
	 * @var array
	 */
	public $all_site_conditions = [];

	/**
	 * @var array
	 */
	public $matched_page_template_layout = [];

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return Jet_Theme_Core
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * @return bool
	 */
	public function is_theme_builder_enabled() {
		return true;
	}

	/**
	 * @return array|false
	 */
	public function add_body_classes( $classes ) {

		$layout = $this->get_matched_page_template_layouts();

		if ( ! $layout ) {
			return $classes;
		}

		if ( ! empty( $layout ) ) {
			$classes[] = 'jet-theme-core--has-template';

			if ( $layout['header']['override'] ) {
				$classes[] = 'jet-theme-core--has-header';
			}

			if ( $layout['body']['override'] ) {
				$classes[] = 'jet-theme-core--has-body';
			}

			if ( $layout['footer']['override'] ) {
				$classes[] = 'jet-theme-core--has-footer';
			}
		}

		return $classes;
	}

	/**
	 * @param $template
	 *
	 * @return mixed
	 */
	public function frontend_override_template( $template ) {

		$layout = $this->get_matched_page_template_layouts();

		if ( empty( $layout ) ) {
			return $template;
		}

		$override_header   = $layout['header']['override'] && $layout['header']['id'] ? true : false;
		$override_body     = $layout['body']['override'] && $layout['body']['id'] ? true : false;
		$override_footer   = $layout['footer']['override'] && $layout['footer']['id'] ? true : false;
		$page_template     = locate_template( 'page.php' );

		if ( $override_header ) {
			// wp-version >= 5.2
			remove_action( 'wp_body_open', 'wp_admin_bar_render', 0 );

			add_action( 'get_header', [ $this, 'get_override_header' ] );
		}

		if ( $override_footer ) {
			add_action( 'get_footer', [ $this, 'get_override_footer' ] );
		}

		if ( $override_body ) {
			return jet_theme_core()->plugin_path( 'includes/theme-builder/templates/frontend-body-template.php' );
		}

		if ( $override_body && !is_home() ) {
			return $page_template;
		}

		return $template;
	}

	/**
	 * @param $name
	 */
	function get_override_header( $name ) {
		$this->get_override_partial( 'header', $name, 'wp_head' );
	}

	/**
	 * @param $name
	 */
	function get_override_footer( $name ) {
		$this->get_override_partial( 'footer', $name, 'wp_footer' );
	}

	/**
	 * @param $partial
	 * @param $name
	 * @param string $action
	 */
	function get_override_partial( $partial, $name, $action = '' ) {
		global $wp_filter;

		$jet_theme_core_theme_head = '';

		/**
		 * Slightly adjusted version of WordPress core code in order to mimic behavior.
		 *
		 * @link https://core.trac.wordpress.org/browser/tags/5.0.3/src/wp-includes/general-template.php#L33
		 */
		$templates = array();
		$name      = (string) $name;

		if ( '' !== $name ) {
			$templates[] = "{$partial}-{$name}.php";
		}
		$templates[] = "{$partial}.php";

		// Buffer and discard the original partial forcing a require_once so it doesn't load again later.
		$buffered = ob_start();

		if ( $buffered ) {
			$actions = [];

			if ( ! empty( $action ) ) {
				// Skip any partial-specific actions so they don't run twice.
				$actions = \Jet_Theme_Core\Utils::array_get( $wp_filter, $action, [] );

				unset( $wp_filter[ $action ] );
			}

			locate_template( $templates, true, true );
			$html = ob_get_clean();

			if ( 'wp_head' === $action ) {
				$jet_theme_core_theme_head = $this->extract_head( $html );
			}

			if ( ! empty( $action ) ) {
				// Restore skipped actions.
				$wp_filter[ $action ] = $actions;
			}
		}

		require_once jet_theme_core()->plugin_path( "includes/theme-builder/templates/frontend-{$partial}-template.php" );
	}

	/**
	 * Extract <head> tag contents.
	 *
	 * @since 4.0.8
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	function extract_head( $html ) {
		// We could use DOMDocument here to guarantee proper parsing but we need
		// the most performant solution since we cannot reliably cache the result.
		$head = array();
		preg_match( '/^[\s\S]*?<head[\s\S]*?>([\s\S]*?)<\/head>[\s\S]*$/i', $html, $head );

		return ! empty( $head[1] ) ? trim( $head[1] ) : '';
	}

	/**
	 * @param false $template_id
	 *
	 * @return false
	 */
	public function render_location( $template_id = false ) {

		if ( ! $template_id ) {
			return false;
		}

		$content_type = jet_theme_core()->templates->get_template_content_type( $template_id );
		$template_type = jet_theme_core()->templates->get_template_type( $template_id );
		$structure = jet_theme_core()->structures->get_structure( $template_type );

		if ( ! $structure ) {
			return false;
		}

		$location = $structure->location_name();

		switch ( $content_type ) {
			case 'elementor':
				$location_render = new \Jet_Theme_Core\Locations\Render\Elementor_Location_Render( [
					'template_id' => $template_id,
					'location'    => $location,
				] );
			break;
			default:
				$location_render = new \Jet_Theme_Core\Locations\Render\Block_Editor_Render( [
					'template_id' => $template_id,
					'location'    => $location,
				] );
			break;
		}

		$buffered = ob_start();

		$location_render->render();

		$location_html = ob_get_clean();

		do_action( "jet-theme-core/theme-builder/render/{$location}-location/before", $template_id, $content_type );

		switch ( $location ) {
			case 'header':
				$this->render_location_html( $location, 'header', $location_html );
				break;
			case 'footer':
				$this->render_location_html( $location, 'footer', $location_html );
				break;
			case 'page':
				$this->render_location_html( $location, 'main', $location_html );
				break;
			case 'single':
				$this->render_location_html( $location, 'main', $location_html );
				break;
			case 'archive':
				$this->render_location_html( $location, 'main', $location_html );
				break;
		}

		do_action( "jet-theme-core/theme-builder/render/{$location}-location/after", $template_id, $content_type );
	}

	/**
	 * @param false $location_html
	 *
	 * @return false
	 */
	public function render_location_html( $location = false, $container_tag = 'div', $location_html = false ) {

		if ( ! $location || ! $location_html ) {
			return false;
		}

		echo sprintf( '<%1$s id="jet-theme-core-%2$s" class="jet-theme-core-location jet-theme-core-location--%2$s-location"><div class="jet-theme-core-location__inner">%3$s</div></%1$s>',
			$container_tag,
			$location,
			$location_html
		);
	}

	/**
	 * @param $template_id
	 * @param $content_type
	 */
	public function render_document_open_wrapper( $template_id, $content_type ) {

		$layout = $this->get_matched_page_template_layouts();

		if ( ! $layout ) {
			return false;
		}

		$override_header = $this->is_layout_structure_override( 'header' );
		$override_footer = $this->is_layout_structure_override( 'footer' );

		if ( ! $override_header || ! $override_footer ) {
			return false;
		}

		do_action( 'jet-theme-core/theme-builder/render/open-document-wrapper/before', $template_id, $content_type );

		echo sprintf( '<div id="jet-theme-core-document" class="jet-theme-core-document jet-theme-core-document--%1$s-content-type"><div class="jet-theme-core-document__inner">',
			$content_type
		);

		do_action( 'jet-theme-core/theme-builder/render/open-document-wrapper/after', $template_id, $content_type );
	}

	/**
	 * @param $template_id
	 * @param $content_type
	 */
	public function render_document_close_wrapper( $template_id, $content_type ) {

		$layout = $this->get_matched_page_template_layouts();

		if ( ! $layout ) {
			return false;
		}

		$override_header = $this->is_layout_structure_override( 'header' );
		$override_footer = $this->is_layout_structure_override( 'footer' );

		if ( ! $override_header || ! $override_footer ) {
			return false;
		}

		do_action( 'jet-theme-core/theme-builder/render/close-document-wrapper/before', $template_id, $content_type );

		echo '</div></div>';

		do_action( 'jet-theme-core/theme-builder/render/close-document-wrapper/before', $template_id, $content_type );
	}

	/**
	 * @param false $single
	 *
	 * @return false
	 */
	public function get_matched_page_template_layouts( $single = false ) {

		if ( ! $this->is_theme_builder_enabled() ) {
			return false;
		}

		if ( ! empty( $this->matched_page_template_layout ) ) {
			return $this->matched_page_template_layout;
		}

		$matched_page_template_conditions = $this->get_matched_page_template_conditions();

		if ( ! $matched_page_template_conditions ) {
			return false;
		}

		uasort($matched_page_template_conditions, function ( $templateA, $templateB ) {
			$priorityA = $templateA[0]['priority'];
			$priorityB = $templateB[0]['priority'];

			if ( $priorityA == $priorityB ) {
				return 0;
			}

			return ( $priorityA < $priorityB ) ? -1 : 1;

		} );

		if ( ! $matched_page_template_conditions || empty( $matched_page_template_conditions ) ) {
			return false;
		}

		$page_template_id = array_key_first( $matched_page_template_conditions );

		$this->matched_page_template_layout = get_post_meta( $page_template_id, '_layout', true );

		return $this->matched_page_template_layout;
	}

	/**
	 * @param false $structure
	 *
	 * @return bool
	 */
	public function is_layout_structure_override( $structure = false ) {
		$layout = $this->get_matched_page_template_layouts();

		if ( ! $layout || ! isset( $layout[ $structure ] ) ) {
			return false;
		}

		return $layout[ $structure ]['override'] && $layout[ $structure ]['id'] ? true : false;
	}

	/**
	 * @param false $single
	 *
	 * @return array|false|mixed
	 */
	public function get_matched_page_template_conditions( $single = false ) {

		$all_site_conditions = get_option( jet_theme_core()->theme_builder->page_templates_manager->page_template_conditions_option_key, [] );

		$page_template_id_list = [];

		foreach ( $all_site_conditions as $page_template_id => $page_template_conditions ) {

			if ( empty( $page_template_conditions ) ) {
				continue;
			}

			$check_list = [];

			// for multi-language plugins
			$page_template_id = apply_filters( 'jet-theme-core/page-template-conditions/page-template-id', $page_template_id );

			$template_conditions = array_map( function( $condition ) use ( $page_template_id ) {

				$include = filter_var( $condition['include'] , FILTER_VALIDATE_BOOLEAN );

				if ( 'entire' === $condition['group'] ) {
					$match = 'entire' === $condition['group'] ? true : false;
					$condition['match'] = $match;

					return $condition;
				} else {
					$sub_group = $condition['subGroup'];

					$instance = jet_theme_core()->template_conditions_manager->get_condition( $sub_group );

					if ( ! $instance ) {
						$condition['match'] = true;

						return $condition;
					}

					$sub_group_value = isset( $condition['subGroupValue'] ) ? $condition['subGroupValue'] : '';

					$instance_check = call_user_func( array( $instance, 'check' ), $sub_group_value );

					$condition['match'] = $instance_check;

					return $condition;

				}

				return $condition;

			}, $page_template_conditions );

			$includes_matchs = [];
			$excludes_matchs = [];

			foreach ( $template_conditions as $key => $condition ) {
				$include = filter_var( $condition['include'], FILTER_VALIDATE_BOOLEAN );

				if ( $include ) {
					$includes_matchs[] = $condition['match'];
				} else {
					$excludes_matchs[] = $condition['match'];
				}
			}

			if ( in_array( true, $includes_matchs ) && ! in_array( true, $excludes_matchs ) ) {
				$page_template_id_list[ $page_template_id ] = $page_template_conditions;
			}
		}

		if ( ! empty( $page_template_id_list ) ) {

			if ( $single ) {
				$first_key = array_key_first( $page_template_id_list );

				return $page_template_id_list[ $first_key ];
			}

			return $page_template_id_list;
		}

		return false;
	}

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_filter( 'body_class', [ $this, 'add_body_classes' ], 9 );

		// Priority of 98 so it can be overridden by BFB.
		add_filter( 'template_include', [ $this, 'frontend_override_template' ], 98 );

		add_action( 'jet-theme-core/theme-builder/render/header-location/before', [ $this, 'render_document_open_wrapper' ], 10, 3 );

		add_action( 'jet-theme-core/theme-builder/render/footer-location/after', [ $this, 'render_document_close_wrapper' ], 10, 3 );

	}
}
