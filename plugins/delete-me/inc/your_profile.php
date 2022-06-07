<?php
// File called by class?
if ( isset( $this ) == false || get_class( $this ) != 'plugin_delete_me' ) exit;

// Enabled?
if ( $this->option['settings']['your_profile_enabled'] == false ) return; // stop executing file

// Administrator or Super Admin?
$user = wp_get_current_user();
if ( in_array( 'administrator', $user->roles ) || ( is_multisite() && is_super_admin() ) ) {
	
	$output = __( 'The delete option provided by the Delete Me plugin is not visible to Administrators.', 'delete-me' );

// Non-Administrator
} else {
	
	// Does user have the capability?
	if ( $profileuser->has_cap( $this->info['cap'] ) == false ) return; // stop executing file

	// User has capability, prepare delete link
	$attributes = array();
	$attributes['class'] = $this->option['settings']['your_profile_class'];
	$attributes['style'] = $this->option['settings']['your_profile_style'];
	$attributes['href'] = esc_url( self_admin_url( 'options.php?page=' . $this->info['slug_prefix'] . '_confirmation' ) );

	// Remove empty attributes
	$attributes = array_filter( $attributes );

	// Assemble attributes in key="value" pairs
	foreach ( $attributes as $key => $value ) $paired_attributes[] = $key . '="' . $value . '"';

	// Output delete link
	/*
	$this->default_option()['settings']['your_profile_anchor']
	Function called separately from array key for PHP < 5.4
	*/
	$default_option = $this->default_option();
	$output = '<a ' . implode( ' ', $paired_attributes ) . '>' . ( $default_option['settings']['your_profile_anchor'] === $this->option['settings']['your_profile_anchor'] ? __( 'Delete Account', 'delete-me' ) : $this->option['settings']['your_profile_anchor'] ) . '</a>';
}
?>
<table class="form-table">
	<tr>
		<th>&nbsp;</th>
		<td><?php echo $output; ?></td>
	</tr>
</table>
