<?php
// File called by class?
if ( isset( $this ) == false || get_class( $this ) != 'plugin_delete_me' ) exit;

// If the delete link will not be shown, the content (if any) inside the shortcode will serve as an alternative.

// Administrator or Super Admin?
$user = wp_get_current_user();
if ( !empty( $user->ID ) && ( in_array( 'administrator', $user->roles ) || ( is_multisite() && is_super_admin() ) ) ) {
	
	$longcode = empty( $content ) ? __( 'The delete option provided by the Delete Me plugin is not visible to Administrators.', 'delete-me' ) : NULL;

// Non-Administrator
} else {
	
	// Does user have the capability?
	if ( current_user_can( $this->info['cap'] ) == false ) return; // stop execution of this file

	// Temporarily hold default option
	$default_option = $this->default_option();

	// User has capability, prepare delete link
	$atts = shortcode_atts( array(
		'class' => $this->option['settings']['shortcode_class'],
		'style' => $this->option['settings']['shortcode_style'],
		'html' => $default_option['settings']['shortcode_anchor'] === $this->option['settings']['shortcode_anchor'] ? __( 'Delete Account', 'delete-me' ) : $this->option['settings']['shortcode_anchor'],
		'js_confirm_warning' => $default_option['settings']['shortcode_js_confirm_warning'] === $this->option['settings']['shortcode_js_confirm_warning'] ? /* xgettext:no-php-format */ _x( 'WARNING!\n\nAre you sure you want to delete user %username% from %sitename%?', 'JavaScript confirm user deletion', 'delete-me' ) : $this->option['settings']['shortcode_js_confirm_warning'],
		'landing_url' => '', // Empty default
		'form_confirm_warning' => $default_option['settings']['shortcode_form_confirm_warning'] === $this->option['settings']['shortcode_form_confirm_warning'] ? /* xgettext:no-php-format */ __( 'WARNING!<br /><br />Are you sure you want to delete user %username% from %sitename%?', 'delete-me' ) : $this->option['settings']['shortcode_form_confirm_warning'],
		'form_password_label' => $default_option['settings']['shortcode_form_confirm_password_label'] === $this->option['settings']['shortcode_form_confirm_password_label'] ? __( 'Password', 'delete-me' ) : $this->option['settings']['shortcode_form_confirm_password_label'],
		'form_confirm_button' => $default_option['settings']['shortcode_form_confirm_button'] === $this->option['settings']['shortcode_form_confirm_button'] ? __( 'Confirm Deletion', 'delete-me' ) : $this->option['settings']['shortcode_form_confirm_button'],
	) , $atts );
	$attributes = array();
	$attributes['class'] = $atts['class'];
	$attributes['style'] = $atts['style'];
	$attributes['href'] = esc_url( add_query_arg(
		array_filter( // Removes landing_url if empty
			array(
				$this->info['trigger'] => $this->user_ID,
				$this->info['nonce'] => wp_create_nonce( $this->info['nonce'] ),
				$this->info['trigger'] . '_landing_url' => $atts['landing_url'],
			)
		)
	) );
	$js_confirm_warning = str_replace( '%username%', $this->user_login, $atts['js_confirm_warning'] );
	$js_confirm_warning = str_replace( '%sitename%', get_option( 'blogname' ), $js_confirm_warning );
	$js_confirm_warning = str_replace( '%displayname%', $user->display_name, $js_confirm_warning );
	if ( $this->option['settings']['shortcode_js_confirm_enabled'] ) $attributes['onclick'] = "if ( ! confirm( '" . esc_attr( addcslashes( $js_confirm_warning, "'" ) ) . "' ) ) return false;";

	// Remove empty attributes
	$attributes = array_filter( $attributes );

	// Assemble attributes in key="value" pairs
	foreach ( $attributes as $key => $value ) $paired_attributes[] = $key . '="' . $value . '"';

	// Implode attributes, return longcode as form or link
	if ( $this->option['settings']['shortcode_form_enabled'] ) {

		$form_confirm_warning = str_replace( '%username%', $this->user_login, $atts['form_confirm_warning'] );
		$form_confirm_warning = str_replace( '%sitename%', get_option( 'blogname' ), $form_confirm_warning );
		$form_confirm_warning = str_replace( '%displayname%', $user->display_name, $form_confirm_warning );
		$incorrect_password_style = empty( $this->POST[$this->info['trigger'] . '_shortcode_password'] ) ? '' : ' style="border: 1px solid #dc3232; box-shadow: 0 0 2px rgba(204,0,0,.8);"';
		$form_password_label = $atts['form_password_label'];
		$form_confirm_button = str_replace( '%username%', $this->user_login, $atts['form_confirm_button'] );
		$form_confirm_button = str_replace( '%displayname%', $user->display_name, $form_confirm_button );
		$longcode =
		// Do not escape Warning or Password label, HTML expected.
		'<form id="' . $this->info['trigger'] . '_shortcode_form" action="' . $attributes['href'] . '" method="post">
			<p>
				' . $form_confirm_warning . '
			</p>
			<p>
				<label for="' . $this->info['trigger'] . '_shortcode_password">' . ( $form_password_label ) . '</label>
				<input' . $incorrect_password_style . ' type="password" autocomplete="off" autofocus="autofocus" id="' . $this->info['trigger'] . '_shortcode_password" name="' . $this->info['trigger'] . '_shortcode_password" />
			</p>
			<p>
				<input type="submit" value="' . esc_attr( $form_confirm_button ) . '" />
			</p>
		</form>';

	} else {

		$longcode = '<a ' . implode( ' ', $paired_attributes ) . '>' . $atts['html'] . '</a>';

	}

}
