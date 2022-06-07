<?php
namespace Jet_Theme_Core;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Utils {

	/**
	 * @return bool
	 */
	public static function has_elementor() {
		return defined( 'ELEMENTOR_VERSION' );
	}

	/**
	 * @return bool
	 */
	public static function has_elementor_pro() {
		return defined( 'ELEMENTOR_PRO_VERSION' );
	}

	/**
	 * [is_license_exist description]
	 * @return boolean [description]
	 */
	public static function get_theme_core_license() {
		return \Jet_Dashboard\Utils::get_plugin_license_key( 'jet-theme-core/jet-theme-core.php' );
	}

	/**
	 * [active_license_link description]
	 * @return [type] [description]
	 */
	public static function active_license_link() {
		return \Jet_Dashboard\Dashboard::get_instance()->get_dashboard_page_url( 'license-page' );
	}

	/**
	 * @return string
	 */
	public static function is_min_suffix() {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}

	/**
	 * [search_terms_by_tax description]
	 * @param  [type] $tax   [description]
	 * @param  [type] $query [description]
	 * @return [type]        [description]
	 */
	public static function get_terms_by_tax( $tax, $query ) {

		$terms = get_terms( array(
			'taxonomy'   => $tax,
			'hide_empty' => false,
			'name__like' => $query,
		) );

		$result = array();

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[] = array(
					'value'   => $term->term_id,
					'label' => $term->name,
				);
			}
		}

		return $result;

	}

	/**
	 * Get post types options list
	 *
	 * @return array
	 */
	public static function get_post_types() {

		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		$deprecated = apply_filters(
			'jet-theme-core/post-types-list/deprecated',
			array(
				'attachment',
				'elementor_library',
				jet_theme_core()->templates->post_type,
			)
		);

		$result = array();

		if ( empty( $post_types ) ) {
			return $result;
		}

		foreach ( $post_types as $slug => $post_type ) {

			if ( in_array( $slug, $deprecated ) ) {
				continue;
			}

			$result[ $slug ] = $post_type->label;

		}

		return $result;

	}

	/**
	 * Get post types options list
	 *
	 * @return array
	 */
	public static function get_post_types_options() {

		$post_types = self::get_post_types();

		$result = array();

		if ( empty( $post_types ) ) {
			return $result;
		}

		foreach ( $post_types as $slug => $label ) {

			$result[] = array(
				'label' => $label,
				'value' => $slug,
			);

		}

		return $result;

	}

	/**
	 * Returns all custom taxonomies
	 *
	 * @return [type] [description]
	 */
	public static function get_taxonomies() {

		$taxonomies = get_taxonomies( array(
			'public'   => true,
			'_builtin' => false
		), 'objects' );

		$deprecated = apply_filters(
			'jet-theme-core/taxonomies-list/deprecated',
			array()
		);

		$result = array();

		if ( empty( $taxonomies ) ) {
			return $result;
		}

		foreach ( $taxonomies as $slug => $tax ) {

			if ( in_array( $slug, $deprecated ) ) {
				continue;
			}

			$result[ $slug ] = $tax->label;

		}

		return $result;
	}

	/**
	 * [search_posts_by_type description]
	 * @param  [type] $type  [description]
	 * @param  [type] $query [description]
	 * @param  array  $ids   [description]
	 * @return [type]        [description]
	 */
	public static function search_posts_by_type( $type, $query, $ids = array() ) {

		add_filter( 'posts_where', array( __CLASS__, 'force_search_by_title' ), 10, 2 );

		$posts = get_posts( array(
			'post_type'           => $type,
			'ignore_sticky_posts' => true,
			'posts_per_page'      => -1,
			'suppress_filters'     => false,
			's_title'             => $query,
			'include'             => $ids,
		) );

		remove_filter( 'posts_where', array( __CLASS__, 'force_search_by_title' ), 10 );

		$result = array();

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$result[] = array(
					'id'   => $post->ID,
					'text' => $post->post_title,
				);
			}
		}

		return $result;
	}

	/**
	 * Force query to look in post title while searching
	 * @return [type] [description]
	 */
	public static function force_search_by_title( $where, $query ) {

		$args = $query->query;

		if ( ! isset( $args['s_title'] ) ) {
			return $where;
		} else {
			global $wpdb;

			$searh = esc_sql( $wpdb->esc_like( $args['s_title'] ) );
			$where .= " AND {$wpdb->posts}.post_title LIKE '%$searh%'";

		}

		return $where;
	}

	/**
	 * [search_terms_by_tax description]
	 * @param  [type] $tax   [description]
	 * @param  [type] $query [description]
	 * @param  array  $ids   [description]
	 * @return [type]        [description]
	 */
	public static function search_terms_by_tax( $tax, $query, $ids = array() ) {

		$terms = get_terms( array(
			'taxonomy'   => $tax,
			'hide_empty' => false,
			'name__like' => $query,
			'include'    => $ids,
		) );

		$result = [];

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[] = array(
					'id'   => $term->term_id,
					'text' => $term->name,
				);
			}
		}

		return $result;

	}

	/**
	 * [search_posts_by_type description]
	 * @param  [type] $type  [description]
	 * @param  [type] $query [description]
	 * @return [type]        [description]
	 */
	public static function get_posts_by_type( $type, $query ) {

		add_filter( 'posts_where', array( __CLASS__, 'force_search_by_title' ), 10, 2 );

		$posts = get_posts( array(
			'post_type'           => $type,
			'ignore_sticky_posts' => true,
			'posts_per_page'      => -1,
			'suppress_filters'    => false,
			's_title'             => $query,
			'post_status'         => [ 'publish', 'private' ],
		) );

		remove_filter( 'posts_where', array( __CLASS__, 'force_search_by_title' ), 10, 2 );

		$result = array();

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$result[] = array(
					'value' => $post->ID,
					'label' => $post->post_title,
				);
			}
		}

		return $result;
	}

	/**
	 * Gets a value from a nested array using an address string.
	 *
	 * @param array  $array   An array which contains value located at `$address`.
	 * @param string|array $address The location of the value within `$array` (dot notation).
	 * @param mixed  $default Value to return if not found. Default is an empty string.
	 *
	 * @return mixed The value, if found, otherwise $default.
	 */
	public static function array_get( $array, $address, $default = '' ) {
		$keys   = is_array( $address ) ? $address : explode( '.', $address );
		$value  = $array;

		foreach ( $keys as $key ) {
			if ( ! empty( $key ) && isset( $key[0] ) && '[' === $key[0] ) {
				$index = substr( $key, 1, -1 );

				if ( is_numeric( $index ) ) {
					$key = (int) $index;
				}
			}

			if ( ! isset( $value[ $key ] ) ) {
				return $default;
			}

			$value = $value[ $key ];
		}

		return $value;
	}

}
