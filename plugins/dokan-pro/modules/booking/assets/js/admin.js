;(function($){
    const Dokan_Accommodation_Booking_Admin = {
        dokan_accommodation_fields: $( '#dokan_accommodation_fields' ),
        is_accommodation_checkbox: $( '#_is_dokan_accommodation' ),

        trigger_accommodation: function () {
            const self = this;
            this.is_accommodation_checkbox.on( 'change', function() {
                if( this.checked ) {
                    self.dokan_accommodation_fields.show();
                } else {
                    self.dokan_accommodation_fields.hide();
                }
            } ).trigger( 'change' );
        },
    };

    $( document ).ready( function () {
        Dokan_Accommodation_Booking_Admin.trigger_accommodation();
    } );
})(jQuery);
