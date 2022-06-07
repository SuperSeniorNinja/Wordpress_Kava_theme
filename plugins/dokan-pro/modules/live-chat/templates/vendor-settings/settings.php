<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label"><?php esc_html_e( 'Enable Live Chat' , 'dokan' ) ?></label>
    <div class="dokan-w5 dokan-text-left">
        <div class="checkbox">
            <label>
                <input type="hidden" name="live_chat" value="no">
                <input type="checkbox" id="live_chat" name="live_chat" value="yes" <?php checked( $enable_chat, 'yes' ); ?>><?php  esc_html_e( 'Enable Live Chat', 'dokan'); ?>
            </label>
        </div>
    </div>
</div>
