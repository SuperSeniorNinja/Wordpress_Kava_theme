<div class='dokan-form-group'>
    <span class="and-time"></span>

    <!-- Store opening times start -->
    <label for='opening-time-<?php echo esc_attr( $current_day ); ?>' class='time' >
        <div class='clock-picker'>
            <span class="fa fa-clock-o" aria-hidden="true"></span>
            <input type='text' class='dokan-form-control opening-time'
                name='opening_time[<?php echo esc_attr( $current_day ); ?>][]'
                id='opening-time-<?php echo esc_attr( $current_day ); ?>'
                placeholder='<?php echo esc_attr( '00:00' ); ?>'
                value='<?php echo esc_attr( dokan_get_store_times( $current_day, 'opening_time', $index ) ); ?>'
                autocomplete='off'>
            <span class="fa fa-exclamation-triangle"></span>
        </div>
    </label>
    <!-- Store opening times end -->

    <span class='time-to'> &#45; </span>

    <!-- Store closing times start -->
    <label for='closing-time-<?php echo esc_attr( $current_day ); ?>' class='time' >
        <div class='clock-picker'>
            <span class="fa fa-clock-o" aria-hidden="true"></span>
            <input type='text' class='dokan-form-control closing-time'
                name='closing_time[<?php echo esc_attr( $current_day ); ?>][]'
                id='closing-time-<?php echo esc_attr( $current_day ); ?>'
                placeholder='<?php echo esc_attr( '00:00' ); ?>'
                value='<?php echo esc_attr( dokan_get_store_times( $current_day, 'closing_time', $index ) ); ?>'
                autocomplete='off'
            <span class="fa fa-exclamation-triangle"></span>
        </div>
    </label>
    <!-- Store closing times end -->

    <!-- Store times action start -->
    <label for='open-close-actions' class='time open-close-actions'>
        <a href="" class="remove-store-closing-time"><span class="fa fa-times" aria-hidden="true"></span></a>
        <a href="" class="added-store-opening-time"><span class="fa fa-plus" aria-hidden="true"></span></a>
    </label>
    <!-- Store times action end -->
</div>
