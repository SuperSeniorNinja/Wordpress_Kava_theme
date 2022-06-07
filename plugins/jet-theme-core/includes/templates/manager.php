<?php
namespace Jet_Theme_Core;
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

class Templates {

	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	public $post_type = 'jet-theme-core';

	/**
	 * Template meta cache key
	 *
	 * @var string
	 */
	public $cache_key = '_jet_template_cache';

	/**
	 * Template type arg for URL
	 * @var string
	 */
	public $type_tax = 'jet_library_type';

	/**
	 * Site conditions
	 * @var array
	 */
	private $conditions = array();

	/**
	 * @var bool
	 */
	public $export_import_manager = false;

	/**
	 * Templates post type slug
	 *
	 * @return string
	 */
	public function slug() {
		return $this->post_type;
	}

	/**
	 * Init comnonents
	 */
	public function init_components() {
		$this->export_import_manager = new Templates_Export_Import();
	}

	/**
	 * Register templates post type
	 *
	 * @return void
	 */
	public function register_post_types() {

		$args = array(
			'labels' => array(
				'name'               => esc_html__( 'Theme Parts', 'jet-theme-core' ),
				'singular_name'      => esc_html__( 'Template', 'jet-theme-core' ),
				'add_new'            => esc_html__( 'Add New', 'jet-theme-core' ),
				'add_new_item'       => esc_html__( 'Add New Template', 'jet-theme-core' ),
				'edit_item'          => esc_html__( 'Edit Template', 'jet-theme-core' ),
				'new_item'           => esc_html__( 'Add New Template', 'jet-theme-core' ),
				'view_item'          => esc_html__( 'View Template', 'jet-theme-core' ),
				'search_items'       => esc_html__( 'Search Template', 'jet-theme-core' ),
				'not_found'          => esc_html__( 'No Templates Found', 'jet-theme-core' ),
				'not_found_in_trash' => esc_html__( 'No Templates Found In Trash', 'jet-theme-core' ),
				'menu_name'          => esc_html__( 'My Library', 'jet-theme-core' ),
			),
			'public'              => true,
			'hierarchical'        => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_rest'        => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'exclude_from_search' => true,
			'capability_type'     => 'post',
			'rewrite'             => false,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'author', 'elementor', 'custom-fields' ),
		);

		register_post_type(
			$this->slug(),
			apply_filters( 'jet-theme-core/templates/post-type/args', $args )
		);

		$tax_args = array(
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_in_nav_menus' => false,
			'show_admin_column' => true,
			'query_var'         => is_admin(),
			'rewrite'           => false,
			'public'            => false,
			'label'             => __( 'Type', 'jet-theme-core' ),
		);

