<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="woocommerce_variation wc-metabox closed">
	<h3>
		<button type="button" class="remove_variation btn btn-sm btn-danger" rel="<?php echo esc_attr( $variation_id ); ?>"><?php _e( 'Remove', 'dokan' ); ?></button>
		<div class="handlediv" title="<?php _e( 'Click to toggle', 'dokan' ); ?>"></div>
		<strong>#<?php echo esc_html( $variation_id ); ?> &mdash; </strong>
		<?php
			foreach ( $parent_data['attributes'] as $attribute ) {

				// Only deal with attributes that are variations
				if ( ! $attribute['is_variation'] ) {
					continue;
				}

				// Get current value for variation (if set)
				$variation_selected_value = isset( $variation_data[ 'attribute_' . sanitize_title( $attribute['name'] ) ][0] ) ? $variation_data[ 'attribute_' . sanitize_title( $attribute['name'] ) ][0] : '';

				// Name will be something like attribute_pa_color
				echo '<select name="attribute_' . sanitize_title( $attribute['name'] ) . '[' . $loop . ']"><option value="">' . __( 'Any', 'dokan' ) . ' ' . esc_html( wc_attribute_label( $attribute['name'] ) ) . '&hellip;</option>';

				// Get terms for attribute taxonomy or value if its a custom attribute
				if ( $attribute['is_taxonomy'] ) {
					$post_terms = wp_get_post_terms( $parent_data['id'], $attribute['name'] );

					foreach ( $post_terms as $term ) {
						echo '<option ' . selected( $variation_selected_value, $term->slug, false ) . ' value="' . esc_attr( $term->slug ) . '">' . apply_filters( 'woocommerce_variation_option_name', esc_html( $term->name ) ) . '</option>';
					}

				} else {
					$options = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );

					foreach ( $options as $option ) {
						echo '<option ' . selected( sanitize_title( $variation_selected_value ), sanitize_title( $option ), false ) . ' value="' . esc_attr( sanitize_title( $option ) ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
					}

				}

				echo '</select>';
			}
		?>
		<input type="hidden" name="variable_post_id[<?php echo $loop; ?>]" value="<?php echo esc_attr( $variation_id ); ?>" />
		<input type="hidden" class="variation_menu_order" name="variation_menu_order[<?php echo $loop; ?>]" value="<?php echo $loop; ?>" />
	</h3>
	<table cellpadding="0" cellspacing="0" class="woocommerce_variable_attributes wc-metabox-content">
		<tbody>
			<tr>
				<td class="sku" colspan="2">
					<?php if ( wc_product_sku_enabled() ) : ?>
						<input type="text" size="5" name="variable_sku[<?php echo $loop; ?>]" value="<?php if ( isset( $_sku ) ) echo esc_attr( $_sku ); ?>" placeholder="<?php echo esc_attr( $parent_data['sku'] ); ?>" />
						<label><?php esc_html_e( 'SKU', 'dokan' ); ?>: <a class="tips" title="<?php esc_attr_e( 'Enter a SKU for this variation or leave blank to use the parent product SKU.', 'dokan' ); ?>" href="#"><span class="dashicons dashicons-editor-help"></span></a></label>
					<?php else : ?>
						<input type="hidden" name="variable_sku[<?php echo $loop; ?>]" value="<?php if ( isset( $_sku ) ) echo esc_attr( $_sku ); ?>" />
					<?php endif; ?>
				</td>
				<td class="data" rowspan="2">
					<table cellspacing="0" cellpadding="0" class="data_table">
						<?php if ( get_option( 'woocommerce_manage_stock' ) == 'yes' ) : ?>
							<tr class="show_if_variation_manage_stock">
								<td>
									<label><?php esc_html_e( 'Stock Qty:', 'dokan' ); ?> <a class="tips" title="<?php esc_attr_e( 'Enter a quantity to enable stock management at variation level, or leave blank to use the parent product\'s options.', 'dokan' ); ?>" href="#"><span class="dashicons dashicons-editor-help"></span></a></label>
									<input type="number" size="5" name="variable_stock[<?php echo $loop; ?>]" value="<?php if ( isset( $_stock ) ) echo wc_stock_amount( $_stock ); ?>" step="any" />
								</td>
								<td>
									<label><?php _e( 'Allow Backorders?', 'dokan' ); ?></label>
									<select name="variable_backorders[<?php echo $loop; ?>]">
										<option value="no" <?php selected( $_backorders, 'no' ) ?>><?php _e( 'Do not allow', 'dokan' ); ?></option>
										<option value="notify" <?php selected( $_backorders, 'notify' ) ?>><?php _e( 'Allow but notify customer', 'dokan' ); ?></option>
										<option value="yes" <?php selected( $_backorders, 'yes' ) ?>><?php _e( 'Allow', 'dokan' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<label><?php esc_html_e( 'Stock status', 'dokan' ); ?> <a href="#" class="tips" title="<?php esc_attr_e( 'Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'dokan' ) ?>"><span class="dashicons dashicons-editor-help"></span></a></label>
									<select name="variable_stock_status[<?php echo $loop; ?>]">
										<option value="instock" <?php selected( $_stock_status, 'instock' ) ?>><?php _e( 'In stock', 'dokan' ); ?></option>
										<option value="outofstock" <?php selected( $_stock_status, 'outofstock' ) ?>><?php _e( 'Out of stock', 'dokan' ); ?></option>
									</select>
								</td>
							</tr>
						<?php endif; ?>

						<tr class="variable_pricing">
							<td>
								<label><?php echo __( 'Regular Price:', 'dokan' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
								<input type="text" size="5" name="variable_regular_price[<?php echo $loop; ?>]" value="<?php if ( isset( $_regular_price ) ) echo esc_attr( $_regular_price ); ?>" class="wc_input_price" placeholder="<?php _e( 'Variation price (required)', 'dokan' ); ?>" />
							</td>
							<td>
								<label><?php echo __( 'Sale Price:', 'dokan' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?> <a href="#" class="sale_schedule"><?php _e( 'Schedule', 'dokan' ); ?></a><a href="#" class="cancel_sale_schedule" style="display:none"><?php _e( 'Cancel schedule', 'dokan' ); ?></a></label>
								<input type="text" size="5" name="variable_sale_price[<?php echo $loop; ?>]" value="<?php if ( isset( $_sale_price ) ) echo esc_attr( $_sale_price ); ?>" class="wc_input_price" />
							</td>
						</tr>

						<tr class="sale_price_dates_fields" style="display:none">
							<td>
								<label><?php _e( 'Sale start date:', 'dokan' ) ?></label>
								<input type="text" class="sale_price_dates_from" name="variable_sale_price_dates_from[<?php echo $loop; ?>]" value="<?php echo ! empty( $_sale_price_dates_from ) ? date_i18n( 'Y-m-d', $_sale_price_dates_from ) : ''; ?>" placeholder="<?php echo _x( 'From&hellip; YYYY-MM-DD', 'placeholder', 'dokan' ) ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
							</td>
							<td>
								<label><?php _e( 'Sale end date:', 'dokan' ) ?></label>
								<input type="text" name="variable_sale_price_dates_to[<?php echo $loop; ?>]" value="<?php echo ! empty( $_sale_price_dates_to ) ? date_i18n( 'Y-m-d', $_sale_price_dates_to ) : ''; ?>" placeholder="<?php echo _x('To&hellip; YYYY-MM-DD', 'placeholder', 'dokan') ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
							</td>
						</tr>

						<?php if ( wc_product_weight_enabled() || wc_product_dimensions_enabled() ) : ?>
							<tr>
								<?php if ( wc_product_weight_enabled() ) : ?>
									<td class="hide_if_variation_virtual">
										<label><?php echo esc_html( 'Weight', 'dokan' ) . ' (' . esc_html( get_option( 'woocommerce_weight_unit' ) ) . '):'; ?> <a class="tips" title="<?php esc_attr_e( 'Enter a weight for this variation or leave blank to use the parent product weight.', 'dokan' ); ?>" href="#"><span class="dashicons dashicons-editor-help"></span></a></label>
										<input type="text" size="5" name="variable_weight[<?php echo $loop; ?>]" value="<?php if ( isset( $_weight ) ) echo esc_attr( $_weight ); ?>" placeholder="<?php echo esc_attr( $parent_data['weight'] ); ?>" class="wc_input_decimal" />
									</td>
								<?php else : ?>
									<td>&nbsp;</td>
								<?php endif; ?>
								<?php if ( wc_product_dimensions_enabled() ) : ?>
									<td class="dimensions_field hide_if_variation_virtual">
										<label for="product_length"><?php echo __( 'Dimensions (L&times;W&times;H)', 'dokan' ) . ' (' . esc_html( get_option( 'woocommerce_dimension_unit' ) ) . '):'; ?></label>
										<input id="product_length" class="input-text wc_input_decimal" size="6" type="text" name="variable_length[<?php echo $loop; ?>]" value="<?php if ( isset( $_length ) ) echo esc_attr( $_length ); ?>" placeholder="<?php echo esc_attr( $parent_data['length'] ); ?>" />
										<input class="input-text wc_input_decimal" size="6" type="text" name="variable_width[<?php echo $loop; ?>]" value="<?php if ( isset( $_width ) ) echo esc_attr( $_width ); ?>" placeholder="<?php echo esc_attr( $parent_data['width'] ); ?>" />
										<input class="input-text wc_input_decimal last" size="6" type="text" name="variable_height[<?php echo $loop; ?>]" value="<?php if ( isset( $_height ) ) echo esc_attr( $_height ); ?>" placeholder="<?php echo esc_attr( $parent_data['height'] ); ?>" />
									</td>
								<?php else : ?>
									<td>&nbsp;</td>
								<?php endif; ?>
							</tr>
						<?php endif; ?>
						<tr>
							<td><label><?php _e( 'Shipping class:', 'dokan' ); ?></label> <?php
								$args = array(
									'taxonomy' 			=> 'product_shipping_class',
									'hide_empty'		=> 0,
									'show_option_none' 	=> __( 'Same as parent', 'dokan' ),
									'name' 				=> 'variable_shipping_class[' . $loop . ']',
									'id'				=> '',
									'selected'			=> isset( $shipping_class ) ? esc_attr( $shipping_class ) : '',
									'echo'				=> 0
								);

								echo wp_dropdown_categories( $args );
							?></td>
							<td>
								<?php if ( get_option( 'woocommerce_calc_taxes' ) == 'yes' ) : ?>
								<label><?php _e( 'Tax class:', 'dokan' ); ?></label>
								<select name="variable_tax_class[<?php echo $loop; ?>]">
									<option value="parent" <?php selected( is_null( $_tax_class ), true ); ?>><?php _e( 'Same as parent', 'dokan' ); ?></option>
									<?php
									foreach ( $parent_data['tax_class_options'] as $key => $value )
										echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key === $_tax_class, true, false ) . '>' . esc_html( $value ) . '</option>';
								?></select>
								<?php endif; ?>
							</td>
						</tr>
						<tr class="show_if_variation_downloadable" style="display:none">
							<td colspan="2">
								<div class="form-field downloadable_files">
									<label><?php _e( 'Downloadable Files', 'dokan' ); ?>:</label>
									<table class="widefat">
										<thead>
											<tr>
												<td><?php esc_html_e( 'Name', 'dokan' ); ?> <span class="tips" title="<?php esc_attr_e( 'This is the name of the download shown to the customer.', 'dokan' ); ?>"><span class="dashicons dashicons-editor-help"></span></span></td>
												<td colspan="2"><?php esc_html_e( 'File URL', 'dokan' ); ?> <span class="tips" title="<?php esc_attr_e( 'This is the URL or absolute path to the file which customers will get access to.', 'dokan' ); ?>"><span class="dashicons dashicons-editor-help"></span></span></td>
												<td>&nbsp;</td>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th colspan="4">
													<a href="#" class="insert-file-row btn btn-sm btn-success" data-row="<?php
														$file = array(
															'file' => '',
															'name' => ''
														);
														ob_start();
														include( 'html-product-variation-download.php' );
														echo esc_attr( ob_get_clean() );
													?>"><?php _e( 'Add File', 'dokan' ); ?></a>
												</th>
											</tr>
										</tfoot>
										<tbody>
											<?php
											if ( $_downloadable_files ) {
												foreach ( $_downloadable_files as $key => $file ) {
													if ( ! is_array( $file ) ) {
														$file = array(
															'file' => $file,
															'name' => ''
														);
													}
													include( 'html-product-variation-download.php' );
												}
											}
											?>
										</tbody>
									</table>
								</div>
							</td>
						</tr>
						<tr class="show_if_variation_downloadable">
							<td>
								<div>
									<label><?php esc_html_e( 'Download Limit:', 'dokan' ); ?> <a class="tips" title="<?php esc_attr_e( 'Leave blank for unlimited re-downloads.', 'dokan' ); ?>" href="#"><span class="dashicons dashicons-editor-help"></span></a></label>
									<input type="text" size="5" name="variable_download_limit[<?php echo $loop; ?>]" value="<?php if ( isset( $_download_limit ) ) echo esc_attr( $_download_limit ); ?>" placeholder="<?php _e( 'Unlimited', 'dokan' ); ?>" />
								</div>
							</td>
							<td>
								<div>
									<label><?php esc_html_e( 'Download Expiry:', 'dokan' ); ?> <a class="tips" title="<?php esc_attr_e( 'Enter the number of days before a download link expires, or leave blank.', 'dokan' ); ?>" href="#"><span class="dashicons dashicons-editor-help"></span></a></label>
									<input type="text" size="5" name="variable_download_expiry[<?php echo $loop; ?>]" value="<?php if ( isset( $_download_expiry ) ) echo esc_attr( $_download_expiry ); ?>" placeholder="<?php _e( 'Unlimited', 'dokan' ); ?>" />
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div>
					                <p class="dokan-form-group">
					                    <label><?php _e( 'Variation description', 'dokan' ); ?></label>
					                    <textarea class="dokan-form-control" name="variable_description[<?php echo $loop; ?>]" rows="3" style="width:100%;"><?php echo isset( $_variation_description ) ? esc_textarea( $_variation_description ) : ''; ?></textarea>
					                </p>
					            </div>
							</td>
						</tr>
						<?php do_action( 'woocommerce_product_after_variable_attributes', $loop, $variation_data, $variation ); ?>
					</table>
				</td>
			</tr>
			<tr>
				<td class="upload_image">
					<a href="#" class="upload_image_button <?php if ( $image_id > 0 ) echo 'remove'; ?>" rel="<?php echo esc_attr( $variation_id ); ?>"><img src="<?php if ( ! empty( $image ) ) echo esc_attr( $image ); else echo esc_attr( wc_placeholder_img_src() ); ?>" /><input type="hidden" name="upload_image_id[<?php echo $loop; ?>]" class="upload_image_id" value="<?php echo esc_attr( $image_id ); ?>" /><span class="overlay"></span></a>
				</td>
				<td class="options">
					<label class="checkbox"><input type="checkbox" class="checkbox" name="variable_enabled[<?php echo $loop; ?>]" <?php checked( $variation_post_status, 'publish' ); ?> /> <?php _e( 'Enabled', 'dokan' ); ?></label>

					<label class="checkbox"><input type="checkbox" class="checkbox variable_is_downloadable" name="variable_is_downloadable[<?php echo $loop; ?>]" <?php checked( isset( $_downloadable ) ? $_downloadable : '', 'yes' ); ?> /> <?php esc_html_e( 'Downloadable', 'dokan' ); ?> <a class="tips" title="<?php esc_attr_e( 'Enable this option if access will be given to a downloadable file upon the purchase of a product', 'dokan' ); ?>" href="#"><span class="dashicons dashicons-editor-help"></span></a></label>

					<label class="checkbox"><input type="checkbox" class="checkbox variable_is_virtual" name="variable_is_virtual[<?php echo $loop; ?>]" <?php checked( isset( $_virtual ) ? $_virtual : '', 'yes' ); ?> /> <?php esc_html_e( 'Virtual', 'dokan' ); ?> <a class="tips" title="<?php esc_attr_e( 'Enable this option if a product is not shipped or there is no shipping cost', 'dokan' ); ?>" href="#"><span class="dashicons dashicons-editor-help"></span></a></label>

					<label class="checkbox"><input type="checkbox" class="checkbox variable_manage_stock" name="variable_manage_stock[<?php echo $loop; ?>]" <?php checked( isset( $_manage_stock ) ? $_manage_stock : '', 'yes' ); ?> /> <?php esc_html_e( 'Manage Stock?', 'dokan' ); ?> <a class="tips" title="<?php esc_attr_e( 'Enable this option to enable stock management at variation level', 'dokan' ); ?>" href="#"><span class="dashicons dashicons-editor-help"></span></a></label>

					<?php do_action( 'woocommerce_variation_options', $loop, $variation_data, $variation ); ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
