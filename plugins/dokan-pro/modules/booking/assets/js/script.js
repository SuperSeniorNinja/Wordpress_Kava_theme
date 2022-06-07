// Remove a person type
jQuery( '#bookings_persons' ).on( 'click', 'button.remove_booking_person', async function ( e ) {
    e.preventDefault();
    const answer = await dokan_sweetalert( wc_bookings_writepanel_js_params.i18n_remove_person, { 
        action : 'confirm', 
        icon   : 'warning',
    } );
    
    if ( 'undefined' !== answer && answer.isConfirmed ) {

        var el = jQuery( this ).parent().parent();

        var person = jQuery( this ).attr( 'rel' );

        if ( person > 0 ) {

            jQuery( el ).block( { message: null } );

            var data = {
                action: 'woocommerce_remove_bookable_person',
                person_id: person,
                security: wc_bookings_writepanel_js_params.nonce_delete_person
            };

            jQuery.post( wc_bookings_writepanel_js_params.ajax_url, data, function ( response ) {
                jQuery( el ).fadeOut( '300', function () {
                    jQuery( el ).remove();
                } );
            } );

        } else {
            jQuery( el ).fadeOut( '300', function () {
                jQuery( el ).remove();
            } );
        }

    }
    return false;
} );

jQuery(function($) {

    $('ul.booking-status').on('click', 'a.dokan-edit-status', function(e) {
        $(this).addClass('dokan-hide').closest('li').next('li').removeClass('dokan-hide');

        return false;
    });

    $('ul.booking-status').on('click', 'a.dokan-cancel-status', function(e) {
        $(this).closest('li').addClass('dokan-hide').prev('li').find('a.dokan-edit-status').removeClass('dokan-hide');

        return false;
    });

    $('form#dokan-booking-status-form').on('submit', function(e) {
        e.preventDefault();

        var self = $(this),
            li = self.closest('li');

        li.block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

        $.post( dokan.ajaxurl, self.serialize(), function(response) {
            li.unblock();

            if ( response.success ) {
                var prev_li = li.prev();

                li.addClass('dokan-hide');
                prev_li.find('label').replaceWith(response.data);
                prev_li.find('a.dokan-edit-status').removeClass('dokan-hide');
            } else {
                dokan_sweetalert( response.data, { 
                    icon: 'error',
                } );
            }
        });
    });

    const booking_time = $('.bookings .dokan-booking-time');

    [...booking_time].forEach((row) => {
        let booking_time_range = $(row).data('booking-time');
        dokan_set_booking_day_view(booking_time_range);
    });

    /**
     * Set the booking day view in calender
     */
    function dokan_set_booking_day_view(range) {
        if ( ! range ) {
            return;
        }

        const hours      = range.split(' ');
        const start_time = hours[0];
        const end_time   = hours[1];

        if ( ! start_time || ! end_time ) {
            return;
        }

        const start_time_in_number = parseInt( start_time.slice(0,2) );
        const end_time_in_number   = parseInt( end_time.slice(0,2) );

        let diff = Math.abs( start_time_in_number - end_time_in_number );

        if ( start_time.includes('am')
            && end_time.includes('am')
            || start_time.includes('pm')
            && end_time.includes('pm')
            ) {
            diff = diff;
        } else if ( end_time_in_number < start_time_in_number ) {
            diff += 12;
        }

        let calendar_days = $('.calendar_days .hours label');
        let has_next      = false;
        let index         = 1;

        [...calendar_days].forEach((row) => {
            let hour_row = $(row);
            let time     = hour_row.data('hour').trim();

            if ( start_time === time || has_next ) {
                if ( index === 1 ) {
                    hour_row.parent().addClass('dokan-has-booking dokan-booking-' + time).append( $('.bookings .dokan-booking-' + time) );
                } else {
                    hour_row.parent().addClass('dokan-has-booking has-next');
                }

                has_next = ( diff - index ) >= 1 ? true : false;
                index++;
            }
        });
    }
});
