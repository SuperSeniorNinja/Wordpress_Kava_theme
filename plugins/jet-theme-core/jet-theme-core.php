<?php
/**
 * Plugin Name: JetThemeCore
 * Plugin URI:  https://crocoblock.com/plugins/jetthemecore/
 * Description: Most powerful plugin created to make building websites super easy
 * Version:     2.0.0
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-theme-core
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

add_action( 'plugins_loaded', 'jet_theme_core_init' );

function jet_theme_core_init() {
	define( 'JET_THEME_CORE_VERSION', '2.0.0' );
	define( 'JET_THEME_CORE_FILE', __FILE__ );
	define( 'JET_THEME_CORE_PLUGIN_NAME', plugin_basename( __FILE__ ) );
	define( 'JET_THEME_CORE_PLUGIN_BASE', plugin_basename( JET_THEME_CORE_FILE ) );
	define( 'JET_THEME_CORE_PATH', plugin_dir_path( JET_THEME_CORE_FILE ) );
	define( 'JET_THEME_CORE_URL', plugins_url( '/', JET_THEME_CORE_FILE ) );

	require JET_THEME_CORE_PATH . 'includes/plugin.php';
}

function jet_theme_core() {
	return Jet_Theme_Core\Plugin::get_instance();
}

