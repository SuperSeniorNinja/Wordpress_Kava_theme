<div class="dokan-wholesale-options dokan-edit-row dokan-clearfix show_if_simple show_if_external">
    <div class="dokan-section-heading" data-togglehandler="dokan_wholesale_options">
        <h2><i class="fas fa-cart-plus" aria-hidden="true"></i> <?php _e( 'Wholesale Options', 'dokan' ); ?></h2>
        <p><?php _e( 'If you want to sell this product as wholesale then set your setting to the right way', 'dokan' ) ?></p>
        <a href="#" class="dokan-section-toggle">
            <i class="fas fa-sort-down fa-flip-vertical" aria-hidden="true"></i>
        </a>
        <div class="dokan-clearfix"></div>
    </div>

    <div class="dokan-section-content">
        <div class="dokan-form-group">
            <label for="wholesale[enable_wholesale]">
                <input type="hidden" name="wholesale[enable_wholesale]" value="no">
                <input name="wholesale[enable_wholesale]" class="wholesaleCheckbox" id="wholesale[enable_wholesale]" <?php checked( $enable_wholesale, 'yes' ); ?> value="yes" type="checkbox">
                <?php _e( 'Enable wholesale for this product', 'dokan' ); ?>
            </label>
        </div>

        <div class="show_if_wholesale <?php echo 'yes' !== $enable_wholesale ? esc_attr( 'dokan-hide' ) : ''; ?>">
            <div class="dokan-form-group content-half-part" style="padding-right: 10px;">
                <label class="form-label" for="dokan-wholesale-price"><?php _e( 'Wholesale Price: ', 'dokan' ); ?>
                    <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php _e( 'Set your wholesale price', 'dokan' ); ?>">
                        <i class="fas fa-question-circle"></i>
                    </span>
                </label>
                <input type="text" id="dokan-wholesale-price" class="wc_input_price dokan-form-control" name="wholesale[price]" value="<?php echo wc_format_localized_price( $wholesale_price ); ?>">
            </div>

            <div class="dokan-form-group content-half-part">
                <label class="form-label" for="dokan-wholesale-qty"><?php _e( 'Minimum Quantity for Wholesale: ', 'dokan' ); ?>
                    <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php _e( 'Set your minimum quantity for applying wholesale price automatically', 'dokan' ); ?>">
                        <i class="fas fa-question-circle"></i>
                    </span>
                </label>
                <input type="number" class="dokan-form-control" min="0" step="1" name="wholesale[quantity]" value="<?php echo $wholesale_quantity; ?>" id="dokan-wholesale-qty">
            </div>
        </div>

        <div class="dokan-clearfix"></div>
    </div>
</div>
