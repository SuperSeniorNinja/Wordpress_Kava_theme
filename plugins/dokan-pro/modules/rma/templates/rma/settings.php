<?php
/**
 * Dokan Dashbaord Settings SEO Form Template
 *
 * @since 2.4
 *
 * @package dokan
 */
?>

<?php if ( ! empty( $_GET['message'] ) && 'success' == $_GET['message'] ): ?>
    <div class="dokan-alert dokan-alert-success" id="dokan-rma-feedback">
        <?php _e( 'Settings saved successfully', 'dokan' ); ?>
    </div>
<?php endif ?>

<div class="dokan-rma-wrapper">
    <form method="post" id="dokan-store-rma-form" action="" class="dokan-form-horizontal">

        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="dokan-rma-label"><?php _e( 'Label: ', 'dokan' ); ?>
                <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php _e( 'Warrany label what customer will be see', 'dokan' ); ?>">
                    <i class="fas fa-question-circle"></i>
                </span>
            </label>
            <div class="dokan-w5 dokan-text-left">
                <input id="dokan-rma-label" value="<?php echo $rma_settings['label']; ?>" name="warranty_label" placeholder="<?php _e( 'Label', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
            </div>
        </div>

        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="dokan-rma-type"><?php _e( 'Type: ', 'dokan' ); ?>
                <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php _e( 'Warranty and Return Type', 'dokan' ); ?>">
                    <i class="fas fa-question-circle"></i>
                </span>
            </label>
            <div class="dokan-w5 dokan-text-left">
                <select name="warranty_type" id="dokan-warranty-type" class="dokan-form-control">
                    <?php foreach ( dokan_rma_warranty_type() as $warranty_key => $warranty_value ): ?>
                        <option value="<?php echo $warranty_key; ?>" <?php selected( $rma_settings['type'], $warranty_key ); ?>><?php echo $warranty_value; ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>

        <div class="dokan-form-group show_if_included_warranty">
            <label class="dokan-w3 dokan-control-label" for="dokan-rma-type"><?php _e( 'Length: ', 'dokan' ); ?>
                <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php _e( 'Warranty length, How many times( day, weeks month, years ) you want to give warranty ', 'dokan' ); ?>">
                    <i class="fas fa-question-circle"></i>
                </span>
            </label>
            <div class="dokan-w5 dokan-text-left">
                <select name="warranty_length" id="dokan-warranty-length" class="dokan-form-control">
                    <?php foreach ( dokan_rma_warranty_length() as $length_key => $length_value ): ?>
                        <option value="<?php echo $length_key; ?>" <?php selected( $rma_settings['length'], $length_key ); ?>><?php echo $length_value; ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>

        <div class="dokan-form-group hide_if_lifetime show_if_included_warranty">
            <label class="dokan-w3 dokan-control-label" for="dokan-rma-type"><?php _e( 'Length Value: ', 'dokan' ); ?>
                <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php _e( 'Warranty length value', 'dokan' ); ?>">
                    <i class="fas fa-question-circle"></i>
                </span>
            </label>
            <div class="dokan-w5 dokan-text-left">
                <input type="number" class="dokan-form-control" min="0" step="1" name="warranty_length_value" value="<?php echo $rma_settings['length_value']; ?>">
            </div>
        </div>

        <div class="dokan-form-group hide_if_lifetime show_if_included_warranty">
            <label class="dokan-w3 dokan-control-label" for="dokan-warranty-length-duration"><?php _e( 'Length Duration: ', 'dokan' ); ?>
                <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php _e( 'Warranty length, How many times( day, weeks month, years ) you want to give warranty ', 'dokan' ); ?>">
                    <i class="fas fa-question-circle"></i>
                </span>
            </label>
            <div class="dokan-w5 dokan-text-left">
                <select name="warranty_length_duration" id="dokan-warranty-length-duration" class="dokan-form-control">
                    <?php foreach ( dokan_rma_warranty_length_duration() as $length_duration_key => $length_duration_value ): ?>
                        <option value="<?php echo $length_duration_key; ?>" <?php selected( $rma_settings['length_duration'], $length_duration_key ); ?>><?php echo $length_duration_value; ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>

        <?php if ( ! empty( $reasons ) ): ?>

            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="dokan-warranty-length-duration"><?php _e( 'Refund Reasons: ', 'dokan' ); ?>
                    <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php _e( 'Select your return reasonas which will be displayed in customer end', 'dokan' ); ?>">
                        <i class="fas fa-question-circle"></i>
                    </span>
                </label>
                <div class="dokan-w5 dokan-text-left">
                    <?php foreach ( $reasons as $reason_key => $reason_value ): ?>
                        <div class="checkbox">
                            <label for="warranty_reason[<?php echo $reason_key ?>]">
                                <input name="warranty_reason[]" <?php echo in_array( $reason_key, $rma_settings['reasons'] ) ? 'checked' : '' ?> id="warranty_reason[<?php echo $reason_key ?>]" value="<?php echo $reason_key; ?>" type="checkbox"> <?php echo $reason_value; ?>
                            </label>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>

        <?php endif ?>

        <div class="dokan-form-group show_if_addon_warranty">
            <label class="dokan-w3 dokan-control-label" for="dokan-warranty-length-duration"><?php _e( 'Add on Warranty settings: ', 'dokan' ); ?>
                <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php _e( 'Warranty length, How many times( day, weeks month, years ) you want to give warranty ', 'dokan' ); ?>">
                    <i class="fas fa-question-circle"></i>
                </span>
            </label>
            <div class="dokan-w8 dokan-text-left">
                <table class="dokan-table dokan-rma-addon-warranty-table">
                    <thead>
                        <tr>
                            <th><?php _e( 'Cost', 'dokan' ) ?></th>
                            <th><?php _e( 'Duration', 'dokan' ) ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $rma_settings['addon_settings'] ) ): ?>
                            <?php foreach ( $rma_settings['addon_settings'] as $addon_setting ): ?>
                                <tr>
                                    <td>
                                        <div class="dokan-input-group">
                                            <span class="dokan-input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                            <input type="text" name="warranty_addon_price[]" class="wc_input_price dokan-form-control" id="warranty_addon_price[]" value="<?php echo wc_format_localized_price( $addon_setting['price'] ); ?>">
                                        </div>
                                    </td>

                                    <td width="45%">
                                        <input type="number" min="0" step="any" name="warranty_addon_length[]" class="dokan-form-control" id="warranty_addon_length[]" value="<?php echo $addon_setting['length']; ?>">
                                        <select name="warranty_addon_duration[]" id="warranty_addon_duration[]" class="dokan-form-control">
                                            <?php foreach ( dokan_rma_warranty_length_duration() as $length_duration_key => $length_duration_value ): ?>
                                                <option value="<?php echo $length_duration_key; ?>" <?php selected( $addon_setting['duration'], $length_duration_key ); ?>><?php echo $length_duration_value; ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </td>

                                    <td width="20%">
                                        <a href="#" class="dokan-btn dokan-btn-default add-item"><i class="fas fa-plus" aria-hidden="true"></i></a>
                                        <a href="#" class="dokan-btn dokan-btn-default remove-item"><i class="far fa-trash-alt" aria-hidden="true"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php else: ?>
                            <tr>
                                <td>
                                    <div class="dokan-input-group">
                                        <span class="dokan-input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                        <input type="text" name="warranty_addon_price[]" class="wc_input_price dokan-form-control" id="warranty_addon_price[]">
                                    </div>
                                </td>

                                <td width="45%">
                                    <input type="number" min="0" step="any" name="warranty_addon_length[]" class="dokan-form-control" id="warranty_addon_length[]">
                                    <select name="warranty_addon_duration[]" id="warranty_addon_duration[]" class="dokan-form-control">
                                        <?php foreach ( dokan_rma_warranty_length_duration() as $length_duration_key => $length_duration_value ): ?>
                                            <option value="<?php echo $length_duration_key; ?>"><?php echo $length_duration_value; ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </td>

                                <td width="20%">
                                    <a href="#" class="dokan-btn dokan-btn-default add-item"><i class="fas fa-plus" aria-hidden="true"></i></a>
                                    <a href="#" class="dokan-btn dokan-btn-default remove-item"><i class="far fa-trash-alt" aria-hidden="true"></i></a>
                                </td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="dokan-warranty-length-duration"><?php _e( 'RMA Policy: ', 'dokan' ); ?>
                <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php _e( 'Your store return and warranty policy', 'dokan' ); ?>">
                    <i class="fas fa-question-circle"></i>
                </span>
            </label>
            <div class="dokan-w8 dokan-text-left">
                <?php wp_editor( $rma_settings['policy'], 'warranty_policy', [ 'editor_height' => 50, 'quicktags' => false, 'media_buttons' => false, 'teeny' => true, 'editor_class' => 'dokan_warranty_policy' ] ); ?>
            </div>
        </div>

        <?php wp_nonce_field( 'dokan_store_rma_form_action', 'dokan_store_rma_form_nonce' ); ?>

        <div class="dokan-form-group" style="margin-left: 25%">
            <input type="submit" name="dokan_rma_vendor_settings" id='dokan-store-rma-form-submit' class="dokan-left dokan-btn dokan-btn-theme" value="<?php esc_attr_e( 'Save Changes', 'dokan' ); ?>">
        </div>
    </form>
</div>
