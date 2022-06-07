<?php
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request

if (!function_exists('tatum_skip_wp_admin_notice')) {
    /**
     * Show an admin notice to administrators when the minimum WP version
     * could not be reached. The error message is only in english available.
     */
    function tatum_skip_wp_admin_notice() {
        if (current_user_can('install_plugins')) {
            $data = get_plugin_data(TATUM_FILE, true, false);
            global $wp_version;
            echo '<div class=\'notice notice-error\'>
				<p><strong>' .
                $data['Name'] .
                '</strong> could not be initialized because you need minimum WordPress version ' .
                TATUM_MIN_WP .
                ' ... you are running: ' .
                $wp_version .
                '.
				<a href="' .
                admin_url('update-core.php') .
                '">Update WordPress now.</a>
			</div>';
        }
    }
}
add_action('admin_notices', 'tatum_skip_wp_admin_notice');
