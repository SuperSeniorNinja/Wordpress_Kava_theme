<div class="dokan-other-options dokan-edit-row dokan-clearfix">
    <div class="dokan-section-heading" data-togglehandler="dokan_other_options">
        <h2><i class="fas fa-cog" aria-hidden="true"></i> <?php _e( 'Other Options', 'dokan' ); ?></h2>
        <p><?php _e( 'Set your extra product options', 'dokan' ); ?></p>
        <a href="#" class="dokan-section-toggle">
            <i class="fas fa-sort-down fa-flip-vertical" aria-hidden="true"></i>
        </a>
        <div class="dokan-clearfix"></div>
    </div>

    <div class="dokan-section-content">
        <div class="dokan-form-group content-half-part">
            <label for="post_status" class="form-label"><?php _e( 'Product Status', 'dokan' ); ?></label>
            <?php if ( $post_status != 'pending' ) { ?>
                <?php
                $post_statuses = apply_filters( 'dokan_post_status', array(
                    'publish' => __( 'Online', 'dokan' ),
                    'draft'   => __( 'Draft', 'dokan' )
                ), $post );
                ?>

                <select id="post_status" class="dokan-form-control" name="post_status">
                    <?php foreach ( $post_statuses as $status => $label ) { ?>
                        <option value="<?php echo $status; ?>"<?php selected( $post_status, $status ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            <?php } else { ?>
                <?php $pending_class = $post_status == 'pending' ? '  dokan-label dokan-label-warning' : ''; ?>
                <span class="dokan-toggle-selected-display<?php echo $pending_class; ?>"><?php echo dokan_get_post_status( $post_status ); ?></span>
            <?php } ?>
        </div>

        <div class="dokan-form-group content-half-part">
            <label for="_visibility" class="form-label"><?php _e( 'Visibility', 'dokan' ); ?></label>
            <select name="_visibility" id="_visibility" class="dokan-form-control">
                <?php foreach ( $visibility_options as $name => $label ): ?>
                    <option value="<?php echo $name; ?>" <?php selected( $_visibility, $name ); ?>><?php echo $label; ?></option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="dokan-clearfix"></div>

        <div class="dokan-form-group">
            <label for="_purchase_note" class="form-label"><?php _e( 'Purchase Note', 'dokan' ); ?></label>
            <?php dokan_post_input_box( $post_id, '_purchase_note', array( 'placeholder' => __( 'Customer will get this info in their order email', 'dokan' ) ), 'textarea' ); ?>
        </div>

        <div class="dokan-form-group">
            <?php $_enable_reviews = ( $post->comment_status == 'open' ) ? 'yes' : 'no'; ?>
            <?php dokan_post_input_box( $post_id, '_enable_reviews', array( 'value' => $_enable_reviews, 'label' => __( 'Enable product reviews', 'dokan' ) ), 'checkbox' ); ?>
        </div>

    </div>
</div><!-- .dokan-other-options -->


