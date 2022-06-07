<?php
$settings                = $this->get_settings_for_display();
$allowed_tags            = $this->get_allowed_html_tags();
$form_title              = isset( $settings['form_title'] ) ? wp_kses_post( $settings['form_title'] ) : '';
$new_password_label      = isset( $settings['new_password_label'] ) ? wp_kses_post( $settings['new_password_label'] ) : '';
$re_enter_password_label = isset( $settings['re_enter_password_label'] ) ? wp_kses_post( $settings['re_enter_password_label'] ) : '';
$reset_form_text         = isset( $settings['reset_form_text'] ) ? wp_kses_post( $settings['reset_form_text'] ) : '';
$reset_form_text_output  = wpautop( wp_kses( $reset_form_text, $allowed_tags ) );
$minimum_password_length = isset( $settings['minimum_password_length'] ) ? $settings['minimum_password_length'] : 8;
$reset_form_button_text  = isset( $settings['form_button_text'] ) ? wp_kses_post( $settings['form_button_text'] ) : '';
$redirect_page_url       = esc_url( $this->get_success_redirect_url( $settings ) );
$complete_template       = '';
$btn_justify             = 'justify' === $settings['submit_alignment'] ? 'jet-reset__button-full' : '';

if ( get_permalink( 0 ) === $redirect_page_url ) {
	$complete_template = '?password_reset=true';
	$redirect_page_url = $redirect_page_url . $complete_template;
}
?>

<div class="jet-reset">

	<?php if ( ! empty( $errors ) ) : ?>

		<?php if ( is_array( $errors ) ) : ?>

			<?php foreach ( $errors as $error ) : ?>
				<p class="jet-reset__error-message">
					<?php echo $error; ?>
				</p>
			<?php endforeach; ?>

		<?php endif; ?>

	<?php endif; ?>

	<form id="resetpasswordform" class="jet-reset__form" name="resetpasswordform" method="post">

		<input name="jet-reset-success-redirect" id="jet-reset-success-redirect" class="input" type="hidden" value="<?php echo $redirect_page_url;?>">

		<?php if ( ! empty( $form_title ) ):?>

			<legend class="jet-reset__form-title"><?php echo $form_title; ?></legend>

		<?php endif;?>

		<?php if ( ! empty( $reset_form_text_output ) ):?>

			<div class="jet-reset__form-text">

				<?php printf( $reset_form_text_output, $minimum_password_length )?>

			</div>

		<?php endif;?>

		<div class="jet-reset__fields-wrapper">

			<p class="jet-reset__field">
				<label for="jet_reset_new_user_pass"><?php echo $new_password_label; ?></label>

				<?php if ( ! empty( $minimum_password_length ) ) { ?>
					<input name="jet_reset_new_user_pass" id="jet_reset_new_user_pass" class="input" type="password" pattern=".{<?php echo $minimum_password_length; ?>,}" required>
				<?php } else { ?>
					<input name="jet_reset_new_user_pass" id="jet_reset_new_user_pass" class="input" type="password" required>
				<?php } ?>
			</p>

			<p class="jet-reset__field">
				<label for="jet_reset_new_user_pass_again"><?php echo $re_enter_password_label; ?></label>
				<?php if ( ! empty( $minimum_password_length ) ) { ?>
					<input name="jet_reset_new_user_pass_again" id="jet_reset_new_user_pass_again" class="input" type="password" pattern=".{<?php echo $minimum_password_length; ?>,}" required>
				<?php } else { ?>
					<input name="jet_reset_new_user_pass_again" id="jet_reset_new_user_pass_again" class="input" type="password" required>
				<?php } ?>
			</p>

		</div>

		<div class="jet-reset__submit">

			<?php wp_nonce_field( 'jet_reset_pass_reset', 'jet_reset_nonce' ); ?>
			<input type="hidden" name="submitted" id="submitted" value="true">
			<input type="hidden" name="jet_reset_action" id="jet_reset_post_action" value="jet_reset_pass_reset">
			<button type="submit" id="reset-pass-submit" name="reset-pass-submit" class="button button-primary jet-reset__button <?php echo $btn_justify; ?>"><?php echo $reset_form_button_text; ?></button>

		</div>

	</form>

</div>