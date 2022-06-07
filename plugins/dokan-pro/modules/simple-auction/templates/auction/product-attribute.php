<li class="product-attribute-list <?php echo esc_attr( implode( ' ', $metabox_class ) ); ?>" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">
    <div class="dokan-product-attribute-heading">
        <span><i class="fas fa-bars" aria-hidden="true"></i>&nbsp;&nbsp;<strong><?php echo ! empty( $attribute_label ) ? esc_html( $attribute_label ) : esc_html__( 'Attribute Name', 'dokan' ); ?></strong></span>
        <a href="#" class="dokan-product-remove-attribute"><?php esc_html_e( 'Remove', 'dokan' ); ?></a>
        <a href="#" class="dokan-product-toggle-attribute">
            <i class="fas fa-sort-down fa-flip-horizointal" aria-hidden="true"></i>
        </a>
    </div>

    <div class="dokan-product-attribute-item dokan-clearfix dokan-hide">
        <div class="content-half-part">
            <label class="form-label" for=""><?php esc_html_e( 'Name', 'dokan' ); ?></label>
            <?php if ( $attribute['is_taxonomy'] ) : ?>
                <strong><?php echo esc_html( $attribute_label ); ?></strong>
                <input type="hidden" name="attribute_names[<?php echo $i; ?>]" value="<?php echo esc_attr( $taxonomy ); ?>" />
            <?php else : ?>
                <input type="text" class="attribute_name dokan-form-control dokan-product-attribute-name" name="attribute_names[<?php echo $i; ?>]" value="<?php echo esc_attr( $attribute['name'] ); ?>">
            <?php endif; ?>

            <input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo $position; ?>" />
            <input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="<?php echo $attribute['is_taxonomy'] ? 1 : 0; ?>" />

            <label class="checkbox-item form-label">
                <input type="checkbox" <?php checked( $attribute['is_visible'], 1 ); ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /> <?php esc_html_e( 'Visible on the product page', 'dokan' ); ?>
            </label>
        </div>

        <div class="content-half-part dokan-attribute-values">
            <label for="" class="form-label"><?php esc_html_e( 'Value(s)', 'dokan' ); ?></label>

            <?php if ( $attribute['is_taxonomy'] ) : ?>
                <?php if ( 'select' === $attribute_taxonomy->attribute_type ) : ?>

                    <select multiple="multiple" style="width:100%" data-placeholder="<?php esc_attr_e( 'Select terms', 'dokan' ); ?>" class="dokan_attribute_values dokan-select2" name="attribute_values[<?php echo $i; ?>][]">
                        <?php
                        $args = array(
                            'orderby'    => 'name',
                            'hide_empty' => 0,
                        );
                        $all_terms = get_terms( $taxonomy, apply_filters( 'dokan_product_attribute_terms', $args ) );
                        if ( $all_terms ) {
                            foreach ( $all_terms as $single_term ) {
                                echo '<option value="' . esc_attr( $single_term->slug ) . '" ' . selected( has_term( absint( $single_term->term_id ), $taxonomy, $thepostid ), true, false ) . '>' . esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $single_term->name, $single_term ) ) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <div class="dokan-pre-defined-attribute-btn-group">
                        <button class="dokan-btn dokan-btn-default plus dokan-select-all-attributes"><?php esc_html_e( 'Select all', 'dokan' ); ?></button>
                        <button class="dokan-btn dokan-btn-default minus dokan-select-no-attributes"><?php esc_html_e( 'Select none', 'dokan' ); ?></button>
                    </div>
                <?php elseif ( 'text' === $attribute_taxonomy->attribute_type ) : ?>
                    <select name="attribute_values[<?php echo $i; ?>][]" id="" multiple style="width:100%" class="dokan-select2" data-placeholder="<?php echo esc_attr( sprintf( __( 'Enter some text, or some attributes by "%s" separating values.', 'dokan' ), WC_DELIMITER ) ); ?>" data-tags="true" data-allow-clear="true" data-token-separators="['|']">
                        <?php
                        $attr_val = wp_get_post_terms( $thepostid, $taxonomy, array( 'fields' => 'names' ) );
                        if ( $attr_val ) :
                            ?>
                            <?php foreach ( $attr_val  as $key => $value ) : ?>
                                <option value="<?php echo esc_attr( $value ); ?>" selected><?php echo esc_html( $value ); ?></option>
                            <?php endforeach ?>
                        <?php endif ?>
                    </select>
                <?php endif; ?>

                <?php do_action( 'dokan_auction_product_option_terms', $attribute_taxonomy, $i ); ?>

            <?php else : ?>
                <select name="attribute_values[<?php echo $i; ?>][]" id="" multiple style="width:100%" class="dokan-select2" data-placeholder="<?php echo esc_attr( sprintf( __( 'Enter some text, or some attributes by "%s" separating values.', 'dokan' ), WC_DELIMITER ) ); ?>" data-tags="true" data-allow-clear="true" data-token-separators="['|']" data-values="[ 'Red', 'Green' ]">
                    <?php if ( $attribute['value'] ) : ?>
                        <?php foreach ( explode( WC_DELIMITER, $attribute['value'] )  as $key => $value ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>" selected><?php echo esc_html( $value ); ?></option>
                        <?php endforeach ?>
                    <?php endif ?>
                </select>
            <?php endif; ?>

        </div>
    </div>
</li>
