<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label"><?php echo esc_html( $settings_label ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <div class="select-box">
            <select class="store-day-selectbox" name="store_day[]">
                <option value=""><?php echo esc_html( $store_day_placeholder ); ?></option>
                <?php
                foreach ( $dokan_days as $day_key => $store_day ) :
                    $working_status = ! empty( $store_info[ $day_key ]['status'] ) ? $store_info[ $day_key ]['status'] : 'close';
                    ?>
                    <option value="<?php echo esc_attr( $day_key ); ?>"
                            data-tag="store-tab-<?php echo esc_attr( $day_key ); ?>"
                        <?php selected( $working_status, 'open' ); ?>>
                        <?php echo esc_html( $store_day ); //phpcs:ignore ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>
<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label"></label>
    <div class="time-slot-tabs">
        <div class="tab-panels">
            <ul class="tabs">
                <?php
                foreach ( $dokan_days as $day_key => $dokan_day ) :
                    $working_status = ! empty( $store_info[ $day_key ]['status'] ) ? $store_info[ $day_key ]['status'] : 'close';

                    if ( empty( $active_day ) && 'open' === $working_status ) {
                        $active_day[] = $day_key;
                    }
                    ?>
                    <li class="<?php echo ( ! empty( $active_day ) && $active_day[0] === $day_key ? 'active' : '' ); ?>" rel="store-tab-<?php echo esc_attr( $day_key ); ?>"><?php echo esc_html( $dokan_day ); ?></li>
                <?php endforeach; ?>
            </ul>

            <?php
            $day_keys   = array_keys( $dokan_days );
            $active_day = ! empty( $active_day ) ? $active_day : $day_keys[0];
            foreach ( $dokan_days as $day_key => $day ) :
                $working_status = ! empty( $store_info[ $day_key ]['status'] ) ? $store_info[ $day_key ]['status'] : 'close';
                ?>
                <div id="store-tab-<?php echo esc_attr( $day_key ); ?>" class="dokan-store-times panel <?php echo ( is_array( $active_day ) && $active_day[0] === $day_key ? 'active' : ( $active_day === $day_key ? 'active' : '' ) ); ?>">
                    <div class="overlay"></div>
                    <div class="dokan-form-group">
                        <!-- Store opening times start -->
                        <label for="opening-time-<?php echo esc_attr( $day_key ); ?>" class="time" >
                            <span class="dokan-control-label start-label" aria-hidden="true"><?php echo esc_html( $label_start ); ?></span>
                            <div class='clock-picker'>
                                <span class="fa fa-clock-o"></span>
                                <input type="text"
                                    class="dokan-form-control opening-time"
                                    name="opening_time[<?php echo esc_attr( $day_key ); ?>][]"
                                    id="opening-time-<?php echo esc_attr( $day_key ); ?>"
                                    placeholder="00:00"
                                    value="<?php echo esc_attr( dokan_get_store_times( $day_key, 'opening_time' ) ); ?>">
                                <span class="fa fa-exclamation-triangle"></span>
                            </div>
                        </label>
                        <!-- Store opening times end -->

                        <span class="time-to"> &#45; </span>

                        <!-- Store closing times start -->
                        <label for="closing-time-<?php echo esc_attr( $day_key ); ?>" class="time" >
                            <span class="dokan-control-label end-label"><?php echo esc_html( $label_end ); ?></span>
                            <div class='clock-picker'>
                                <span class="fa fa-clock-o" aria-hidden="true"></span>
                                <input type="text"
                                    class="dokan-form-control closing-time"
                                    name="closing_time[<?php echo esc_attr( $day_key ); ?>][]"
                                    id="closing-time-<?php echo esc_attr( $day_key ); ?>"
                                    placeholder="00:00"
                                    value="<?php echo esc_attr( dokan_get_store_times( $day_key, 'closing_time' ) ); ?>">
                                <span class="fa fa-exclamation-triangle"></span>
                            </div>
                        </label>
                        <!-- Store closing times end -->

                        <!-- Store times action start -->
                        <label for="open-close-actions" class="time open-close-actions" >
                            <a href="" class="remove-store-closing-time"><span class="fa fa-times" aria-hidden="true"></span></a>
                            <a href="" class="added-store-opening-time"><span class="fa fa-plus" aria-hidden="true"></span></a>
                        </label>
                        <!-- Store times action end -->

                    </div>

                    <?php
                    /**
                     * @since 3.5.0
                     */
                    do_action( 'after_dokan_store_time_settings_form', $day_key, $working_status );
                    ?>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
