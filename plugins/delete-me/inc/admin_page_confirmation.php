<?php
// File called by class?
if ( isset( $this ) == false || get_class( $this ) != 'plugin_delete_me' ) exit;

// Enabled?
if ( $this->option['settings']['your_profile_enabled'] == false ) return; // stop executing file

// Does user have the capability?
if ( current_user_can( $this->info['cap'] ) == false || ( is_multisite() && is_super_admin() ) ) return; // stop executing file
$user = wp_get_current_user();

// Temporarily hold default option
$default_option = $this->default_option();
?>
<div class="wrap">
	<h2><?php echo $default_option['settings']['your_profile_confirm_heading'] === $this->option['settings']['your_profile_confirm_heading'] ? __( 'Delete Account', 'delete-me' ) : $this->option['settings']['your_profile_confirm_heading']; ?></h2>
	<form action="<?php echo esc_url( add_query_arg( array( $this->info['trigger'] => $this->user_ID, $this->info['nonce'] => wp_create_nonce( $this->info['nonce'] ) ) ) ); ?>" method="post">
		<p>
			<?php
			// Do not escape Warning or Password label, HTML expected.
			$warning = str_replace( '%username%', $this->user_login, $default_option['settings']['your_profile_confirm_warning'] === $this->option['settings']['your_profile_confirm_warning'] ? /* xgettext:no-php-format */ __( 'WARNING!<br /><br />Are you sure you want to delete user %username% from %sitename%?', 'delete-me' ) : $this->option['settings']['your_profile_confirm_warning'] );
			$warning = str_replace( '%sitename%', get_option( 'blogname' ), $warning );
			$warning = str_replace( '%displayname%', $user->display_name, $warning );
			echo $warning;

			// Confirm Button
			$your_profile_confirm_button = str_replace( '%username%', $this->user_login, $default_option['settings']['your_profile_confirm_button'] === $this->option['settings']['your_profile_confirm_button'] ? __( 'Confirm Deletion', 'delete-me' ) : $this->option['settings']['your_profile_confirm_button'] );
			$your_profile_confirm_button = str_replace( '%displayname%', $user->display_name, $your_profile_confirm_button );
			?>
		</p>
		<?php if ( $this->option['settings']['your_profile_confirm_password_required'] === true ) : ?>
		<table class="form-table">
			<tr class="form-field<?php if ( !empty( $this->POST[$this->info['trigger'] . '_your_profile_confirm_password'] ) ) echo ' form-invalid'; ?>">
				<td style="padding: 0;">
					<label for="<?php echo $this->info['trigger']; ?>_your_profile_confirm_password"><?php echo $default_option['settings']['your_profile_confirm_password_label'] === $this->option['settings']['your_profile_confirm_password_label'] ? __( 'Password', 'delete-me' ) : $this->option['settings']['your_profile_confirm_password_label']; ?></label> <input style="width: 15em;" type="password" autocomplete="off" autofocus id="<?php echo $this->info['trigger']; ?>_your_profile_confirm_password" name="<?php echo $this->info['trigger']; ?>_your_profile_confirm_password" />
				</td>
			</tr>
		</table>
		<?php endif; ?>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php echo esc_attr( $your_profile_confirm_button ); ?>" />
		</p>
	</form>
</div>