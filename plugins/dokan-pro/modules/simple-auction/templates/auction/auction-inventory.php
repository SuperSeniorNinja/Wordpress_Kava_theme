<div class="dokan-product-inventory dokan-edit-row">
    <div class="dokan-section-heading" data-togglehandler="dokan_product_inventory">
        <h2><i class="fas fa-cubes" aria-hidden="true"></i> <?php esc_html_e( 'Inventory', 'dokan' ); ?></h2>
        <p><?php esc_html_e( 'Manage inventory for this product. inventory', 'dokan' ); ?></p>
        <a href="#" class="dokan-section-toggle">
            <i class="fas fa-sort-down fa-flip-vertical" aria-hidden="true"></i>
        </a>
        <div class="dokan-clearfix"></div>
    </div>

    <div class="dokan-section-content">
        <div class="dokan-form-group">
            <label for="_sku" class="form-label"><?php esc_html_e( 'SKU', 'dokan' ); ?> <span><?php esc_html_e( '(Stock Keeping Unit)', 'dokan' ); ?></span></label>
            <?php dokan_post_input_box( $post_id, '_sku' ); ?>
        </div>

        <div class="dokan-clearfix"></div>
    </div>
</div>
