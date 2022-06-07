( function( $ ) {
    $( '#dokan-shipstation-settings-form' ).on( 'submit', function ( e ) {
        e.preventDefault();

        var form = $( this ),
            form_data = form.serialize() + '&action=dokan_shipstation_settings';

        $.post( dokan.ajaxurl, form_data, function( response ) {
            if ( ! response.success ) {
                dokan_sweetalert( response.data, { 
                    icon: 'error',
                } );
                return;
            }
        } );
    } );
} )( jQuery );
