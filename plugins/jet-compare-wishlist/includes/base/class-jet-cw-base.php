<?php
/**
 * Compare & Wishlist base class
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

abstract class Jet_CW_Base extends Widget_Base {

	public $__context         = 'render';
	public $__processed_item  = false;
	public $__processed_index = 0;
	public $__new_icon_prefix = 'selected_';

	/**
	 * Returns jet help url
	 *
	 * @return false
	 */
	public function get_jet_help_url() {
		return false;
	}

	/**
	 * Returns helps url
	 *
	 * @return false|string
	 */
	public function get_help_url() {

		$url = $this->get_jet_help_url();

		if ( ! empty( $url ) ) {
			return add_query_arg(
				array(
					'utm_source'   => 'need-help',
					'utm_medium'   => $this->get_name(),
					'utm_campaign' => 'jetcomparewishlist',
				),
				esc_url( $url )
			);
		}

		return false;

	}

	/**
	 * Get globally affected template
	 *
	 * @param null $name
	 *
	 * @return bool|mixed|string
	 */
	public function __get_global_template( $name = null ) {

		$template = call_user_func( array( $this, sprintf( '__get_%s_template', $this->__context ) ) );

		if ( ! $template ) {
			$template = jet_cw()->get_template( $this->get_name() . '/global/' . $name . '.php' );
		}

		return $template;

	}

	/**
	 * Get front-end template
	 *
	 * @param null $name
	 *
	 * @return bool|string
	 */
	public function __get_render_template( $name = null ) {
		return jet_cw()->get_template( $this->get_name() . '/render/' . $name . '.php' );
	}

	/**
	 * Get editor template
	 *
	 * @param null $name
	 *
	 * @return bool|string
	 */
	public function __get_edit_template( $name = null ) {
		return jet_cw()->get_template( $this->get_name() . '/edit/' . $name . '.php' );
	}

	/**
	 * Get global looped template for settings
	 * Required only to process repeater settings.
	 *
	 * @param string $name    Base template name.
	 * @param string $setting Repeater setting that provide data for template.
	 *
	 * @return void
	 */
	public function __get_global_looped_template( $name = null, $setting = null ) {

		$templates = array(
			'start' => $this->__get_global_template( $name . '-loop-start' ),
			'loop'  => $this->__get_global_template( $name . '-loop-item' ),
			'end'   => $this->__get_global_template( $name . '-loop-end' ),
		);

		call_user_func(
			array( $this, sprintf( '__get_%s_looped_template', $this->__context ) ), $templates, $setting
		);

	}

	/**
	 * Get render mode looped template
	 *
	 * @param array $templates
	 * @param null  $setting
	 *
	 * @return void
	 */
	public function __get_render_looped_template( $templates = array(), $setting = null ) {

		$loop = $this->get_settings( $setting );

		if ( empty( $loop ) ) {
			return;
		}

		if ( ! empty( $templates['start'] ) ) {
			include $templates['start'];
		}

		foreach ( $loop as $item ) {
			$this->__processed_item = $item;

			if ( ! empty( $templates['start'] ) ) {
				include $templates['loop'];
			}

			$this->__processed_index++;
		}

		$this->__processed_item  = false;
		$this->__processed_index = 0;

		if ( ! empty( $templates['end'] ) ) {
			include $templates['end'];
		}

	}

	/**
	 * Get edit mode looped template
	 *
	 * @param array $templates
	 * @param null  $setting
	 *
	 * @return void
	 */
	public function __get_edit_looped_template( $templates = array(), $setting = null ) {
		?>
		<# if ( settings.<?php echo $setting; ?> ) { #>
		<?php
		if ( ! empty( $templates['start'] ) ) {
			include $templates['start'];
		}
		?>
		<# _.each( settings.<?php echo $setting; ?>, function( item ) { #>
		<?php
		if ( ! empty( $templates['loop'] ) ) {
			include $templates['loop'];
		}
		?>
		<# } ); #>
		<?php
		if ( ! empty( $templates['end'] ) ) {
			include $templates['end'];
		}
		?>
		<# } #>
		<?php
	}

	/**
	 * Get current looped item dependents from context.
	 *
	 * @param array  $keys
	 * @param string $format
	 *
	 * @return mixed
	 */
	public function __loop_item( $keys = array(), $format = '%s' ) {
		return call_user_func( array( $this, sprintf( '__%s_loop_item', $this->__context ) ), $keys, $format );
	}

	/**
	 * Loop edit item
	 *
	 * @param array  $keys
	 * @param string $format [description]
	 *
	 * @return false|string [type]              [description]
	 */
	public function __edit_loop_item( $keys = array(), $format = '%s' ) {

		$settings = $keys[0];

		if ( isset( $keys[1] ) ) {
			$settings .= '.' . $keys[1];
		}

		ob_start();

		echo '<# if ( item.' . $settings . ' ) { #>';
		printf( $format, '{{{ item.' . $settings . ' }}}' );
		echo '<# } #>';

		return ob_get_clean();

	}

	/**
	 * Loop render item
	 *
	 * @param array  $keys
	 * @param string $format
	 *
	 * @return false|string
	 */
	public function __render_loop_item( $keys = array(), $format = '%s' ) {

		$item = $this->__processed_item;

		$key        = $keys[0];
		$nested_key = isset( $keys[1] ) ? $keys[1] : false;

		if ( empty( $item ) || ! isset( $item[ $key ] ) ) {
			return false;
		}

		if ( false === $nested_key || ! is_array( $item[ $key ] ) ) {
			$value = $item[ $key ];
		} else {
			$value = isset( $item[ $key ][ $nested_key ] ) ? $item[ $key ][ $nested_key ] : false;
		}

		if ( ! empty( $value ) ) {
			return sprintf( $format, $value );
		}

	}

	/**
	 * Include global template if any of passed settings is defined
	 *
	 * @param null  $name
	 * @param array $settings
	 *
	 * @return void
	 */
	public function __glob_inc_if( $name = null, $settings = array() ) {

		$template = $this->__get_global_template( $name );

		call_user_func( array( $this, sprintf( '__%s_inc_if', $this->__context ) ), $template, $settings );

	}

	/**
	 * Include render template if any of passed setting is not empty
	 *
	 * @param null  $file
	 * @param array $settings
	 *
	 * @return void
	 */
	public function __render_inc_if( $file = null, $settings = array() ) {
		foreach ( $settings as $setting ) {
			$val = $this->get_settings( $setting );

			if ( ! empty( $val ) ) {
				include $file;
				return;
			}
		}
	}

	/**
	 * Include render template if any of passed setting is not empty
	 *
	 * @param null  $file
	 * @param array $settings
	 *
	 * @return void
	 */
	public function __edit_inc_if( $file = null, $settings = array() ) {

		$condition = null;
		$sep       = null;

		foreach ( $settings as $setting ) {
			$condition .= $sep . 'settings.' . $setting;
			$sep       = ' || ';
		}
		?>

		<# if ( <?php echo $condition; ?> ) { #>

		<?php include $file; ?>
		<# } #>
		<?php

	}

	/**
	 * Open standard wrapper
	 *
	 * @return void
	 */
	public function __open_wrap() {
		printf( '<div class="%s jet-cw">', $this->get_name() );
	}

	/**
	 * Close standard wrapper
	 *
	 * @return void
	 */
	public function __close_wrap() {
		echo '</div>';
	}

	/**
	 * Print HTML markup if passed setting not empty.
	 *
	 * @param array  $data    Array with all data.
	 * @param string $setting Passed setting.
	 * @param string $format  Required markup.
	 *
	 * @return string|void
	 */
	public function __html( $data = [], $setting = null, $format = '%s' ) {
		if ( ! empty( $data[ $setting ] ) ) {
			printf( $format, $data[ $setting ] );
		}
	}

	/**
	 * Returns HTML markup if passed setting not empty.
	 *
	 * @param null   $setting Passed setting.
	 * @param string $format  Required markup.
	 *
	 * @return string|void
	 */
	public function __get_html( $setting = null, $format = '%s' ) {

		ob_start();

		$this->__html( $setting, $format );

		return ob_get_clean();

	}

	/**
	 * Add icon control
	 *
	 * @param string $id
	 * @param array  $args
	 * @param object $instance
	 */
	public function __add_advanced_icon_control( $id = '', array $args = array(), $instance = null ) {

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '2.6.0', '>=' ) ) {

			$_id = $id; // old control id
			$id  = $this->__new_icon_prefix . $id;

			$args['type']             = Controls_Manager::ICONS;
			$args['fa4compatibility'] = $_id;

			unset( $args['file'] );
			unset( $args['default'] );

			if ( isset( $args['fa5_default'] ) ) {
				$args['default'] = $args['fa5_default'];

				unset( $args['fa5_default'] );
			}
		} else {
			$args['type'] = Controls_Manager::ICON;
			unset( $args['fa5_default'] );
		}

		if ( null !== $instance ) {
			$instance->add_control( $id, $args );
		} else {
			$this->add_control( $id, $args );
		}

	}

	/**
	 * Prepare icon control ID for condition.
	 *
	 * @param string $id Old icon control ID.
	 *
	 * @return string
	 */
	public function __prepare_icon_id_for_condition( $id ) {

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '2.6.0', '>=' ) ) {
			return $this->__new_icon_prefix . $id . '[value]';
		}

		return $id;

	}

	/**
	 * Print HTML icon template
	 *
	 * @param array  $setting
	 * @param string $format
	 * @param string $icon_class
	 * @param bool   $echo
	 *
	 * @return void|string
	 */
	public function __render_icon( $setting = null, $format = '%s', $icon_class = '', $echo = true ) {

		if ( false === $this->__processed_item ) {
			$settings = $this->get_settings_for_display();
		} else {
			$settings = $this->__processed_item;
		}

		$new_setting = $this->__new_icon_prefix . $setting;

		$migrated = isset( $settings['__fa4_migrated'][ $new_setting ] );
		$is_new   = empty( $settings[ $setting ] ) && class_exists( 'Elementor\Icons_Manager' ) && Icons_Manager::is_migration_allowed();

		$icon_html = '';

		if ( $is_new || $migrated ) {
			$attr = array( 'aria-hidden' => 'true' );

			if ( ! empty( $icon_class ) ) {
				$attr['class'] = $icon_class;
			}

			if ( isset( $settings[ $new_setting ] ) ) {
				ob_start();

				Icons_Manager::render_icon( $settings[ $new_setting ], $attr );

				$icon_html = ob_get_clean();
			}
		} else if ( ! empty( $settings[ $setting ] ) ) {
			if ( empty( $icon_class ) ) {
				$icon_class = $settings[ $setting ];
			} else {
				$icon_class .= ' ' . $settings[ $setting ];
			}

			$icon_html = sprintf( '<i class="%s" aria-hidden="true"></i>', $icon_class );
		}

		if ( empty( $icon_html ) ) {
			return;
		}

		if ( ! $echo ) {
			return sprintf( $format, $icon_html );
		}

		printf( $format, $icon_html );

	}

}
