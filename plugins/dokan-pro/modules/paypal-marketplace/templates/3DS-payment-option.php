<div class="card_container">
    <div class="form-row address-field validate-postcode form-row-wide">
        <label for="dpm_card_number" class=""><?php esc_html_e( 'Card Number', 'dokan' ); ?></label>
        <div id="dpm_card_number" class="card_field form-group"></div>
    </div>

    <div class="form-row address-field validate-postcode form-row-wide">
        <label for="dpm_card_expiry" class=""><?php esc_html_e( 'Expiration Date', 'dokan' ); ?></label>
        <div id="dpm_card_expiry" class="card_field form-group"></div>
    </div>

    <div class="form-row address-field validate-postcode form-row-wide">
        <label for="dpm_cvv" class=""><?php esc_html_e( 'CVV', 'dokan' ); ?></label>
        <div id="dpm_cvv" class="card_field form-group"></div>
    </div>

    <p class="form-row address-field validate-postcode form-row-wide">
        <label for="dpm_name_on_card" class=""><?php esc_html_e( 'Name on Card', 'dokan' ); ?></label>

        <span class="woocommerce-input-wrapper">
            <input type="text" class="input-text" name="dpm_name_on_card" id="dpm_name_on_card" placeholder="Jhon Smith">
        </span>
    </p>

    <div id="payments-sdk__contingency-lightbox"></div>
</div>
