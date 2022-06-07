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

class Templates_Export_Import {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Jet_Theme_Core
	 */
	private static $instance = null;

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
	 * [popup_export_preset description]
	 * @return [type] [description]
	 */
	public function export_template_action_handler() {

		if ( isset( $_GET['action'] ) && 'jet_theme_core_export_template' === $_GET['action'] && isset( $_GET['template_id'] ) ) {
			$this->export_template( [ $_GET['template_id'] ] );
		}
	}

	/**
	 * [export_template description]
	 * @param  [type] $popup_id [description]
	 * @return [type]           [description]
	 */
	public function export_template( $template_ids = [] ) {

		if ( empty( $template_ids ) && ! is_array( $template_ids ) ) {
			wp_send_json_error( __( 'Server Error', 'jet-theme-core' ) );
		}

		$template_data_to_export = $this->prepare_template_data_to_export( $template_ids );

		$file_data = [
			'name' => 'jet-template-' . implode( '-', $template_ids ) . '-' . date( 'Y-m-d' ) . '.json',
			'data' => wp_json_encode( $template_data_to_export ),
		];

		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: public' );
		header( 'Content-Description: File Transfer' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="'. $file_data['name'] . '"' );
		header( 'Content-Transfer-Encoding: binary' );

		session_write_close();

		// Output file data.
		echo $file_data['data'];

		die();
	}

	/**
	 * [prepare_popup_export description]
	 * @param  [type] $popup_id [description]
	 * @return [type]           [description]
	 */
	public function prepare_template_data_to_export( $template_ids ) {

		$template_list = [];

		foreach ( $template_ids as $template_id ) {
			$content_type = jet_theme_core()->templates->get_template_content_type( $template_id );
			$type = jet_theme_core()->templates->get_template_type( $template_id );

			$editor_template_data = [];

			switch ( $content_type ) {
				case 'elementor':
					if ( \Jet_Theme_Core\Utils::has_elementor() ) {
						$editor_template_data = jet_theme_core()->elementor_manager->get_elementor_template_data_to_export( $template_id );
					}
					break;
				default:
					$editor_template_data = $this->get_template_data_to_export( $template_id );
					break;
			}

			$meta_fields = [
				'_jet_template_conditions'   => jet_theme_core()->template_conditions_manager->get_template_conditions( $template_id ),
				'_jet_template_content_type' => $content_type,
				'_jet_template_type'         => $type,
			];

			$template_data = [
				'templateName' => get_the_title( $template_id ),
				'type'         => $type,
				'contentType'  => $content_type,
				'content'      => $editor_template_data['content'],
				'metaFields'   => wp_parse_args( $meta_fields, $editor_template_data['meta_fields'] ),
			];

			$template_list[] = $template_data;
		}

		$export_data = [
			'version'       => JET_THEME_CORE_VERSION,
			'templateList'  => $template_list
		];

		return $export_data;
	}

	/**
	 * @param $template_id
	 *
	 * @return array
	 */
	public function get_template_data_to_export( $template_id ) {
		$template_data = [];
		$post_data = get_post( $template_id );
		$content = $post_data->post_content;
		$meta_fields = [];

		$template_data = [
			'content'    => $content,
			'meta_fields' => $meta_fields,
		];

		return $template_data;
	}

	/**
	 *
	 */
	public function template_import_action() {

		if ( ! current_user_can( 'import' ) ) {
			wp_send_json_error( __( 'You don\'t have permissions to do this', 'jet-theme-core' ) );
		}

		if ( empty( $_FILES['_file'] ) ) {
			wp_send_json_error( __( 'File not passed', 'jet-theme-core' ) );
		}

		$file = $_FILES['_file'];

		if ( 'application/json' !== $file['type'] ) {
			wp_send_json_error( __( 'Format not allowed', 'jet-theme-core' ) );
		}

		$content = file_get_contents( $file['tmp_name'] );
		$content = json_decode( $content, true );

		if ( ! $content ) {
			wp_send_json_error( __( 'No data found in file', 'jet-theme-core' ) );
		}

		if ( empty( $content['templateList'] ) ) {
			wp_send_json_error( [
				'message'       => __( 'Template List not found', 'jet-theme-core' ),
			] );
		}

		foreach ( $content['templateList'] as $templateData ) {
			$create_template_handler = $this->create_imported_template( $templateData );
		}

		wp_send_json_success( [
			'message' => __( 'Templates has been created', 'jet-theme-core' )
		] );
	}

	/**
	 * @param false $import_data
	 *
	 * @return array
	 */
	public function create_imported_template( $import_data = false ) {

		if ( ! $import_data ) {
			return [
				'type'          => 'error',
				'message'       => __( 'Server Error', 'jet-theme-core' ),
				'template_id'   => false,
			];
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return [
				'type'          => 'error',
				'message'       => __( 'You don\'t have permissions to do this', 'jet-theme-core' ),
				'template_id'   => false,
			];
		}

		$template_name = $import_data[ 'templateName' ];
		$type          = $import_data[ 'type' ];
		$content_type  = $import_data[ 'contentType' ];
		$content       = $import_data[ 'content' ];
		$meta_fields    = $import_data[ 'metaFields' ];
		$meta_input    = [];
		$template_args = [];

		$template_types = jet_theme_core()->structures->get_structures_for_post_type();

		if ( ! $type || ! array_key_exists( $type, $template_types ) ) {
			return [
				'type'          => 'error',
				'message'       => __( 'Incorrect template type. Please try again.', 'jet-theme-core' ),
				'template_id'   => false,
			];
		}

		switch ( $content_type ) {
			case 'default':

				if ( ! empty( $meta_fields ) ) {
					$meta_input = wp_parse_args( $meta_input, $meta_fields );
				}

				if ( ! empty( $content ) ) {
					$template_args = [
						'post_content' => $content,
					];
				}

				break;
			case 'elementor':

				if ( ! \Jet_Theme_Core\Utils::has_elementor() ) {
					return [
						'type'          => 'error',
						'message'       => __( 'Elementor plugin not active.', 'jet-theme-core' ),
						'template_id'   => false,
					];
				}

				$documents = \Elementor\Plugin::instance()->documents;
				$doc_type  = $documents->get_document_type( $type );

				if ( ! $doc_type ) {
					return [
						'type'          => 'error',
						'message'       => __( 'Incorrect template type.', 'jet-theme-core' ),
						'template_id'   => false,
					];
				}

				$elementor_content = $this->process_export_import_elementor_content( $content, 'on_import' );

				$meta_input = [
					'_elementor_edit_mode'   => 'builder',
					$doc_type::TYPE_META_KEY => $type,
					'_elementor_data'        => wp_slash( json_encode( $elementor_content ) ),
				];

				if ( ! empty( $meta_fields ) ) {
					$meta_input = wp_parse_args( $meta_input, $meta_fields );
				}

				break;
		}

		$new_template_data = wp_parse_args( [
			'post_status' => 'publish',
			'post_title'  => $template_name,
			'post_type'   => jet_theme_core()->templates->slug(),
			'tax_input'   => [
				jet_theme_core()->templates->type_tax => $type,
			],
			'meta_input' => $meta_input,
		], $template_args );

		$template_id = wp_insert_post( $new_template_data, true );

		return [
			'type'          => 'success',
			'message'       => __( 'Templates has been created', 'jet-theme-core' ),
			'template_id'   => $template_id,
		];
	}

	/**
	 * Process content for export/import.
	 *
	 * Process the content and all the inner elements, and prepare all the
	 * elements data for export/import.
	 *
	 * @since 1.5.0
	 * @access protected
	 *
	 * @param array  $content A set of elements.
	 * @param string $method  Accepts either `on_export` to export data or
	 *                        `on_import` to import data.
	 *
	 * @return mixed Processed content data.
	 */
	protected function process_export_import_elementor_content( $content, $method ) {
		return \ELementor\Plugin::$instance->db->iterate_data(
			$content, function( $element_data ) use ( $method ) {
			$element = \ELementor\Plugin::$instance->elements_manager->create_element_instance( $element_data );

			// If the widget/element isn't exist, like a plugin that creates a widget but deactivated
			if ( ! $element ) {
				return null;
			}

			return $this->process_element_export_import_content( $element, $method );
		}
		);
	}

	/**
	 * Process single element content for export/import.
	 *
	 * Process any given element and prepare the element data for export/import.
	 *
	 * @since 1.5.0
	 * @access protected
	 *
	 * @param Controls_Stack $element
	 * @param string         $method
	 *
	 * @return array Processed element data.
	 */
	protected function process_element_export_import_content( $element, $method ) {

		$element_data = $element->get_data();

		if ( method_exists( $element, $method ) ) {
			// TODO: Use the internal element data without parameters.
			$element_data = $element->{$method}( $element_data );
		}

		foreach ( $element->get_controls() as $control ) {
			$control_class = \ELementor\Plugin::$instance->controls_manager->get_control( $control['type'] );

			// If the control isn't exist, like a plugin that creates the control but deactivated.
			if ( ! $control_class ) {
				return $element_data;
			}

			if ( method_exists( $control_class, $method ) ) {
				$element_data['settings'][ $control['name'] ] = $control_class->{$method}( $element->get_settings( $control['name'] ), $control );
			}
		}

		return $element_data;
	}

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		add_action( 'admin_init', [ $this, 'export_template_action_handler' ] );
		add_action( 'wp_ajax_jet_theme_core_import_template', array( $this, 'template_import_action' ) );

		//var_dump( get_post_meta( 3494 ) );
	}

}
