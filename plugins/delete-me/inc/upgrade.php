<?php
// File called by class?
if ( isset( $this ) == false || get_class( $this ) != 'plugin_delete_me' ) exit;

// Record previous version
$previous_version = $option['version'];

// Make option changes
// Version 1.6
if ( version_compare( $previous_version, '1.2', '>' ) && version_compare( $previous_version, '1.6', '<' ) ) {
	
	$option['settings']['your_profile_confirm_warning'] = str_replace( '\n', '<br />', $option['settings']['your_profile_js_confirm'] );
	$option['settings']['shortcode_js_confirm_warning'] = $option['settings']['shortcode_js_confirm'];
	
}
$option['version'] = $this->info['version'];
$option = $this->sync_arrays( $this->default_option(), $option ); // sync old & new option arrays

// Save changes to update option with the newly synced option array
$this->save_option( $network, $option );

// Print admin message
$this->admin_message_class = 'updated';
$this->admin_message_content = sprintf( __( 'Plugin %1$s updated to version %2$s. See <a href="%3$s">Changelog</a> for details.', 'delete-me' ), $this->info['name'], $this->info['version'], esc_url( $this->info['url'] . '#developers' ) );
add_action( 'all_admin_notices', array( &$this, 'admin_message' ) );
