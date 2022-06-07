<div class="dokan-form-group dokan-live-chat-settings">
    <label for="fb_page_id" class="dokan-w3 dokan-control-label">
        <?php esc_html_e( 'Facebook Page ID', 'dokan' ); ?>
    </label>

    <div class="dokan-w5 dokan-text-left">
        <input type="text" id="fb_page_id" name="fb_page_id" value=<?php echo esc_attr( $fb_page_id ); ?>>
        <a
            href="<?php echo esc_url( 'https://www.facebook.com/pages/create/' ); ?>"
            style="font-style: italic;text-decoration: underline !important;color: gray;padding-left: 10px;"
            target="_blank"
            class="get-fb-page-id">
            <?php esc_html_e( 'Get Facebook page id', 'dokan' ); ?>
        </a>
        <p>
            <small>
                <?php esc_html_e( 'Setup Facebook Messenger Chat', 'dokan' ); ?>
                <a
                    href="<?php echo esc_url( 'https://wedevs.com/docs/dokan/modules/dokan-live-chat/' ); ?>"
                    style="text-decoration: underline !important;font-weight: bold;color: gray;"
                    target="_blank"
                    class="get-fb-page-id">
                    <?php esc_html_e( 'Get Help', 'dokan' ); ?>
                </a>
            </small>
        </p>
    </div>
</div>
