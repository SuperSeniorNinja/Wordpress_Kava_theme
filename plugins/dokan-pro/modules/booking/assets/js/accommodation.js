;(function($){
    const Dokan_Accommodation_Booking = {
        i18n: typeof dokan_accommodation_i18n === 'undefined' ? {} : dokan_accommodation_i18n,
        is_accommodation_checkbox: $( '#_is_dokan_accommodation' ),
        booking_duration_type_wrapper: $( '.wc_booking_duration_type' ),
        booking_enable_range_picker_wrapper: $( '.dokan-booking-enable-range-picker' ),
        booking_custom_block: $( '.show_if_custom_block' ),
        dokan_booking_buffer_wrapper: $( '.dokan-booking-buffer' ),

        min_booking_duration_label: $( "label[for='_wc_booking_min_duration']" ),
        max_booking_duration_label: $( "label[for='_wc_booking_max_duration']" ),

        booking_qty_label: $( "label[for='_wc_booking_qty']" ),

        booking_cost_label: $( "label[for='_wc_booking_cost']" ),
        booking_block_cost: $( "label[for='_wc_booking_block_cost']" ),


        accommodation_checkin_checkout: $( '.dokan-accommodation-checkin-checkout' ),

        trigger_accommodation: function () {
            const self = this;
            this.is_accommodation_checkbox.on( 'change', function() {
                if( this.checked ) {
                    self.booking_duration_type_wrapper.hide();

                    self.accommodation_checkin_checkout.show();
                    self.booking_custom_block.show();

                    self.booking_enable_range_picker_wrapper.hide();

                    self.min_booking_duration_label.html( self.i18n._wc_booking_min_duration.accommodation );
                    self.max_booking_duration_label.html( self.i18n._wc_booking_max_duration.accommodation );

                    self.booking_qty_label.html( self.i18n._wc_booking_qty.accommodation );

                    self.booking_cost_label.text( self.i18n._wc_booking_cost.accommodation );
                    self.booking_block_cost.text( self.i18n._wc_booking_block_cost.accommodation );

                    self.dokan_booking_buffer_wrapper.hide();
                } else {
                    self.booking_duration_type_wrapper.show();

                    self.accommodation_checkin_checkout.hide();
                    self.booking_custom_block.hide();

                    self.booking_enable_range_picker_wrapper.show();

                    self.min_booking_duration_label.html( self.i18n._wc_booking_min_duration.default );
                    self.max_booking_duration_label.html( self.i18n._wc_booking_max_duration.default );

                    self.booking_qty_label.html( self.i18n._wc_booking_qty.default );

                    self.booking_cost_label.text( self.i18n._wc_booking_cost.default );
                    self.booking_block_cost.text( self.i18n._wc_booking_block_cost.default );

                    self.dokan_booking_buffer_wrapper.show();
                }
            } ).trigger( 'change' );
        },
    };

    $( document ).ready( function () {
        Dokan_Accommodation_Booking.trigger_accommodation();

        $( '.dokan-accommodation-timepicker' ).timepicker( {
            timeFormat: dokan_helper.i18n_time_format,
        } );
    } );
})(jQuery);