		register_taxonomy(
			$this->type_tax,
			$this->slug(),
			apply_filters( 'jet-theme-core/templates/type-tax/args', $tax_args )
		);
	}

	/**
	 * Set required post columns
	 *
	 * @param  array $columns
	 * @return array
	 */
	public function set_post_columns( $columns ) {

		unset( $columns['taxonomy-' . $this->type_tax ] );
		unset( $columns['date'] );

		$columns['type'] = __( 'Type', 'jet-theme-core' );

		if ( ! isset( $_GET[ $this->type_tax ] ) || ! in_array( $_GET[ $this->type_tax ], array( 'jet_page', 'jet_section' ) ) ) {
			$columns['conditions'] = __( 'Instances', 'jet-theme-core' );
		}

		$columns['date'] = __( 'Date', 'jet-theme-core' );

		return $columns;

	}

	/**
	 * Manage post columns content
	 *
	 * @return [type] [description]
	 */
	public function post_columns( $column, $template_id ) {

		$structure = jet_theme_core()->structures->get_post_structure( $template_id );

		if ( ! $structure ) {
			return false;
		}

		switch ( $column ) {

			case 'type':

				if ( $structure ) {

					$link = add_query_arg( array(
						$this->type_tax => $structure->get_id(),
					) );

					$template_content_type = $this->get_template_content_type( $template_id );
					$template_content_type_icons = $this->get_template_content_type_icons();
					$icon = isset( $template_content_type_icons[ $template_content_type ] ) ? $template_content_type_icons[ $template_content_type ] : '';

					printf( '<a href="%1$s" class="jet-template-library-column__template-type"><span class="type-icon">%3$s</span><span>%2$s</span></a>', $link, $structure->get_single_label(), $icon );

					do_action( "jet-theme-core/admin/template-library/column-type/{$template_content_type}-type/after", $template_id );
				}

				break;

			case 'conditions':

				$template_verbose_conditions = jet_theme_core()->template_conditions_manager->post_conditions_verbose( $template_id );

				echo sprintf( '<div class="jet-template-library__template-conditions" data-template-id="%1$s" data-structure-type="%2$s">%3$s</div>',
					$template_id,
					$structure->get_id(),
					$template_verbose_conditions
				);

				printf(
					'<a class="jet-template-library__template-edit-conditions" href="#" data-template-id="%1$s" data-structure-type="%2$s"><span class="dashicons dashicons-edit"></span><span>%3$s</span></a>',
					$template_id,
					$structure->get_id(),
					__( 'Edit Conditions', 'jet-theme-core' )
				);

				break;
		}
	}

	/**
	 * @param false $template_type
	 * @param string $content_type
	 * @param string $template_name
	 *
	 * @return array
	 */
	public function create_template( $template_type = false, $template_content_type = 'default', $template_name = '', $template_conditions = [] ) {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return [
				'type'          => 'error',
				'message'       => __( 'You don\'t have permissions to do this', 'jet-theme-core' ),
				'redirect'      => false,
				'newTemplateId' => false,
			];
		}

		$template_types = jet_theme_core()->structures->get_structures_for_post_type();

		if ( ! $template_type || ! array_key_exists( $template_type, $template_types ) ) {
			return [
				'type'          => 'error',
				'message'       => __( 'Incorrect template type. Please try again.', 'jet-theme-core' ),
				'redirect'      => false,
				'newTemplateId' => false,
			];
		}

		switch ( $template_content_type ) {
			case 'default':
				$meta_input = [
					'_jet_template_conditions'   => $template_conditions,
					'_jet_template_content_type' => $template_content_type,
					'_jet_template_type'         => $template_type,
				];
				break;
			case 'elementor':
				$documents = \Elementor\Plugin::instance()->documents;
				$doc_type  = $documents->get_document_type( $template_type );

				if ( ! $doc_type ) {
					return [
						'type'          => 'error',
						'message'       => __( 'Incorrect template type.', 'jet-theme-core' ),
						'redirect'      => false,
						'newTemplateId' => false,
					];
				}

				$meta_input = [
					'_elementor_edit_mode'       => 'builder',
					$doc_type::TYPE_META_KEY     => $template_type,
					'_jet_template_conditions'   => [],
					'_jet_template_content_type' => $template_content_type,
					'_jet_template_type'         => $template_type,
				];

				break;
		}

		$post_title = $template_name;

		if ( empty( $template_name ) ) {
			$post_title = ucwords( str_replace( '_', ' ', $template_type ) );
		}

		$post_data = array(
			'post_status' => 'publish',
			'post_title'  => $post_title,
			'post_type'   => $this->slug(),
			'tax_input'   => array(
				$this->type_tax => $template_type,
			),
			'meta_input' => $meta_input,
		);

		$template_id = wp_insert_post( $post_data, true );

		if ( empty( $template_name ) ) {
			$post_title = $post_title . ' #' . $template_id;

			wp_update_post( [
				'ID'         => $template_id,
				'post_title' => $post_title,
			] );
		}

		if ( $template_id ) {

			switch ( $template_content_type ) {
				case 'default':
					$redirect = get_edit_post_link( $template_id, '' );
					break;
				case 'elementor':
					$redirect = \Elementor\Plugin::$instance->documents->get( $template_id )->get_edit_url();
					break;
			}

			return [
				'type'          => 'success',
				'message'       => __( 'Template has been created', 'jet-theme-core' ),
				'redirect'      => $redirect,
				'newTemplateId' => $template_id,
			];
		} else {
			return [
				'type'          => 'error',
				'message'       => __( 'Server Error. Please try again later.', 'jet-theme-core' ),
				'redirect'      => false,
				'newTemplateId' => false,
			];
		}

	}

	/**
	 * @param $template_id
	 *
	 * @return mixed|string
	 */
	public function get_template_type( $template_id ) {
		$type = get_post_meta( $template_id, '_jet_template_type', true );

		return ! empty( $type ) ? $type : false;
	}

	/**
	 * @param $template_id
	 *
	 * @return string
	 */
	public function get_template_content_type( $template_id ) {
		$content_type = get_post_meta( $template_id, '_jet_template_content_type', true );

		return ! empty( $content_type ) ? $content_type : 'default';
	}

	/**
	 * @return array|array[]
	 */
	public function get_template_list() {

		$raw_templates = get_posts( [
			'post_type'           => $this->post_type,
			'ignore_sticky_posts' => true,
			'posts_per_page'      => -1,
			'suppress_filters'     => false,
			'meta_query'   => apply_filters( 'jet-theme-core/templates/meta-query-params', [] ),
		] );

		if ( empty( $raw_templates ) ) {
			return [];
		}

		$templates = array_map( function( $template_obj ) {
			$template_id = $template_obj->ID;

			$type = $this->get_template_type( $template_id );
			$content_type = $this->get_template_content_type( $template_id );
			$edit_link = $this->get_template_edit_link( $template_id, $content_type );
			$template_id = $template_obj->ID;
			$author_id = $template_obj->post_author;
			$author_data = get_userdata( $author_id );

			return [
				'id'          => $template_id,
				'title'       => $template_obj->post_title,
				'type'        => $type,
				'contentType' => $content_type,
				'editLink'    => $edit_link,
				'date'          => [
					'raw'         => $template_obj->post_date,
					'format'      => get_the_date( '', $template_id ),
					'lastModified' => $template_obj->post_modified,
				],
				'author'        => [
					'id'   => $author_id,
					'name' => $author_data->user_login,
				],
			];
		}, $raw_templates );

		return $templates;
	}

	/**
	 * @param false $template_id
	 * @param false $content_type
	 *
	 * @return false|mixed|string|null
	 */
	public function get_template_edit_link( $template_id = false, $content_type = false ) {

		if ( ! $template_id || ! $content_type ) {
			return false;
		}

		switch ( $content_type ) {
			case 'default':
				$edit_link = get_edit_post_link( $template_id, '' );
				break;
			case 'elementor':

				if ( \Jet_Theme_Core\Utils::has_elementor() ) {
					$edit_link = \Elementor\Plugin::$instance->documents->get( $template_id )->get_edit_url();
				} else {
					$edit_link = false;
				}

				break;
		}

		return $edit_link;
	}

	/**
	 * Disable metaboxes from Jet Templates
	 *
	 * @return void
	 */
	public function disable_metaboxes() {
		global $wp_meta_boxes;
		unset( $wp_meta_boxes[ $this->slug() ]['side']['core']['pageparentdiv'] );
	}

	/**
	 * Menu page
	 */
	public function add_templates_page() {
		add_submenu_page(
			'jet-dashboard',
			esc_html__( 'Theme Templates', 'jet-theme-core' ),
			esc_html__( 'Theme Templates', 'jet-theme-core' ),
			'edit_pages',
			'edit.php?post_type=' . $this->slug()
		);
	}

	/**
	 * Print library types tabs
	 *
	 * @return [type] [description]
	 */
	public function print_type_tabs( $edit_links ) {

		$tabs = jet_theme_core()->templates_api->get_library_types();
		$tabs = array_merge(
			array(
				'all' => esc_html__( 'All', 'jet-theme-core' ),
			),
			$tabs
		);

		$active_tab = isset( $_GET[ $this->type_tax ] ) ? $_GET[ $this->type_tax ] : 'all';
		$page_link  = admin_url( 'edit.php?post_type=' . $this->slug() );

		if ( ! array_key_exists( $active_tab, $tabs ) ) {
			$active_tab = 'all';
		}

		include jet_theme_core()->get_template( 'template-types-tabs.php' );

		return $edit_links;
	}

	/**
	 * Add an export link to the template library action links table list.
	 *
	 * @param array $actions
	 * @param object $post
	 *
	 * @return array
	 */
	public function post_row_actions( $actions, $post ) {

		if ( $this->post_type === $post->post_type ) {
			$actions['export-template'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				$this->get_export_link( $post->ID ),
				esc_html__( 'Export Template', 'jet-theme-core' )
			);
		}

		return $actions;
	}

	/**
	 * Get template export link.
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return string
	 */
	private function get_export_link( $template_id ) {
		return add_query_arg(
			array(
				'action'         => 'jet_theme_core_export_template',
				'template_id'    => $template_id,
			),
			esc_url( admin_url( 'admin-ajax.php' ) )
		);
	}

	/**
	 * Template type popup assets
	 *
	 * @return void
	 */
	public function template_type_form_assets() {

		$screen = get_current_screen();

		if ( $screen->id !== 'edit-' . $this->slug() ) {
			return;
		}

		$module_data = jet_theme_core()->module_loader->get_included_module_data( 'cherry-x-vue-ui.php' );
		$ui          = new \CX_Vue_UI( $module_data );
		$ui->enqueue_assets();

		wp_enqueue_style(
			'jet-theme-core-templates-library',
			jet_theme_core()->plugin_url( 'assets/css/templates-library.css' ),
			array(),
			jet_theme_core()->get_version()
		);

		wp_enqueue_script(
			'jet-theme-core-templates-library',
			jet_theme_core()->plugin_url( 'assets/js/templates-library.js' ), array(
				'jquery',
				'cx-vue-ui',
				'wp-api-fetch',
			),
			jet_theme_core()->get_version(),
			true
		);

		wp_localize_script(
			'jet-theme-core-templates-library',
			'JetThemeCoreTemplatesLibrary',
			array(
				'templateTypeOptions'          => jet_theme_core()->structures->get_template_type_options(),
				'templateContentTypeOptions'   => $this->get_template_content_type_options(),
				'createTemplatePath'           => 'jet-theme-core-api/v2/create-template',
				'getTemplateConditionsPath'    => 'jet-theme-core-api/v2/get-template-conditions',
				'updateTemplateConditionsPath' => 'jet-theme-core-api/v2/update-template-conditions',
				'rawConditionsData'            => jet_theme_core()->template_conditions_manager->get_conditions_raw_data(),
			)
		);

	}

	/**
	 * @return mixed|void
	 */
	public function get_template_content_type_options() {
		return apply_filters( 'jet-theme-core/templates/content-type-options', [
			[
				'label' => __( 'Block Editor', 'jet-theme-core' ),
				'value' => 'default',
			],
		] );
	}

	/**
	 * @return mixed|void
	 */
	public function get_template_content_type_icons() {
		return apply_filters( 'jet-theme-core/templates/content-type-icons', [
			'default' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M24 12C24 18.6274 18.6274 24 12 24C5.37258 24 0 18.6274 0 12C0 5.37258 5.37258 0 12 0C18.6274 0 24 5.37258 24 12ZM13.1079 0.927195L23.0728 10.8921C22.5528 5.63123 18.3688 1.44724 13.1079 0.927195ZM23.0728 13.1079C22.5528 18.3688 18.3688 22.5528 13.1079 23.0728L23.0728 13.1079ZM11.1313 23.0939L0.906137 12.8687C1.3272 18.3215 5.67854 22.6728 11.1313 23.0939ZM0.906137 11.1313C1.3272 5.67854 5.67854 1.3272 11.1313 0.906137L0.906137 11.1313ZM17.0621 10.5758C16.8846 10.4529 16.6433 10.5036 16.5226 10.6843C15.8199 11.7614 14.3294 11.8192 14.2513 11.8192H14.2158C12.3775 11.8192 11.6748 13.4168 11.6464 13.4819C11.5612 13.6843 11.6535 13.9156 11.8451 14.0023C11.8948 14.024 11.9516 14.0385 12.0013 14.0385C12.1503 14.0385 12.2923 13.9517 12.3562 13.7999L12.3571 13.798C12.3817 13.7446 12.8692 12.6853 14.088 12.6144V14.6602C14.0384 15.1011 13.8325 15.4481 13.4705 15.7084C13.0943 15.9758 12.5904 16.1132 11.9729 16.1132C11.2347 16.1132 10.6314 15.8529 10.1842 15.3397C9.72993 14.8264 9.5028 14.0963 9.5028 13.1566L9.5099 10.9011C9.54539 10.0698 9.76542 9.41198 10.1842 8.9421C10.6385 8.42885 11.2347 8.16861 11.9729 8.16861C12.5904 8.16861 13.0943 8.30596 13.4705 8.57343C13.8467 8.8409 14.0597 9.20957 14.0951 9.68668V9.73729C14.0951 10.012 14.3152 10.2361 14.5849 10.2361C14.8546 10.2361 15.0747 10.012 15.0747 9.73729V9.68668C15.0037 8.97102 14.6843 8.40717 14.1093 7.98066C13.5344 7.55415 12.8175 7.34451 11.9516 7.34451C10.9224 7.34451 10.0919 7.6915 9.46021 8.37825C8.86399 9.02162 8.55168 9.86741 8.51619 10.9084C8.51619 10.9445 8.51442 10.9806 8.51264 11.0168L8.51264 11.0168C8.51087 11.0529 8.50909 11.0891 8.50909 11.1252L8.51619 13.1566H8.50909C8.50909 14.306 8.8285 15.224 9.46021 15.9108C10.0919 16.5975 10.9224 16.9445 11.9516 16.9445C12.8175 16.9445 13.5344 16.7349 14.1093 16.3084C14.6346 15.918 14.9469 15.4048 15.0534 14.7686L15.0747 12.4987C15.7206 12.3397 16.6007 11.9783 17.1543 11.1252C17.2963 10.9445 17.2466 10.6987 17.0621 10.5758ZM12.1091 22.8374L1.27166 12L12.1091 1.16257L22.9465 12L12.1091 22.8374Z" fill="#23282D"/></svg>',
		] );
	}

	/**
	 * [print_vue_templates description]
	 * @return [type] [description]
	 */
	public function print_vue_templates() {

		$map = [
			'template-conditions-item',
			'template-conditions-manager',
		];

		foreach ( glob( jet_theme_core()->plugin_path( 'templates/admin/templates-library/' )  . '*.php' ) as $file ) {
			$name = basename( $file, '.php' );

			if ( ! in_array( $name,  $map )) {
				continue;
			}

			ob_start();
			include $file;
			printf( '<script type="x-template" id="tmpl-jet-theme-core-%1$s">%2$s</script>', $name, ob_get_clean() );
		}

	}

	/**
	 * Print template type form HTML
	 *
	 * @return void
	 */
	public function print_template_library() {
		$screen = get_current_screen();

		if ( $screen->id !== 'edit-' . $this->slug() ) {
			return;
		}

		include jet_theme_core()->get_template( 'admin/templates-library/templates-library.php' );
	}

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'jet-theme-core/init', array( $this, 'init_components' ) );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_templates_page' ), 22 );
			add_filter( 'views_edit-' . $this->post_type, array( $this, 'print_type_tabs' ) );
			add_filter( 'manage_' . $this->slug() . '_posts_columns', array( $this, 'set_post_columns' ) );
			add_action( 'manage_' . $this->slug() . '_posts_custom_column', array( $this, 'post_columns' ), 10, 2 );
			add_action( 'add_meta_boxes_' . $this->slug(), array( $this, 'disable_metaboxes' ), 9999 );
			add_filter( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 );

			add_action( 'admin_enqueue_scripts', array( $this, 'template_type_form_assets' ) );
			add_action( 'admin_footer', array( $this, 'print_vue_templates' ), 998 );
			add_action( 'admin_footer', array( $this, 'print_template_library' ), 999 );
		}

	}

}
