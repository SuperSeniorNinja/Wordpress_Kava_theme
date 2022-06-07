<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * @var array
	 */
	private $_conditions    = [];

	/**
	 * @var string
	 */
	public  $conditions_key = 'jet_site_conditions';

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * [load_files description]
	 * @return [type] [description]
	 */
	public function load_files() {}

	/**
	 * [register_conditions description]
	 * @return [type] [description]
	 */
	public function register_conditions() {

		$base_path = jet_theme_core()->plugin_path( 'includes/template-conditions/conditions/' );

		require $base_path . 'base.php';

		$default_conditions = array(

			'\Jet_Theme_Core\Template_Conditions\Entire'         => $base_path . 'entire.php',

			// Singular conditions
			'\Jet_Theme_Core\Template_Conditions\Front_Page'         => $base_path . 'singular-front-page.php',
			'\Jet_Theme_Core\Template_Conditions\Page'               => $base_path . 'singular-page.php',
			'\Jet_Theme_Core\Template_Conditions\Page_Child'         => $base_path . 'singular-page-child.php',
			'\Jet_Theme_Core\Template_Conditions\Page_Template'      => $base_path . 'singular-page-template.php',
			'\Jet_Theme_Core\Template_Conditions\Page_404'           => $base_path . 'singular-404.php',
			'\Jet_Theme_Core\Template_Conditions\Post'               => $base_path . 'singular-post.php',
			'\Jet_Theme_Core\Template_Conditions\Post_From_Category' => $base_path . 'singular-post-from-cat.php',
			'\Jet_Theme_Core\Template_Conditions\Post_From_Tag'      => $base_path . 'singular-post-from-tag.php',

			// Archive conditions
			'\Jet_Theme_Core\Template_Conditions\Archive_All'        => $base_path . 'archive-all.php',
			'\Jet_Theme_Core\Template_Conditions\Archive_Category'   => $base_path . 'archive-category.php',
			'\Jet_Theme_Core\Template_Conditions\Archive_Tag'        => $base_path . 'archive-tag.php',
			'\Jet_Theme_Core\Template_Conditions\Archive_Search'     => $base_path . 'archive-search-results.php',

			// Custom Post Type
			'\Jet_Theme_Core\Template_Conditions\CPT_Type'           => $base_path . 'cpt-type.php',
			'\Jet_Theme_Core\Template_Conditions\CPT_Archive'        => $base_path . 'cpt-archive.php',

			// Advanced
			'\Jet_Theme_Core\Template_Conditions\Url_Param'          => $base_path . 'advanced-url-param.php',
			'\Jet_Theme_Core\Template_Conditions\Device'             => $base_path . 'advanced-device.php',
			'\Jet_Theme_Core\Template_Conditions\Roles'              => $base_path . 'advanced-roles.php',
		);

		foreach ( $default_conditions as $class => $file ) {
			require $file;

			$instance = new $class;

			$this->_conditions[ $instance->get_id() ] = $instance;
		}

		/**
		 * You could register custom conditions on this hook.
		 * Note - each condition should be presented like instance of class 'Jet_Popup_Conditions_Base'
		 */
		do_action( 'jet-theme-core/template-conditions/register', $this );

	}

	/**
	 * @return false
	 */
	public function maybe_update_backward_conditions() {

		if ( version_compare( JET_THEME_CORE_VERSION, '2.0.0', '<' ) ) {
			return false;
		}

		$site_conditions = get_option( $this->conditions_key, [] );

		if ( empty( $site_conditions ) ) {
			return false;
		}

		foreach ( $site_conditions as $type => $type_templates ) {

			if ( ! empty( $type_templates ) ) {

				foreach ( $type_templates as $template_id => $template_conditions ) {

					if ( ! array_key_exists( 'main', $template_conditions ) ) {
						continue;
					}

					$elementor_template_type = get_post_meta( $template_id, '_elementor_template_type', true );
					update_post_meta( $template_id, '_jet_template_type', $elementor_template_type );
					$is_elementor_content_type = get_post_meta( $template_id, '_elementor_edit_mode', true );

					if ( 'builder' === $is_elementor_content_type ) {
						update_post_meta( $template_id, '_jet_template_content_type', 'elementor' );
					}
					
					$converted_template_conditions = $this->maybe_convert_conditions( $template_conditions );

					if ( $converted_template_conditions ) {
						$this->update_template_conditions( $template_id, $converted_template_conditions );
					}
				}
			}
		}

		return false;
	}

	/**
	 * [get_condition description]
	 * @param  [type] $condition_id [description]
	 * @return [type]               [description]
	 */
	public function get_condition( $condition_id ) {
		return isset( $this->_conditions[ $condition_id ] ) ? $this->_conditions[ $condition_id ] : false;
	}

	/**
	 * [get_template_id description]
	 * @return [type] [description]
	 */
	public function get_template_id() {
		return get_the_ID();
	}

	/**
	 * [update_template_conditions description]
	 * @param  boolean $post_id    [description]
	 * @param  array   $conditions [description]
	 * @return [type]              [description]
	 */
	public function update_template_conditions( $template_id = false, $conditions = [] ) {
		update_post_meta( $template_id, '_jet_template_conditions', $conditions );

		$siteTemplateConditions = get_option( $this->conditions_key, [] );
		$type = get_post_meta( $template_id, '_jet_template_type', true );
		$type = isset( $type ) ? $type : 'other';

		if ( ! isset( $siteTemplateConditions[ $type ] ) ) {
			$siteTemplateConditions[ $type ] = [];
		}

		if ( isset( $siteTemplateConditions[ $type ][ $template_id ] ) ) {
			unset( $siteTemplateConditions[ $type ][ $template_id ] );
			$new_condition[ $template_id ] = $conditions;
			$siteTemplateConditions[ $type ] = $new_condition + $siteTemplateConditions[ $type ];
		} else {
			$siteTemplateConditions[ $type ][ $template_id ] = $conditions;
			$siteTemplateConditions[ $type ] = array_reverse( $siteTemplateConditions[ $type ], true );
		}

		update_option( $this->conditions_key, $siteTemplateConditions, true );
	}

	/**
	 * [get_template_conditions description]
	 * @param  boolean $post_id [descriptionupdate_template_conditions
	 * @return [type]           [description]
	 */
	public function get_template_conditions( $template_id = false ) {
		$template_conditions = get_post_meta( $template_id, '_jet_template_conditions', true );

		return ! empty( $template_conditions ) ? $template_conditions : [];
	}

	/**
	 * [remove_post_from_site_conditions description]
	 * @param  integer $post_id [description]
	 * @return [type]           [description]
	 */
	public function remove_post_from_site_conditions( $post_id = 0 ) {
		$conditions = get_option( $this->conditions_key, [] );
		$conditions = $this->remove_post_from_conditions_array( $post_id, $conditions );

		update_option( $this->conditions_key, $conditions, true );
	}

	/**
	 * Check if post currently presented in conditions array and remove it if yes.
	 *
	 * @param  integer $post_id    [description]
	 * @param  array   $conditions [description]
	 * @return [type]              [description]
	 */
	public function remove_post_from_conditions_array( $post_id = 0, $conditions = array() ) {

		foreach ( $conditions as $type => $type_conditions ) {
			if ( array_key_exists( $post_id, $type_conditions ) ) {
				unset( $conditions[ $type ][ $post_id ] );
			}
		}

		return $conditions;
	}

	/**
	 * Run condtions check for passed type. Return {template_id} on firs condition match.
	 * If not matched - return false
	 *
	 * @return int|bool
	 */
	public function find_matched_conditions( $type, $single = false ) {

		$conditions = get_option( $this->conditions_key, [] );


		if ( empty( $conditions[ $type ] ) ) {
			return false;
		}

		$template_id_list = [];

		foreach ( $conditions[ $type ] as $template_id => $template_conditions ) {

			if ( empty( $template_conditions ) ) {
				continue;
			}

			$check_list = [];

			// for multi-language plugins
			$template_id = apply_filters( 'jet-theme-core/template-conditions/template_id', $template_id );

			$template_conditions = array_map( function( $condition ) use ( $template_id ) {

				$include = filter_var( $condition['include'] , FILTER_VALIDATE_BOOLEAN );

				if ( 'entire' === $condition['group'] ) {
					$match = 'entire' === $condition['group'] ? true : false;
					$condition['match'] = $match;

					return $condition;
				} else {
					$sub_group = $condition['subGroup'];

					$instance = $this->get_condition( $sub_group );

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

			}, $template_conditions );

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
				$template_id_list[] = $template_id;
			}
		}

		if ( ! empty( $template_id_list ) ) {

			if ( $single ) {
				return $template_id_list[0];
			}

			return $template_id_list;
		}

		return false;
	}

	/**
	 * @param array $condition
	 *
	 * @return array|mixed
	 */
	public function maybe_convert_conditions( $condition = [] ) {

		if ( ! array_key_exists( 'main', $condition ) ) {
			return false;
		}

		$new_condition        = [];
		$condition_array_keys = array_keys( $condition );
		$sub_group            = isset( $condition_array_keys[1] ) ? $condition_array_keys[1] : false;
		$sub_group_value      = '';

		if ( $sub_group && isset( $sub_group ) ) {
			$sub_group_key   = $condition[ $sub_group ];
			$key_value       = ! empty( array_keys( $sub_group_key ) ) ? array_keys( $sub_group_key )[ 0 ] : false;
			$sub_group_value = $key_value ? $sub_group_key[ $key_value ] : '';
		}

		if ( ! empty( $sub_group_value ) && is_array( $sub_group_value ) ) {

			foreach ( $sub_group_value as $key => $value ) {
				$new_condition[] = [
					'id'            => uniqid( '_' ),
					'include'       => 'true',
					'group'         => $condition['main'],
					'subGroup'      => $sub_group ? $sub_group : 'entire',
					'subGroupValue' => $value,
				];
			}

			return $new_condition;
		} else {
			$sub_group_value = ! is_array( $sub_group_value ) ? $sub_group_value : '';

			$new_condition[] = [
				'id'            => uniqid( '_' ),
				'include'       => 'true',
				'group'         => $condition['main'],
				'subGroup'      => $sub_group ? $sub_group : 'entire',
				'subGroupValue' => $sub_group_value,
			];

			return $new_condition;
		}

		return false;
	}

	/**
	 * Get active conditions for passed post
	 *
	 * @param  [type] $post_id [description]
	 * @return [type]          [description]
	 */
	public function post_conditions_verbose( $post_id = null ) {

		$verbose = '';

		$conditions = $this->get_template_conditions( $post_id );

		if ( empty( $conditions ) ) {
			$warning_icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.47 21H19.53C21.07 21 22.03 19.33 21.26 18L13.73 4.99C12.96 3.66 11.04 3.66 10.27 4.99L2.74 18C1.97 19.33 2.93 21 4.47 21ZM12 14C11.45 14 11 13.55 11 13V11C11 10.45 11.45 10 12 10C12.55 10 13 10.45 13 11V13C13 13.55 12.55 14 12 14ZM13 18H11V16H13V18Z" fill="#fcb92c"/></svg>';

			return sprintf(
				'<div class="jet-template-library__template-conditions-item undefined-conditions">%1$s<span>%2$s</span></div>',
				$warning_icon,
				__( 'Conditions not selected', 'jet-theme-core' )
			);
		}

		$verbose = '';

		if ( $this->isActiveTemplateStructure( $post_id ) ) {
			$verbose .= sprintf(
				'<div class="jet-template-library__template-conditions-item active-structure"><span class="dashicons dashicons-yes-alt"></span>%1$s</div>',
				__( 'Active', 'jet-theme-core' )
			);
		}

		foreach ( $conditions as $key => $condition ) {
			$include         = filter_var( $condition['include'], FILTER_VALIDATE_BOOLEAN );
			$group           = $condition['group'];
			$sub_group       = $condition['subGroup'];
			$sub_group_value = $condition['subGroupValue'];

			$item_icon = '<span class="dashicons dashicons-plus-alt2"></span>';

			if ( 'entire' === $group ) {
				$verbose .= sprintf( '<div class="jet-template-library__template-conditions-item match-condition">%2$s<span>%1$s</span></div>', __( 'Entire Site', 'jet-theme-core' ), $item_icon );

				continue;
			}

			$instance = $this->get_condition( $sub_group );

			if ( ! $instance ) {
				continue;
			}

			$item_class = 'jet-template-library__template-conditions-item match-condition';

			if ( ! $include ) {
				$item_class .= ' exclude';
				$item_icon = '<span class="dashicons dashicons-minus"></span>';
			}

			if ( ! empty( $sub_group_value ) ) {
				$label = $instance->get_label_by_value( $sub_group_value );
				$verbose .= sprintf( '<div class="%1$s">%4$s<span>%2$s: </span><i>%3$s</i></div>', $item_class, $instance->get_label(), $label, $item_icon );
			} else {
				$verbose .= sprintf( '<div class="%1$s">%3$s<span>%2$s</span></div>', $item_class, $instance->get_label(), $item_icon );
			}
		}

		return sprintf(
			'<div class="jet-template-library__template-conditions-list">%1$s</div>',
			$verbose
		) ;
	}

	/**
	 * @param $template_id
	 *
	 * @return bool
	 */
	public function isActiveTemplateStructure( $template_id ) {
		$structure = jet_theme_core()->structures->get_post_structure( $template_id );

		if ( ! $structure ) {
			return false;
		}

		$structure_id   = $structure->get_id();
		$siteConditions = get_option( 'jet_site_conditions', [] );

		if ( empty( $siteConditions ) || ! isset( $siteConditions[ $structure_id ] ) ) {
			return false;
		}

		$structure_templates = array_keys( $siteConditions[ $structure_id ] );

		if ( in_array( $template_id, $structure_templates ) ) {
			return true;
		}

		return false;
	}

	/**
	 * [prepare_data_for_localize description]
	 * @return [type] [description]
	 */
	public function get_conditions_raw_data() {

		$sorted_conditions = [
			'entire' => [
				'label'    => __( 'Entire', 'jet-theme-core' ),
			],
			'singular' => [
				'label'   => __( 'Singular', 'jet-theme-core' ),
			],
			'archive' => [
				'label'   => __( 'Archive', 'jet-theme-core' ),
			],
			'advanced' => [
				'label'   => __( 'Advanced', 'jet-theme-core' ),
			]
		];

		foreach ( $this->_conditions as $cid => $instance ) {

			$group = $instance->get_group();

			$current = array(
				'label'         => $instance->get_label(),
				'priority'      => $instance->get_priority(),
				'action'        => $instance->ajax_action(),
				'options'       => $instance->get_avaliable_options(),
				'control'       => $instance->get_control(),
				'bodyStructure' => $instance->get_body_structure(),
			);

			$sorted_conditions[ $group ]['sub-groups'][ $cid ] = $current;

		}

		return $sorted_conditions;
	}



	/**
	 * [__construct description]
	 */
	public function __construct() {
		$this->load_files();
		$this->register_conditions();
		$this->maybe_update_backward_conditions();

		add_action( 'wp_trash_post', array( $this, 'remove_post_from_site_conditions' ) );
	}

}
