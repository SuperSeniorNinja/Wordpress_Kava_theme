<?php
// File called by class?
if ( isset( $this ) == false || get_class( $this ) != 'plugin_delete_me' ) exit;

// Does user have the capability for this menu page?
if ( current_user_can( 'delete_users' ) == false ) return; // stop executing file

// Network Settings page?
$network_settings = is_network_admin();

// Only used to check Network Wide status on site-specific settings page
/*
$network = is_multisite() ? $this->fetch_option( true )['network'] : false;
---------------------------------------------------------------------------
Function called separately from array key for PHP < 5.4
*/
if ( is_multisite() ) {
	
	$network_option = $this->fetch_option( true );
	$network = $network_option['network'];
	
} else {
	
	$network = false;
	
}

// Temporarily holds settings for use on this page
$option = $this->fetch_option( $network_settings );

// Temporarily holds default settings for use on this page
$default_option = $this->default_option();

// Temporarily holds roles for use on this page
settype( $roles, 'array' );
if ( $network_settings ) {
	
	$blog_ids = $this->wpdb->get_col( "SELECT `blog_id` FROM " . $this->wpdb->blogs );
	
	foreach ( $blog_ids as $blog_id ) {		
		
		switch_to_blog( $blog_id );
		
		foreach ( $this->wp_roles->role_objects as $role_object ) {
			
			if ( isset( $roles[$role_object->name] ) ) continue; // Skip if already recorded, most sites will have the similar if not the same roles.
			$roles[$role_object->name] = array( 'name_id' => $role_object->name, 'name_text' => $this->wp_roles->roles[$role_object->name]['name'], 'has_cap' => ( $option['network'] === false ) ? false : $role_object->has_cap( $this->info['cap'] ) ); // Set has_cap to false on Network settings page if Network Wide disabled
			
		}
		
		restore_current_blog();
		
	}
	
} else {
	
	foreach ( $this->wp_roles->role_objects as $role_object ) {
		
		$roles[$role_object->name] = array( 'name_id' => $role_object->name, 'name_text' => $this->wp_roles->roles[$role_object->name]['name'], 'has_cap' => $role_object->has_cap( $this->info['cap'] ) );
		
	}
	
}

// Form nonce
$form_nonce_action = $this->GET['page'] . '_nonce_action';
$form_nonce_name = $this->GET['page'] . '_nonce_name';

