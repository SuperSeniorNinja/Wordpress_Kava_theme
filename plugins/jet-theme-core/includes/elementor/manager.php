<?php
namespace Jet_Theme_Core\Elementor;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Elementor Integration Class class
 */
class Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * @var null
	 */
	public $location_manager = null;

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register new controls
	 */
	public function add_controls( $controls_manager ) {

		$controls = array(
			'jet_search' => 'Jet_Control_Search',
		);

		foreach ( $controls as $control_id => $class_name ) {
			if ( $this->include_control( $class_name, false ) ) {
				$class_name = '\\Elementor\\' . $class_name;
				$controls_manager->register_control( $control_id, new $class_name() );
			}
		}
	}

	/**
	 * Include control file by class name.
	 *
	 * @param  [type] $class_name [description]
	 * @return [type]             [description]
	 */
	public function include_control( $class_name, $grouped = false ) {

		$filename = sprintf(
			'includes/elementor/controls/%2$s%1$s.php',
			str_replace( 'jet_control_', '', strtolower( $class_name ) ),
			( true === $grouped ? 'groups/' : '' )
		);

		if ( ! file_exists( jet_theme_core()->plugin_path( $filename ) ) ) {
			return false;
		}

		require jet_theme_core()->plugin_path( $filename );

		return true;
	}

	/**
	 * Register apropriate Document Types for existing structures
	 *
	 * @return void
	 */
	public function register_document_types_for_structures( $documents_manager ) {

		// For compatibility with Elementor 2.7.0
		require jet_theme_core()->plugin_path( 'includes/elementor/document-types/not-supported.php' );
		$documents_manager->register_document_type( 'jet-theme-core-not-supported', 'Jet_Theme_Core_Not_Supported' );

		require jet_theme_core()->plugin_path( 'includes/elementor/document-types/base.php' );

		$structures = jet_theme_core()->structures->get_structures();

		foreach ( $structures as $id => $structure ) {
			$document_type = $structure->get_elementor_document_type();

			require $document_type['file'];
			$documents_manager->register_document_type( $id, $document_type['class'] );
		}
	}

	/**
	 * Switch to specific preview query
	 *
	 * @return void
	 */
	public function switch_to_preview_query() {
		$current_post_id = get_the_ID();
		$document        = \Elementor\Plugin::instance()->documents->get_doc_or_auto_save( $current_post_id );

		if ( ! is_object( $document ) || ! method_exists( $document, 'get_preview_as_query_args' ) ) {
			return;
		}

		$new_query_vars = $document->get_preview_as_query_args();

		if ( empty( $new_query_vars ) ) {
			return;
		}

		\Elementor\Plugin::instance()->db->switch_to_query( $new_query_vars );
	}

	/**
	 * Restore default query
	 *
	 * @return void
	 */
	public function restore_current_query() {
		\Elementor\Plugin::instance()->db->restore_current_query();
	}

	/**
	 * Enqueue elemnetor editor scripts
	 *
	 * @return void
	 */
	public function editor_scripts() {

		wp_enqueue_script(
			'jet-theme-core-editor',
			jet_theme_core()->plugin_url( 'includes/elementor/assets/js/editor.js' ),
			array( 'jquery', 'underscore', 'backbone-marionette' ),
			jet_theme_core()->get_version(),
			true
		);

		$icon   = apply_filters( 'jet-theme-core/library-button/icon', '<svg enable-background="new 0 0 483.719 483.719" version="1.1" viewBox="0 0 483.719 483.719" xml:space="preserve" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="m441.76 451l-153-194.43c-6.461 4.764-14.325 7.427-22.477 7.427-3.488 0-6.952-0.481-10.326-1.438l-17.934-5.081 171.96 218.53c3.993 5.072 9.917 7.715 15.902 7.715 4.38 0 8.782-1.414 12.499-4.327 8.762-6.913 10.283-19.62 3.376-28.393z"/><path fill="currentColor" d="m282.49 164.19l1.961 4.943 6.3 15.899 51.397-2.399c4.948-0.244 9.035-4.14 9.511-8.917 0.554-4.912-2.619-9.582-7.395-10.852l-51.615-13.836-9.511 14.207-0.648 0.955z"/><path fill="currentColor" d="m207.97 248.96l-1.624-0.458-4.343 3.603-13.177 10.907 19.063 50.513c1.488 3.872 5.264 6.469 9.398 6.469l2.054-0.213c4.885-1.011 8.25-5.362 7.997-10.377l-2.926-55.781-16.442-4.663z"/><path fill="currentColor" d="m223.62 62.903l2.208 2.817 2.59-0.165 17.061-1.091 14.366-52.222c2.915-10.599-11.341-17.028-17.354-7.826l-29.438 45.049 10.567 13.438z"/><path fill="currentColor" d="m102.8 206.58l-0.173-4.449-2.623-1.665-14.458-9.152-44.231 35.364c-8.574 6.856-0.844 20.421 9.425 16.536l52.69-19.93-0.63-16.704z"/><path fill="currentColor" d="m93.34 97.02l16.047-5.898 5.294-1.946 0.297-1.183 4.235-16.578-47.447-31.203c-9.18-6.036-19.688 5.531-12.8 14.09l34.374 42.718z"/><path fill="currentColor" d="m264.77 231.43c0.508 0.143 1.01 0.214 1.516 0.214 1.551 0 3.049-0.648 4.105-1.824 1.424-1.575 1.818-3.8 1.044-5.767l-25.289-63.743 38.167-56.967c1.178-1.753 1.248-4.019 0.202-5.852-0.995-1.723-2.814-2.779-4.787-2.779-0.122 0-0.253 5e-3 -0.376 0.016l-68.447 4.358-42.376-53.911c-1.075-1.348-2.683-2.116-4.379-2.116-0.376 0-0.753 0.041-1.138 0.119-2.054 0.442-3.689 2.006-4.215 4.054l-16.993 66.432-64.373 23.665c-1.985 0.726-3.376 2.517-3.599 4.619-0.234 2.093 0.762 4.136 2.542 5.268l57.94 36.694 2.602 68.527c0.081 2.109 1.349 3.988 3.273 4.85 0.728 0.326 1.503 0.483 2.257 0.483 1.281 0 2.549-0.436 3.555-1.279l52.791-43.748 65.978 18.687z"/></svg>' );
		$button = jet_theme_core()->config->get( 'library_button' );
		$license = \Jet_Theme_Core\Utils::get_theme_core_license();
		$link    = sprintf(
			'<a class="template-library-activate-license" href="%1$s" target="_blank">%2$s %3$s</a>',
			\Jet_Theme_Core\Utils::active_license_link(),
			'<i class="fa fa-external-link" aria-hidden="true"></i>',
			__( 'Activate license', 'jet-theme-core' )
		);

		wp_localize_script( 'jet-theme-core-editor', 'JetThemeCoreData', apply_filters(
			'jet-theme-core/assets/editor/localize',
			array(
				'libraryButton' => ( false !== $button ) ? $icon . $button : false,
				'modalRegions'  => array(
					'modalHeader'  => '.dialog-header',
					'modalContent' => '.dialog-message',
				),
				'license'       => array(
					'activated' => true,
					'link'      => '',
				),
			)
		) );
	}

	/**
	 * Enqueue elemnetor editor-related styles
	 *
	 * @return void
	 */
	public function editor_styles() {
		wp_enqueue_style(
			'jet-theme-core-editor',
			jet_theme_core()->plugin_url( 'includes/elementor/assets/css/editor.css' ),
			array(),
			jet_theme_core()->get_version()
		);
	}

	/**
	 * Prints editor templates
	 *
	 * @return void
	 */
	public function print_templates() {

		foreach ( glob( jet_theme_core()->plugin_path( 'templates/editor/*.php' ) ) as $file ) {
			$name = basename( $file, '.php' );
			ob_start();
			include $file;
			printf( '<script type="text/html" id="tmpl-jet-%1$s">%2$s</script>', $name, ob_get_clean() );
		}
	}

	/**
	 * Load preview assets
	 *
	 * @return void
	 */
	public function preview_styles() {

		wp_enqueue_style(
			'jet-theme-core-preview',
			jet_theme_core()->plugin_url( 'includes/elementor/assets/css/preview.css' ),
			array(),
			jet_theme_core()->get_version()
		);
	}

	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	public function modify_content_type_options( $options ) {
		$options[] = [
			'label' => __( 'Elementor', 'jet-theme-core' ),
			'value' => 'elementor',
		];

		return $options;
	}

	/**
	 * @param $icons
	 *
	 * @return mixed
	 */
	public function modify_content_type_icons( $icons ) {
		$icons['elementor'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.6 3H3V21H6.6V3ZM21 13.7964V10.1964L10.2 10.1964V13.7964H21ZM21 3V6.6L10.2 6.6V3H21ZM21 20.9921V17.3921H10.2V20.9921H21Z" fill="#23282D"/></svg>';

		return $icons;
	}

	/**
	 * @param $params
	 *
	 * @return mixed
	 */
	public function modify_get_templates_meta_query_params( $meta_query_params ) {
		$meta_query_params[] = [
			'key'     => '_jet_template_content_type',
			'value'   => 'elementor',
		];

		return $meta_query_params;
	}

	/**
	 * @param $template_id
	 */
	public function modify_template_library_column_type( $template_id ) {

		if ( ! \Jet_Theme_Core\Utils::has_elementor() ) {
			$warning_icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.47 21H19.53C21.07 21 22.03 19.33 21.26 18L13.73 4.99C12.96 3.66 11.04 3.66 10.27 4.99L2.74 18C1.97 19.33 2.93 21 4.47 21ZM12 14C11.45 14 11 13.55 11 13V11C11 10.45 11.45 10 12 10C12.55 10 13 10.45 13 11V13C13 13.55 12.55 14 12 14ZM13 18H11V16H13V18Z" fill="#fcb92c"/></svg>';
			printf( '<div class="jet-template-library-column__template-not-editable">%1$s<span>%2$s</span></div>', $warning_icon, __( 'Template cannot be used, please install required content editor', 'jet-theme-core' ) );
		}
	}

	/**
	 * Editor templates.
	 *
	 * @param  string $template Current template name.
	 * @return string
	 */
	public function set_editor_template( $template ) {

		$found = false;

		if ( is_singular( jet_theme_core()->templates->slug() ) ) {
			$found    = true;
			$template = jet_theme_core()->plugin_path( 'templates/blank.php' );
		}

		if ( $found ) {
			do_action( 'jet-theme-core/post-type/editor-template/found' );
		}

		return $template;

	}

	/**
	 * Add opening wrapper if defined in manifes
	 *
	 * @return void
	 */
	public function editor_template_before() {
		$editor = jet_theme_core()->config->get( 'editor' );

		if ( isset( $editor['template_before'] ) ) {
			echo $editor['template_before'];
		}
	}

	/**
	 * Add closing wrapper if defined in manifes
	 *
	 * @return void
	 */
	public function editor_template_after() {
		$editor = jet_theme_core()->config->get( 'editor' );

		if ( isset( $editor['template_after'] ) ) {
			echo $editor['template_after'];
		}
	}

	/**
	 * @param $template_id
	 *
	 * @return mixed
	 */
	public function get_elementor_template_data_to_export( $template_id ) {

		$template_data = [];

		$content = false;

		$db = \Elementor\Plugin::$instance->db;

		$content = $db->get_builder( $template_id );

		$meta_fields = [];

		//_elementor_data
		//_elementor_page_assets
		//_elementor_css

		$template_data = [
			'content'    => $content,
			'meta_fields' => $meta_fields,
		];

		return $template_data;
	}

	/**
	 * Initalize integration hooks
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'jet-theme-core/templates/content-type-icons', array( $this, 'modify_content_type_icons' ), 10, 2 );
		add_action( 'jet-theme-core/admin/template-library/column-type/elementor-type/after', array( $this, 'modify_template_library_column_type' ), 10, 2 );

		if ( ! \Jet_Theme_Core\Utils::has_elementor() ) {
			return false;
		}

		add_action( 'elementor/controls/controls_registered', array( $this, 'add_controls' ), 10 );
		add_action( 'elementor/documents/register', array( $this, 'register_document_types_for_structures' ) );
		add_action( 'elementor/dynamic_tags/before_render', array( $this, 'switch_to_preview_query' ) );
		add_action( 'elementor/dynamic_tags/after_render', array( $this, 'restore_current_query' ) );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'editor_scripts' ), 0 );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'editor_styles' ) );
		add_action( 'elementor/editor/footer', array( $this, 'print_templates' ) );
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'preview_styles' ) );
		add_filter( 'jet-theme-core/templates/content-type-options', array( $this, 'modify_content_type_options' ), 10, 2 );
		//add_filter( 'jet-theme-core/templates/meta-query-params', array( $this, 'modify_get_templates_meta_query_params' ), 10, 2 );

		if ( ! empty( $_GET['elementor-preview'] ) ) {
			add_action( 'template_include', array( $this, 'set_editor_template' ), 9999 );
			add_action( 'jet-theme-core/blank-page/before-content', array( $this, 'editor_template_before' ) );
			add_action( 'jet-theme-core/blank-page/after-content', array( $this, 'editor_template_after' ) );
		}

		$this->location_manager = new Locations();
	}

}
