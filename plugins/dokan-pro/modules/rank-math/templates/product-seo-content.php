<?php do_action( 'dokan_product_edit_before_rank_math_seo', $product_id ); ?>

<style>
    .rank-math-tooltip input {
        display: none !important;
    }

    .rank-math-checklist li {
        padding-left: 24px !important;
    }

    #rank-math-metabox-wrapper .hidden {
        display: none;
    }

    #rank-math-metabox-wrapper .rank-math-tabs .components-panel__body .advanced-robots .components-checkbox-control input {
        width: inherit !important;
    }

    #rank-math-metabox-wrapper .components-notice.is-warning,
    #cmb2-metabox-rank_math_metabox_content_ai .components-base-control__help {
        display: none !important;
    }

    #cmb2-metabox-rank_math_metabox_content_ai div.rank-math-ca-keywords-wrapper .rank-math-ca-credits-wrapper .rank-math-ca-credits .update-credits {
        margin: -8px 8px 0 0;
    }

    #cmb2-metabox-rank_math_metabox_content_ai div.rank-math-ca-keywords-wrapper .rank-math-ca-credits-wrapper .rank-math-ca-credits .update-credits i {
        font-size: 15px;
        margin-top: -1px;
    }

    #cmb2-metabox-rank_math_metabox_content_ai div.rank-math-content-ai-wrapper .rank-math-help-icon {
        font-size: 12px;
        line-height: 14px;
    }

    #rank_math_metabox_content_ai .postbox-header .hndle {
        background: #f7f9fa;
        padding: 5px;
        color: dimgray;
    }

</style>

<div class="dokan-edit-row dokan-clearfix dokan-border-top">
    <div class="dokan-section-heading">
        <h2><i class="fab fa-superpowers" aria-hidden="true"></i> <?php esc_html_e( 'Rank Math SEO', 'dokan' ); ?></h2>
        <p><?php esc_html_e( 'Manage SEO for this product', 'dokan' ); ?></p>
        <a href="#" class="dokan-section-toggle">
            <i class="fas fa-sort-down fa-flip-vertical" aria-hidden="true"></i>
        </a>
        <div class="dokan-clearfix"></div>
    </div>

    <div class="dokan-section-content">
        <input type="hidden" id="post_name" size="13" value="<?php echo esc_attr( $product->post_name ); ?>" />
        <input type="hidden" id="title" size="30" value="<?php echo esc_attr( $product->post_title ); ?>" />
        <div id="rank-math-metabox-wrapper"></div>
    </div>
    <hr>
    <div class="dokan-section-content">
        <div id="rank_math_metabox_content_ai" class="postbox cmb2-postbox">
            <div class="postbox-header">
                <h3 class="hndle"><strong><?php esc_html_e( 'Content AI', 'dokan' ); ?></strong></h3>
            </div>
            <div class="inside">
                <div class="cmb2-wrap form-table">
                    <div id="cmb2-metabox-rank_math_metabox_content_ai" class="cmb2-metabox cmb-field-list"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php do_action( 'dokan_product_edit_after_rank_math_seo', $product_id ); ?>