// [Save Changes], [Restore Default Settings], or [Enable/Disable Network Wide]
if ( isset( $this->POST[$form_nonce_name] ) && wp_verify_nonce( $this->POST[$form_nonce_name], $form_nonce_action ) ) {
	
	if ( isset( $this->GET['restore'] ) ) {
		
		// Only reset roles on Network settings page if Network Wide is enabled
		// Only reset roles on site-specific settings page if Network Wide is disabled
		if ( ( $network_settings && $option['network'] === true ) || ( !$network_settings && $network === false ) ) {
			
			// Restore default settings
			if ( $network_settings ) {

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					foreach ( $this->wp_roles->role_objects as $role_object ) $role_object->remove_cap( $this->info['cap'] );
					restore_current_blog();

				}

			} else {
				
				foreach ( $this->wp_roles->role_objects as $role_object ) $role_object->remove_cap( $this->info['cap'] );
				
			}
			
			foreach ( $roles as $role ) $roles[$role['name_id']]['has_cap'] = false;
			
		}
		
		$option = $default_option;
		$admin_message_content = '<strong>' . __( 'Default settings restored.', 'delete-me' ) . '</strong>';
		
	} elseif ( isset( $this->GET['toggle_network'] ) ) {
		
		if ( $network_settings ) : // Just checking to make sure we're on Network settings page.
			
			if ( $option['network'] === true ) {
				
				// Disable
				$option['network'] = false;
				$admin_message_content = '<strong>' . __( 'Network Wide has been disabled.', 'delete-me' ) . '</strong>';
				
			} else {
				
				// Enable
				$option['network'] = true;
				$admin_message_content = '<strong>' . __( 'Network Wide has been enabled.', 'delete-me' ) . '</strong>';
				
			}
			
			// Reset roles
			foreach ( $blog_ids as $blog_id ) {
				
				switch_to_blog( $blog_id );
				foreach ( $this->wp_roles->role_objects as $role_object ) $role_object->remove_cap( $this->info['cap'] );
				restore_current_blog();
				
			}
			
			$option['network_selected_roles'] = array();
			foreach ( $roles as $role ) $roles[$role['name_id']]['has_cap'] = false;			
			
		endif;
		
	} else {
		
		// Roles
		settype( $this->POST['roles'], 'array' );
		
		// Do not update roles on Network settings page if Network Wide is disabled
		// Do not update roles on site-specific settings page if Network Wide is enabled
		if ( ( $network_settings && $option['network'] === true ) || ( !$network_settings && $network === false ) ) :
			
			$option['network_selected_roles'] = array();
			
			foreach ( $roles as $role ) {
				
				$checked = isset( $this->POST['roles'][$role['name_id']] ) ? true : false;
				$has_cap = $role['has_cap'] ? true : false;
				
				if ( $network_settings && $checked ) $option['network_selected_roles'][] = $role['name_id'];
				
				if ( $checked == true && $has_cap == false ) {
					
					if ( $network_settings ) {						
						
						foreach ( $blog_ids as $blog_id ) {

							switch_to_blog( $blog_id );
							$role_object = get_role( $role['name_id'] );
							if ( $role_object ) $role_object->add_cap( $this->info['cap'] ); // May not exist on all sites
							restore_current_blog();

						}

					} else {

						$role_object = get_role( $role['name_id'] )->add_cap( $this->info['cap'] );

					}

					$roles[$role['name_id']]['has_cap'] = true;

				} elseif ( $checked == false && $has_cap == true ) {

					if ( $network_settings ) {
						
						foreach ( $blog_ids as $blog_id ) {

							switch_to_blog( $blog_id );
							$role_object = get_role( $role['name_id'] );
							if ( $role_object ) $role_object->remove_cap( $this->info['cap'] ); // May not exist on all sites
							restore_current_blog();

						}

					} else {

						$role_object = get_role( $role['name_id'] )->remove_cap( $this->info['cap'] );

					}

					$roles[$role['name_id']]['has_cap'] = false;

				}

			}
			
		endif;
		
		// Your Profile
		settype( $this->POST['your_profile_class'], 'string' );
		settype( $this->POST['your_profile_style'], 'string' );
		settype( $this->POST['your_profile_anchor'], 'string' );
		settype( $this->POST['your_profile_confirm_heading'], 'string' );
		settype( $this->POST['your_profile_confirm_warning'], 'string' );
		settype( $this->POST['your_profile_confirm_password_required'], 'bool' );
		settype( $this->POST['your_profile_confirm_password_label'], 'string' );
		settype( $this->POST['your_profile_confirm_button'], 'string' );
		settype( $this->POST['your_profile_landing_url'], 'string' );
		settype( $this->POST['your_profile_enabled'], 'bool' );		
		$option['settings']['your_profile_class'] = empty( $this->POST['your_profile_class'] ) ? NULL : $this->POST['your_profile_class'];
		$option['settings']['your_profile_style'] = empty( $this->POST['your_profile_style'] ) ? NULL : $this->POST['your_profile_style'];
		$option['settings']['your_profile_anchor'] = empty( $this->POST['your_profile_anchor'] ) ? $default_option['settings']['your_profile_anchor'] : $this->POST['your_profile_anchor'];
		$option['settings']['your_profile_confirm_heading'] = empty( $this->POST['your_profile_confirm_heading'] ) ? $default_option['settings']['your_profile_confirm_heading'] : $this->POST['your_profile_confirm_heading'];
		$option['settings']['your_profile_confirm_warning'] = empty( $this->POST['your_profile_confirm_warning'] ) ? $default_option['settings']['your_profile_confirm_warning'] : $this->POST['your_profile_confirm_warning'];
		$option['settings']['your_profile_confirm_password_required'] = $this->POST['your_profile_confirm_password_required'];
		$option['settings']['your_profile_confirm_password_label'] = empty( $this->POST['your_profile_confirm_password_label'] ) ? $default_option['settings']['your_profile_confirm_password_label'] : $this->POST['your_profile_confirm_password_label'];
		$option['settings']['your_profile_confirm_button'] = empty( $this->POST['your_profile_confirm_button'] ) ? $default_option['settings']['your_profile_confirm_button'] : $this->POST['your_profile_confirm_button'];
		$option['settings']['your_profile_landing_url'] = empty( $this->POST['your_profile_landing_url'] ) ? '' : $this->POST['your_profile_landing_url'];
		$option['settings']['your_profile_enabled'] = $this->POST['your_profile_enabled'];
		
		// Shortcode
		settype( $this->POST['shortcode_class'], 'string' );
		settype( $this->POST['shortcode_style'], 'string' );
		settype( $this->POST['shortcode_anchor'], 'string' );
		settype( $this->POST['shortcode_js_confirm_warning'], 'string' );
		settype( $this->POST['shortcode_js_confirm_enabled'], 'bool' );
		settype( $this->POST['shortcode_form_enabled'], 'bool' );
		settype( $this->POST['shortcode_form_confirm_warning'], 'string' );
		settype( $this->POST['shortcode_form_confirm_password_label'], 'string' );
		settype( $this->POST['shortcode_form_confirm_button'], 'string' );
		settype( $this->POST['shortcode_landing_url'], 'string' );
		$option['settings']['shortcode_class'] = empty( $this->POST['shortcode_class'] ) ? NULL : $this->POST['shortcode_class'];
		$option['settings']['shortcode_style'] = empty( $this->POST['shortcode_style'] ) ? NULL : $this->POST['shortcode_style'];
		$option['settings']['shortcode_anchor'] = empty( $this->POST['shortcode_anchor'] ) ? $default_option['settings']['shortcode_anchor'] : $this->POST['shortcode_anchor'];
		$option['settings']['shortcode_js_confirm_warning'] = empty( $this->POST['shortcode_js_confirm_warning'] ) ? $default_option['settings']['shortcode_js_confirm_warning'] : $this->POST['shortcode_js_confirm_warning'];
		$option['settings']['shortcode_js_confirm_enabled'] = $this->POST['shortcode_js_confirm_enabled'];		
		$option['settings']['shortcode_form_enabled'] = $this->POST['shortcode_form_enabled'];
		$option['settings']['shortcode_form_confirm_warning'] = empty( $this->POST['shortcode_form_confirm_warning'] ) ? $default_option['settings']['shortcode_form_confirm_warning'] : $this->POST['shortcode_form_confirm_warning'];
		$option['settings']['shortcode_form_confirm_password_label'] = empty( $this->POST['shortcode_form_confirm_password_label'] ) ? $default_option['settings']['shortcode_form_confirm_password_label'] : $this->POST['shortcode_form_confirm_password_label'];
		$option['settings']['shortcode_form_confirm_button'] = empty( $this->POST['shortcode_form_confirm_button'] ) ? $default_option['settings']['shortcode_form_confirm_button'] : $this->POST['shortcode_form_confirm_button'];
		$option['settings']['shortcode_landing_url'] = empty( $this->POST['shortcode_landing_url'] ) ? '' : $this->POST['shortcode_landing_url'];
		
		// Multisite: Delete from Network
		settype( $this->POST['ms_delete_from_network'], 'bool' );
		$option['settings']['ms_delete_from_network'] = $this->POST['ms_delete_from_network'];
		
		// Delete Comments
		settype( $this->POST['delete_comments'], 'bool' );
		$option['settings']['delete_comments'] = $this->POST['delete_comments'];
		
		// E-mail notification
		settype( $this->POST['email_notification'], 'bool' );
		$option['settings']['email_notification'] = $this->POST['email_notification'];
		
		// Admin message content
		$admin_message_content = '<strong>' . __( 'Settings saved.', 'delete-me' ) . '</strong>';
		
	}
	
	// Save Option
	$this->save_option( $network_settings, $option );
	
	// Print admin message
	$this->admin_message_class = 'updated';
	$this->admin_message_content = $admin_message_content;
	$this->admin_message();
	
}

