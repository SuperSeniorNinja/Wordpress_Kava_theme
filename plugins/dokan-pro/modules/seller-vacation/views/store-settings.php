<fieldset id="dokan-seller-vacation-settings">
    <div class="dokan-form-group goto_vacation_settings">
        <label class="dokan-w3 dokan-control-label" for="setting_go_vacation"><?php _e( 'Go to Vacation', 'dokan' ); ?></label>
        <div class="dokan-w9">
            <div class="checkbox dokan-text-left">
                <label>
                    <input type="hidden" name="setting_go_vacation" value="no">
                    <input type="checkbox" name="setting_go_vacation" id="dokan-seller-vacation-activate" value="yes"<?php checked( $setting_go_vacation, 'yes' ); ?>> <?php _e( 'Want to go vacation by closing our store publically', 'dokan' ); ?>
                </label>
            </div>
        </div>
    </div>
    <div class="dokan-form-group dokan-text-left <?php echo dokan_validate_boolean( $setting_go_vacation ) ? '' : 'dokan-hide'; ?>" id="dokan-seller-vacation-closing-style">
        <label class="dokan-w3 dokan-control-label" for="settings_closing_style"><?php _e( 'Closing Style', 'dokan' ); ?></label>
        <div class="dokan-w5">
            <label>
               <select class="form-control" name="settings_closing_style">
                   <?php foreach ( $closing_style_options as $key => $closing_style_option ): ?>
                        <option value="<?php echo $key; ?>" <?php selected( $key, $settings_closing_style ); ?>><?php echo $closing_style_option; ?></option>
                   <?php endforeach ?>
               </select>
            </label>
        </div>
    </div>
    <div class="dokan-text-left <?php echo $show_schedules ? '' : 'dokan-hide'; ?>" id="dokan-seller-vacation-vacation-dates">
        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label"><?php _e( 'Date Range', 'dokan' ); ?></label>
            <div class="dokan-w6">
                <div class="row">
                    <div class="col-md-6 dokan-seller-vacation-datepickers">
                        <?php _e( 'From', 'dokan' ) ?> <input type="text" class="form-control" id="dokan-seller-vacation-date-from" name="dokan_seller_vacation_datewise_from">
                    </div>
                    <div class="col-md-6 dokan-seller-vacation-datepickers">
                        <?php _e( 'To', 'dokan' ) ?> <input type="text" class="form-control" id="dokan-seller-vacation-date-to" name="dokan_seller_vacation_datewise_to">
                    </div>
                </div>
            </div>
        </div>

        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label"><?php _e( 'Set Vacation Message', 'dokan' ); ?></label>
            <div class="dokan-w6">
                <textarea class="form-control" id="dokan-seller-vacation-message" rows="5" name="dokan_seller_vacation_datewise_message"></textarea>
                <button
                    type="button"
                    class="dokan-btn dokan-btn-default dokan-btn-sm"
                    id="dokan-seller-vacation-save-edit"
                    disabled
                ><i class="fas fa-check"></i> <span><?php _e( 'Save', 'dokan' ); ?></span></button>
                <button
                    type="button"
                    class="dokan-btn dokan-btn-default dokan-btn-sm"
                    id="dokan-seller-vacation-cancel-edit"
                    disabled
                ><i class="fas fa-times"></i> <?php _e( 'Cancel', 'dokan' ); ?></button>
            </div>
        </div>

        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label"><?php _e( 'Vacation List', 'dokan' ); ?></label>

            <div class="dokan-w9">
                <table class="dokan-table dokan-table-striped" id="dokan-seller-vacation-list-table">
                    <thead>
                        <tr>
                            <th class="dokan-seller-vacation-list-from"><?php _e( 'From', 'dokan' ); ?></th>
                            <th class="dokan-seller-vacation-list-to"><?php _e( 'To', 'dokan' ); ?></th>
                            <th class="dokan-seller-vacation-list-message"><?php _e( 'Message', 'dokan' ); ?></th>
                            <th class="dokan-seller-vacation-list-action"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <input type="hidden" id="dokan-seller-vacation-schedules" value="<?php echo esc_attr( json_encode( $seller_vacation_schedules ) ); ?>">
    </div>

    <div class="dokan-text-left <?php echo $show_schedules ? '' : 'dokan-hide'; ?>" id="dokan-seller-vacation-vacation-instant-vacation-message">
        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label"><?php _e( 'Set Vacation Message', 'dokan' ); ?></label>
            <div class="dokan-w6">
                <textarea class="form-control" id="dokan-seller-vacation-message" rows="5" name="setting_vacation_message"><?php echo $setting_vacation_message; ?></textarea>
            </div>
        </div>

    </div>
</fieldset>
