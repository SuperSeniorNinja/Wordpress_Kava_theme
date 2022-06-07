<?php
/**
* Deep Linking Javascript extension
*/
namespace extensions\deep_linking;

class Main{

	const PREFIX = 'deep-linking';
	const DIR = '/extensions/wp-deep-linking/';

	private static $_instance;

	public function __construct(){
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_theme_support( self::PREFIX );
	}

	public function enqueue_scripts(){

		global $localize_data;

		wp_register_script( 'jquery-deep-linking', $this->get_script_src(), array( 'jquery' ), false, false );

		$localize_data['base_url'] = trailingslashit( home_url() );

		wp_localize_script( 'jquery-deep-linking', 'deep_linking', $localize_data );
		wp_enqueue_script( 'jquery-deep-linking' );
	}

	public function get_script_src(){
		/** To do: minification switch */
		return get_stylesheet_directory_uri() . self::DIR . 'js/jquery.deep-linking.js';
	}

	public static function instance(){

		if( null == self::$_instance ){
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}

if( !function_exists( 'wp_deep_linking' ) ){
	function wp_deep_linking(){
		return Main::instance();
	}

	$_GLOBAL['deep_linking'] = wp_deep_linking();
}