$settings_uri = remove_query_arg( array( 'restore', 'toggle_network' ) );

function editable_string_color( $unchanged ) {
	
	return $unchanged ? '#b5e1b9' : '#ffe399'; // Unchanged : Changed
	
}
?>
<div class="wrap">
	<div class="icon32" id="icon-options-general"><br/></div>
	<h2><?php printf( esc_html_x( '%s Settings', '%s = plugin name', 'delete-me' ), $this->info['name'] ); echo ( $network_settings ) ? ( ' &mdash; ' . esc_html__( 'Network-Wide', 'delete-me' ) ) : ''; ?></h2>
	<form action="<?php echo esc_url( $settings_uri ); ?>" method="post">
		<!-- Start: Disabled and Ineffective Message -->
		<?php if ( ( $network_settings && $option['network'] === false ) || ( !$network_settings && $network === true ) ) : ?>
		<div class="updated">
			<?php printf( _x( 'Role changes are disabled here and changes to settings will not go into effect because <strong>Network-Wide</strong> is %s.', '%s = enabled or disabled', 'delete-me' ), ( ( $network_settings && $option['network'] === true ) || ( !$network_settings && $network === true ) ) ? '<strong style="color: green;">enabled</strong>' : '<strong style="color: red;">disabled</strong>' ); echo ' <a href="' . esc_url( network_admin_url( 'settings.php?page=' . $this->info['slug_prefix'] . '_network_settings' ) ) . '">' . sprintf( esc_html_x( 'Network Admin &rarr; Settings &rarr; %s', 'Link text for Network-Wide settings. %s = plugin name', 'delete-me' ), $this->info['name'] ) . '</a>'; ?>
		</div>
		<?php endif; ?>
		<!-- Stop: Disabled and Ineffective Message -->
		<!-- Start: Network Wide -->
		<?php if ( $network_settings ) : ?>
		<table class="form-table">
			<tr>
				<th scope="row"><input type="submit" class="button-primary" value="<?php printf( esc_attr_x( '%s Network-Wide', '%s = Enable or Disable', 'delete-me' ), ( $option['network'] === false ) ? __( 'Enable', 'delete-me' ) : __( 'Disable', 'delete-me' ) ); ?>" onclick="if ( confirm( '<?php echo sprintf( esc_attr_x( 'WARNING!\n\nALL ROLE SELECTIONS WILL BE LOST, NETWORK AND SITE-SPECIFIC\n\nAre you sure you want to %s Network-Wide?', 'JavaScript confirm for button toggling Network-Wide. %s = enable or disable', 'delete-me' ), ( $option['network'] === false ) ? __( 'Enable', 'delete-me' ) : __( 'Disable', 'delete-me' ) ); ?>' ) ) { this.form.action='<?php echo esc_url( add_query_arg( 'toggle_network', 'true', $settings_uri ) ); ?>'; } else { return false; }" /></th>
				<td>
					<p><strong><?php echo ( $option['network'] === true ) ? '<span style="color: green;">' . __( 'Enabled', 'delete-me' ) . '</span>' : __( 'Enabled', 'delete-me' ); ?></strong>: <?php echo __( 'The settings on this page affect all sites. Site-specific settings are ignored, except for override attributes in shortcodes.', 'delete-me' ); ?></p>
					<p><strong><?php echo ( $option['network'] === false ) ? '<span style="color: red;">' . __( 'Disabled', 'delete-me' ) . '</span>' : __( 'Disabled', 'delete-me' ); ?></strong>: <?php echo __( 'Site-specific settings are used on all sites. Settings on this page are ignored.', 'delete-me' ); ?></p>
				</td>
			</tr>
		</table>
		<?php endif; ?>
		<!-- Stop: Network Wide -->
		<h3><?php echo esc_html__( 'Roles', 'delete-me' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row" style="padding-left: 1em;"><?php echo esc_html__( 'Which roles can delete themselves?', 'delete-me' ); ?></th>
				<td>
				<?php
				
				foreach ( $roles as $role ) {
					
					$disabled = ( $role['name_id'] == 'administrator' || ( $network_settings && $option['network'] === false ) || ( !$network_settings && $network === true ) ) ? ' disabled="disabled"' : '';						
					
					?>
					
					<label>
						<input type="checkbox" name="roles[<?php echo $role['name_id']; ?>]" value="1"<?php echo $role['has_cap'] ? ' checked="checked"' : ''; echo $disabled; ?> />
						<?php
						
						if ( $role['name_id'] == 'administrator' ) {
							
							echo esc_html__( 'Super Admin & Administrator', 'delete-me' );
							
						} else {
							
							echo esc_html( $role['name_text'] );
							
						}
						
						?>
					</label>
					<br />
					
					<?php
					
				}
				
				?>
				<br />
				<div>
					<p class="description">
						<ol style="margin-left: 1em; margin-bottom: 0;">
							<li><?php echo esc_html__( 'Super Admins & Administrators are disabled because those roles should typically not be deleted and they can already delete users in WordPress.', 'delete-me' ); ?></li>
							<li><?php echo esc_html__( 'To test the plugin, you should use a separate WordPress login with a role other than Super Admin & Administrator. That way the delete links you configure are visible to you.', 'delete-me' ); ?></li>
							<?php if ( $network_settings ) echo '<li>' . esc_html__( 'Roles from all network Sites are combined on this page, and the selected roles are allowed to delete themselves across all Sites.', 'delete-me' ) . '</li>'; ?>
						</ol>
					</p>
				</div>
				</td>
			</tr>
		</table>
		<hr>
		<table class="form-table">
			<tr>
				<th scope="row"><h3 style="margin: 0;"><?php echo esc_html__( 'Translatable', 'delete-me' ); ?> <a href="#" style="text-decoration: none;" title="<?php echo esc_attr__( 'Translatable text strings are highlighted below using two colors. These strings can be controlled using translations on Sites available in multiple languages. If a string is "Unchanged" from the default settings, it will use available translations from the languages directory for plugins. A "Changed" string will never use translations.', 'delete-me' ); ?>">[?]</a></h3></th>
				<td>
					<div>
						<div style="float: left; margin: 0 0.5em 0 1em; width: 15px; height: 15px; background-color: #b5e1b9; border: 1px solid #444;"></div>
						<div style="float: left;"><?php echo esc_html__( 'Unchanged', 'delete-me' ); ?></div>
						<div style="float: left; margin: 0 0.5em 0 1em; width: 15px; height: 15px; background-color: #ffe399; border: 1px solid #444;"></div>
						<div style="float: left;"><?php echo esc_html__( 'Changed', 'delete-me' ); ?></div>
						<div style="float: left; margin: 0 1em;">&mdash;</div>
						<div style="float: left;"><a href="<?php echo $this->info['url']; ?>#installation"><?php echo esc_html__( 'Installing Translations', 'delete-me' ); ?></a></div>
					</div>
				</td>
			</tr>
		</table>
		<h3><?php echo esc_html__( 'Your Profile', 'delete-me' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="your_profile_anchor"><?php echo esc_html__( 'Link', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php echo esc_attr__( 'Class & Style are optional. The last box is the clickable content of the link in HTML (e.g. Delete Account &mdash; or &mdash; <img alt="" src="http://www.example.com/image.png" width="100" height="20" />).', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<code>
						&lt;a
						class="<input type="text" name="your_profile_class" class="code" value="<?php echo esc_attr( $option['settings']['your_profile_class'] ); ?>" />"
						style="<input type="text" name="your_profile_style" class="code" value="<?php echo esc_attr( $option['settings']['your_profile_style'] ); ?>" />"&gt;
						<input type="text" id="your_profile_anchor" name="your_profile_anchor" class="code" style="background: <?php echo editable_string_color( $option['settings']['your_profile_anchor'] === $default_option['settings']['your_profile_anchor'] ); ?>;" value="<?php echo esc_attr( $option['settings']['your_profile_anchor'] ); ?>" />
						&lt;/a&gt;
					</code>
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="your_profile_confirm_heading"><?php echo esc_html__( 'Confirm Heading', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php echo esc_attr__( 'Heading, in HTML, used on confirmation page.', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<code>
						&lt;h2&gt;
						<input type="text" id="your_profile_confirm_heading" name="your_profile_confirm_heading" class="code" style="background: <?php echo editable_string_color( $option['settings']['your_profile_confirm_heading'] === $default_option['settings']['your_profile_confirm_heading'] ); ?>;" value="<?php echo esc_attr( $option['settings']['your_profile_confirm_heading'] ); ?>" />
						&lt;/h2&gt;
					</code>
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="your_profile_confirm_warning"><?php echo esc_html__( 'Confirm Warning', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php /* xgettext:no-php-format */ echo esc_attr__( 'Warning, in HTML, used on confirmation page. Use %username% for Username, %displayname% for Display Name, and %sitename% for Site name.', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<input type="text" id="your_profile_confirm_warning" name="your_profile_confirm_warning" class="code large-text" style="background: <?php echo editable_string_color( $option['settings']['your_profile_confirm_warning'] === $default_option['settings']['your_profile_confirm_warning'] ); ?>;" value="<?php echo esc_attr( $option['settings']['your_profile_confirm_warning'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="your_profile_confirm_password_required"><?php echo esc_html__( 'Confirm Password', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php echo esc_attr__( 'Check box to require that users confirm their password on the confirmation page. Label, in HTML, used for the password input box on confirmation page.', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<input type="checkbox" id="your_profile_confirm_password_required" name="your_profile_confirm_password_required" value="1"<?php echo ( $option['settings']['your_profile_confirm_password_required'] == true ) ? ' checked="checked"' : ''; ?> />
					<code>
						&lt;label for="password"&gt;
						<input type="text" id="your_profile_confirm_password_label" name="your_profile_confirm_password_label" class="code" style="background: <?php echo editable_string_color( $option['settings']['your_profile_confirm_password_label'] === $default_option['settings']['your_profile_confirm_password_label'] ); ?>;" value="<?php echo esc_attr( $option['settings']['your_profile_confirm_password_label'] ); ?>" />
						&lt;label&gt;
					</code>
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="your_profile_confirm_button"><?php echo esc_html__( 'Confirm Button', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php /* xgettext:no-php-format */ echo esc_attr__( 'Button text used on confirmation page. Use %username% for Username and %displayname% for Display Name.', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<code>
						&lt;input
						type="submit"
						value="<input type="text" id="your_profile_confirm_button" name="your_profile_confirm_button" class="code" style="background: <?php echo editable_string_color( $option['settings']['your_profile_confirm_button'] === $default_option['settings']['your_profile_confirm_button'] ); ?>;" value="<?php echo esc_attr( $option['settings']['your_profile_confirm_button'] ); ?>" />"
						/&gt;
					</code>
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="your_profile_landing_url"><?php echo esc_html__( 'Landing URL', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php echo esc_attr__( 'Redirect user here after deletion.', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<input type="text" id="your_profile_landing_url" name="your_profile_landing_url" class="code large-text" value="<?php if ( $option['settings']['your_profile_landing_url'] != '' ) echo esc_attr( $option['settings']['your_profile_landing_url'] ); ?>" />
					<code><?php echo esc_html__( 'Leave blank to remain at the same URL after deletion.', 'delete-me' ); ?></code>
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="your_profile_enabled"><?php echo esc_html__( 'Link Enabled', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php echo esc_attr__( 'Check box to show delete link near the bottom of the Your Profile page, uncheck box to hide delete link.', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<input type="checkbox" id="your_profile_enabled" name="your_profile_enabled" value="1"<?php echo ( $option['settings']['your_profile_enabled'] == true ) ? ' checked="checked"' : ''; ?> />
				</td>
			</tr>
		</table>
		<h3><?php echo esc_html__( 'Shortcode', 'delete-me' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="shortcode_anchor"><?php echo esc_html__( 'Link', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php echo esc_attr__( 'Class & Style are optional. The last box is the clickable content of the link in HTML (e.g. Delete Account &mdash; or &mdash; <img alt="" src="http://www.example.com/image.png" width="100" height="20" />).' ); ?>">[?]</a></th>
				<td>
					<code>
						&lt;a
						class="<input type="text" name="shortcode_class" class="code" value="<?php echo esc_attr( $option['settings']['shortcode_class'] ); ?>" />"
						style="<input type="text" name="shortcode_style" class="code" value="<?php echo esc_attr( $option['settings']['shortcode_style'] ); ?>" />"&gt;
						<input type="text" id="shortcode_anchor" name="shortcode_anchor" class="code" style="background: <?php echo editable_string_color( $option['settings']['shortcode_anchor'] === $default_option['settings']['shortcode_anchor'] ); ?>;" value="<?php echo esc_attr( $option['settings']['shortcode_anchor'] ); ?>" />
						&lt;/a&gt;
					</code>
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="shortcode_js_confirm_warning"><?php echo esc_html__( 'JS Confirm Warning', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php /* xgettext:no-php-format */ echo esc_attr__( 'Warning text used for Javascript confirm dialog when using Link, ignored if using Form. Use \n for new lines, %username% for Username, and %displayname% for Display Name.', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<input type="text" id="shortcode_js_confirm_warning" name="shortcode_js_confirm_warning" class="code large-text" style="background: <?php echo editable_string_color( $option['settings']['shortcode_js_confirm_warning'] === $default_option['settings']['shortcode_js_confirm_warning'] ); ?>;" value="<?php echo esc_attr( $option['settings']['shortcode_js_confirm_warning'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="shortcode_js_confirm_enabled"><?php echo esc_html__( 'JS Confirm Enabled', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php echo esc_attr__( 'Check box to use Javascript confirm dialog when using Link, ignored if using Form. Uncheck box for deletion without further confirmation.', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<input type="checkbox" id="shortcode_js_confirm_enabled" name="shortcode_js_confirm_enabled" value="1"<?php echo ( $option['settings']['shortcode_js_confirm_enabled'] == true ) ? ' checked="checked"' : ''; ?> />
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="shortcode_form_enabled"><?php echo esc_html__( 'Use Form Instead of Link', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php /* xgettext:no-php-format */ echo esc_attr__( 'Check box to use the form below instead of the Link. The form also requires users to confirm their password. Uncheck box to use Link configured above. Use %username% for Username and %displayname% for Display Name (1st and 3rd inputs). Use %sitename% for Site name (1st input only). Typical form use would be to place the shortcode on a custom confirmation page you create for account deletion, then link to the confirmation page from somewhere appropriate on your site (e.g. a profile page).', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<input type="checkbox" id="shortcode_form_enabled" name="shortcode_form_enabled" value="1"<?php echo ( $option['settings']['shortcode_form_enabled'] == true ) ? ' checked="checked"' : ''; ?> />
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;">&nbsp;</th>
				<td>
<pre style="margin: 0;"><code>&lt;form id="<?php echo $this->info['trigger']; ?>_shortcode_form" method="post"&gt;
	&lt;p&gt;<input type="text" name="shortcode_form_confirm_warning" class="code" style="width: 50em; background: <?php echo editable_string_color( $option['settings']['shortcode_form_confirm_warning'] === $default_option['settings']['shortcode_form_confirm_warning'] ); ?>;" value="<?php echo esc_attr( $option['settings']['shortcode_form_confirm_warning'] ); ?>" />&lt;p/&gt;
	&lt;p&gt;
		&lt;label for="<?php echo $this->info['trigger']; ?>_shortcode_password"&gt;<input type="text" name="shortcode_form_confirm_password_label" class="code" style="background: <?php echo editable_string_color( $option['settings']['shortcode_form_confirm_password_label'] === $default_option['settings']['shortcode_form_confirm_password_label'] ); ?>;" value="<?php echo esc_attr( $option['settings']['shortcode_form_confirm_password_label'] ); ?>" />&lt;/label&gt;
		&lt;input type="password" autocomplete="off" autofocus="autofocus" id="<?php echo $this->info['trigger']; ?>_shortcode_password" name="<?php echo $this->info['trigger']; ?>_shortcode_password" /&gt;
	&lt;/p&gt;
	&lt;p&gt;&lt;input type="submit" value="<input type="text" name="shortcode_form_confirm_button" class="code" style="background: <?php echo editable_string_color( $option['settings']['shortcode_form_confirm_button'] === $default_option['settings']['shortcode_form_confirm_button'] ); ?>;" value="<?php echo esc_attr( $option['settings']['shortcode_form_confirm_button'] ); ?>" />" /&gt;&lt;/p&gt;
&lt;/form&gt;</code></pre>
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="shortcode_landing_url"><?php echo esc_html__( 'Landing URL', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php echo esc_html__( 'Redirect user here after deletion.', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<input type="text" id="shortcode_landing_url" name="shortcode_landing_url" class="code large-text" value="<?php if ( $option['settings']['shortcode_landing_url'] != '' ) echo esc_attr( $option['settings']['shortcode_landing_url'] ); ?>" />
					<code><?php echo esc_html__( 'Leave blank to remain at the same URL after deletion.', 'delete-me' ); ?></code>
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;"><?php echo esc_html__( 'Usage', 'delete-me' ); ?> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php echo esc_attr__( 'Text inside the Shortcode open and close tags is only served to those who cannot delete themselves, everyone else will be shown the delete link. Attributes may be used to override settings, but are not required.', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<p>
						<code>[<?php echo $this->info['shortcode']; ?> /]</code><br />
						<code>[<?php echo $this->info['shortcode']; ?>]<?php echo esc_html__( 'Text inside Shortcode tags', 'delete-me' ); ?>[/<?php echo $this->info['shortcode']; ?>]</code>
					</p>
					<p>
						<code>&lt;?php echo do_shortcode( '[<?php echo $this->info['shortcode']; ?> /]' ); ?&gt;</code><br />
						<code>&lt;?php echo do_shortcode( '[<?php echo $this->info['shortcode']; ?>]<?php echo esc_html__( 'Text inside Shortcode tags', 'delete-me' ); ?>[/<?php echo $this->info['shortcode']; ?>]' ); ?&gt;</code>
					</p>
					<p>
						<code><?php echo esc_html__( 'Link attributes: class, style, html, js_confirm_warning, landing_url', 'delete-me' ); ?></code>
					</p>
					<p>
						<code><?php echo esc_html__( 'Form attributes: form_confirm_warning, form_password_label, form_confirm_button, landing_url', 'delete-me' ); ?></code>
					</p>
				</td>
			</tr>
		</table>
		<hr>
		<h3><?php echo esc_html__( 'Multisite', 'delete-me' ); ?> <span class="description">( <?php echo is_multisite() ? ( esc_html__( 'Want the same settings across all network sites?', 'delete-me' ) . ' <a href="' . esc_url( network_admin_url( 'settings.php?page=' . $this->info['slug_prefix'] . '_network_settings' ) ) . '">' . sprintf( esc_html_x( 'Network Admin &rarr; Settings &rarr; %s', 'Link text for Network-Wide settings. %s = plugin name', 'delete-me' ), $this->info['name'] ) . '</a>' ) : esc_html__( 'The setting below applies only to WordPress Multisite installations.', 'delete-me' ); ?> )</span></h3>
		<table class="form-table">
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="ms_delete_from_network"><?php echo esc_html__( 'Delete From Network', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php echo esc_attr__( 'Check to delete users from the entire Network. Uncheck to delete users from only the current Site.', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<input type="checkbox" id="ms_delete_from_network" name="ms_delete_from_network" value="1"<?php echo ( $option['settings']['ms_delete_from_network'] == true ) ? ' checked="checked"' : ''; ?> />
				</td>
			</tr>
		</table>
		<h3><?php echo esc_html__( 'Miscellaneous', 'delete-me' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="delete_comments"><?php echo esc_html__( 'Delete Comments', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php echo esc_attr__( 'Delete all comments by users when they delete themselves. IF MULTISITE, only comments on the current Site are deleted, other Network Sites remain unaffected.', 'delete-me' ); ?>">[?]</a></th>
				<td>
					<input type="checkbox" id="delete_comments" name="delete_comments" value="1"<?php echo ( $option['settings']['delete_comments'] == true ) ? ' checked="checked"' : ''; ?> />
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding-left: 1em;"><label for="email_notification"><?php echo esc_html__( 'E-mail Notification', 'delete-me' ); ?></label> <a href="#" onclick="return false;" style="text-decoration: none;" title="<?php printf( esc_attr_x( 'Send a text email with deletion details each time a user deletes themselves using %s. This will go to the site administrator email (i.e. %s), the same email address used for new user notification.', '%s = plugin name, WordPress admin email address', 'delete-me' ), $this->info['name'], get_option( 'admin_email' ) ); ?>">[?]</a></th>
				<td>
					<input type="checkbox" id="email_notification" name="email_notification" value="1"<?php echo ( $option['settings']['email_notification'] == true ) ? ' checked="checked"' : ''; ?> />
				</td>
			</tr>
		</table>
		<p class="submit">
			<?php wp_nonce_field( $form_nonce_action, $form_nonce_name ); ?>
			<input type="submit" class="button-primary" value="<?php echo esc_attr__( 'Save Changes', 'delete-me' ); ?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="button-primary" value="<?php echo esc_attr__( 'Restore Default Settings', 'delete-me' ); ?>" onclick="if ( confirm( '<?php echo esc_attr_x( 'WARNING!\n\nALL CHANGES WILL BE LOST.\n\nAre you sure you want to Restore Default Settings?', 'JavaScript confirm for button that restores default settings', 'delete-me' ); ?>' ) ) { this.form.action='<?php echo esc_url( add_query_arg( 'restore', 'true', $settings_uri ) ); ?>'; } else { return false; }" />
		</p>
	</form>
</div>
