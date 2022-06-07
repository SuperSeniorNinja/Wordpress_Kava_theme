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

class Page_Templates_Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Jet_Theme_Core
	 */
	private static $instance = null;

	/**
	 * @var string
	 */
	public $page_template_conditions_option_key = 'jet_page_template_conditions';

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
	 * Templates post type slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return jet_theme_core()->theme_builder->post_type;
	}

	/**
	 * @param false $template_type
	 * @param string $content_type
	 * @param string $template_name
	 *
	 * @return array
	 */
	public function create_page_template( $template_name = '', $template_conditions = [], $template_layout = [], $template_type = 'unassigned' ) {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return [
				'type'     => 'error',
				'message'  => __( 'You don\'t have permissions to do this', 'jet-theme-core' ),
			];
		}

		$default_layout = [
			'header' => [
				'id'       => false,
				'enabled'  => true,
				'override' => true,
			],
			'body' => [
				'id'       => false,
				'enabled'  => true,
				'override' => true,
			],
			'footer' => [
				'id'       => false,
				'enabled'  => true,
				'override' => true,
			],
		];

		$template_layout = wp_parse_args( $template_layout, $default_layout );

		$meta_input = [
			'_conditions'  => $template_conditions,
			'_layout'      => $template_layout,
			'_type'        => $template_type,
		];

		$post_title = $template_name;

		$post_data = array(
			'post_status' => 'publish',
			'post_title'  => $post_title,
			'post_type'   => $this->get_slug(),
			'meta_input'  => $meta_input,
		);

		$template_id = wp_insert_post( $post_data, true );

		if ( empty( $template_name ) ) {
			$post_title = $post_title . 'Page Template #' . $template_id;

			wp_update_post( [
				'ID'         => $template_id,
				'post_title' => $post_title,
			] );
		}

		if ( $template_id ) {

			// Update all site page template conditions.
			$page_template_conditions = get_option( $this->page_template_conditions_option_key, [] );

			if ( ! isset( $page_template_conditions[ $template_id ] ) ) {
				$page_template_conditions[ $template_id ] = [];
				update_option( $this->page_template_conditions_option_key, $page_template_conditions, true );
			}

			$page_template_list = $this->get_page_template_list();

			return [
				'type'     => 'success',
				'message'  => __( 'Page template has been created', 'jet-theme-core' ),
				'data' => [
					'newTemplateId' => $template_id,
					'list'          => $page_template_list,
				],
			];
		} else {
			return [
				'type'     => 'error',
				'message'  => __( 'Server Error. Please try again later.', 'jet-theme-core' ),
				'data' => [],
			];
		}
	}

	/**
	 * @param $template_id
	 *
	 * @return array
	 */
	public function delete_page_template( $template_id ) {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return [
				'type'     => 'error',
				'message'  => __( 'You don\'t have permissions to do this', 'jet-theme-core' ),
			];
		}

		if ( ! $template_id ) {
			return [
				'type'     => 'error',
				'message'  => __( 'Invalid template id', 'jet-theme-core' ),
			];
		}

		$delete = wp_delete_post( $template_id, true );

		if ( $delete ) {

			// Update all site page template conditions.
			$page_template_conditions = get_option( $this->page_template_conditions_option_key, [] );

			if ( isset( $page_template_conditions[ $template_id ] ) ) {
				unset( $page_template_conditions[ $template_id ] );
				update_option( $this->page_template_conditions_option_key, $page_template_conditions, true );
			}

			$page_template_list = $this->get_page_template_list();

			return [
				'type'     => 'success',
				'message'  => __( 'Page template has been deleted', 'jet-theme-core' ),
				'data' => [
					'list' => $page_template_list,
				],
			];
		} else {
			return [
				'type'     => 'error',
				'message'  => __( 'Server Error. Please try again later.', 'jet-theme-core' ),
				'data' => [],
			];
		}
	}

	/**
	 * @param $template_id
	 *
	 * @return array
	 */
	public function copy_page_template( $template_id ) {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return [
				'type'     => 'error',
				'message'  => __( 'You don\'t have permissions to do this', 'jet-theme-core' ),
			];
		}

		if ( ! $template_id ) {
			return [
				'type'     => 'error',
				'message'  => __( 'Invalid template id', 'jet-theme-core' ),
			];
		}

		$new_template_name   = get_the_title( $template_id ) . ' copy';

		$template_data = get_post( $template_id );

		$new_template    = array(
			'post_title'  => $new_template_name,
			'post_status' => 'publish',
			'post_type'   => $template_data->post_type,
			'post_author' => get_current_user_id(),
		);

		// Create new page template
		$new_template_id = wp_insert_post( $new_template );

		// Copy page template metadata
		$data = get_post_custom( $template_id );

		foreach ( $data as $key => $values ) {
			foreach ( $values as $value) {
				add_post_meta( $new_template_id, $key, maybe_unserialize( $value ) );
			}
		}

		if ( $new_template_id ) {

			// Update all site page template conditions.
			$page_template_conditions = get_option( $this->page_template_conditions_option_key, [] );

			if ( isset( $page_template_conditions[ $template_id ] ) ) {
				$page_template_conditions[ $new_template_id ] = $this->get_page_template_conditions( $template_id );

				update_option( $this->page_template_conditions_option_key, $page_template_conditions, true );
			}

			$page_template_list = $this->get_page_template_list();

			return [
				'type'     => 'success',
				'message'  => __( 'Page template has been deleted', 'jet-theme-core' ),
				'data' => [
					'list' => $page_template_list,
				],
			];
		} else {
			return [
				'type'     => 'error',
				'message'  => __( 'Server Error. Please try again later.', 'jet-theme-core' ),
				'data' => [],
			];
		}
	}

	/**
	 * @return array
	 */
	public function get_page_template_list( $template_name = false, $order_by = false ) {

		$params = [
			'post_type'           => $this->get_slug(),
			'ignore_sticky_posts' => true,
			'posts_per_page'      => -1,
			'suppress_filters'     => false,
		];

		if ( $template_name ) {
			$params['s'] = $template_name;
		}

		$page_templates_data = get_posts( $params );

		$page_templates = [];

		if ( ! empty( $page_templates_data ) ) {
			foreach ( $page_templates_data as $template ) {
				$template_id = $template->ID;
				$author_id = $template->post_author;
				$author_data = get_userdata( $author_id );

				$page_templates[] = [
					'id'            => $template_id,
					'templateName'  => $template->post_title,
					'date'          => [
						'raw'         => $template->post_date,
						'format'      => get_the_date( '', $template_id ),
						'lastModified' => $template->post_modified,
					],
					'author'        => [
						'id'   => $author_id,
						'name' => $author_data->user_login,
					],
					'type'          => get_post_meta( $template_id, '_type', true ),
					'conditions'    => get_post_meta( $template_id, '_conditions', true ),
					'layout'        => $this->get_page_template_layout( $template_id ),
					'exportLink'    => \Jet_Theme_Core\Theme_Builder\Page_Templates_Export_Import::get_instance()->get_page_template_export_link( $template_id )
				];
			}
		}

		return $page_templates;
	}

	/**
	 * @param $template_id
	 *
	 * @return mixed
	 */
	public function get_page_template_layout( $template_id ) {
		$layout = get_post_meta( $template_id, '_layout', true );

		if ( ! empty( $layout ) ) {
			$is_modify = false;

			foreach ( $layout as $structure => $structure_data ) {

				if ( ! empty( $structure_data['id'] ) && 'publish' !== get_post_status( $structure_data['id'] ) ) {
					$layout[ $structure ]['id'] = false;
					$is_modify = true;
				}
			}

			if ( $is_modify ) {
				$this->update_page_template_layout( $template_id, $layout );
			}
		}

		return $layout;
	}

	/**
	 * @param false $page_template_id
	 * @param false $structure
	 * @param array $structure_data
	 *
	 * @return array
	 */
	public function update_page_template_data( $id = false,  $data = false ) {

		if ( ! $id || empty( $data ) ) {
			return [
				'type'     => 'error',
				'message'  => __( 'Server Error', 'jet-theme-core' ),
				'data' => [],
			];
		}

		if ( isset( $data['conditions'] ) ) {
			$this->update_page_template_conditions( $id, $data['conditions'] );
		}

		if ( isset( $data['layout'] ) ) {
			$this->update_page_template_layout( $id, $data['layout'] );
		}

		if ( isset( $data['type'] ) ) {
			$this->update_page_template_type( $id, $data['type'] );
		}

		if ( isset( $data['templateName'] ) ) {
			wp_update_post( [
				'ID'         => $id,
				'post_title' => $data['templateName'],
			] );
		}

		return [
			'type'     => 'success',
			'message'  => __( 'Page template layout updated', 'jet-theme-core' ),
			'data' => [],
		];
	}

	/**
	 * @param false $id
	 * @param array $layout
	 */
	public function update_page_template_layout( $id = false, $layout = [] ) {

		if ( ! $id || empty( $layout ) ) {
			return false;
		}

		return update_post_meta( $id, '_layout', $layout );
	}

	/**
	 * @param false $id
	 * @param array $layout
	 */
	public function update_page_template_type( $id = false, $type = false ) {

		if ( ! $id || ! $type ) {
			return false;
		}

		return update_post_meta( $id, '_type', $type );
	}

	/**
	 * @param false $id
	 * @param false $data
	 *
	 * @return array
	 */
	public function update_template_data( $id = false,  $data = false ) {

		if ( ! $id || empty( $data ) ) {
			return [
				'type'     => 'error',
				'message'  => __( 'Server Error', 'jet-theme-core' ),
				'data' => [],
			];
		}

		if ( isset( $data['title'] ) ) {

			wp_update_post( [
				'ID'         => $id,
				'post_title' => $data['title'],
			] );
		}

		return [
			'type'     => 'success',
			'message'  => __( 'Template layout updated', 'jet-theme-core' ),
			'data' => [],
		];
	}

	/**
	 * @param false $page_template_id
	 *
	 * @return array|mixed
	 */
	public function get_page_template_conditions( $page_template_id = false ) {

		if ( ! $page_template_id ) {
			return [];
		}

		$page_template_conditions = get_post_meta( $page_template_id, '_conditions', true );

		return ! empty( $page_template_conditions ) ? $page_template_conditions : [];
	}

	/**
	 * @param false $page_template_id
	 * @param array $conditions
	 */
	public function update_page_template_conditions( $page_template_id = false, $conditions = [] ) {

		update_post_meta( $page_template_id, '_conditions', $conditions );

		// Update all site page template conditions.
		$page_template_conditions = get_option( $this->page_template_conditions_option_key, [] );

		$page_template_conditions[ $page_template_id ] = $conditions;

		update_option( $this->page_template_conditions_option_key, $page_template_conditions, true );
	}

	/**
	 * @param $page_template_id
	 *
	 * @return string
	 */
	public function get_page_template_export_link( $page_template_id ) {
		return add_query_arg(
			[
				'action'           => 'jet_theme_core_export_page_template',
				'page_template_id' => $page_template_id,
			],
			admin_url( 'admin-ajax.php' )
		);
	}


	/**
	 * Constructor for the class
	 */
	public function __construct() {}

}
