;(function($){
    const Dokan_Auction = {
        init: function () {
            $( '.dokan-section-heading' ).on( 'click', function() {
                $( this ).siblings( '.dokan-section-content' ).slideToggle();
            } );
            $( 'input#_disable_shipping' ).on( 'change', this.disableShippingChange );
            $( '.hide_if_virtual' ).removeClass( 'dokan-hide' );
            $( '.show_if_virtual' ).addClass( 'dokan-hide' );
            $( '.dokan-form-group' ).on( 'change', 'input#_downloadable, input#_virtual', this.handleVirtualDownloadableChange ).trigger( 'change' );
        },
        disableShippingChange: function() {
            $( this ).closest( '.dokan-form-group' ).siblings( '.show_if_needs_shipping' ).slideToggle(600);
        },
        handleVirtualDownloadableChange: function () {
            var is_virtual      = $( 'input#_virtual:checked' ).length;
            let is_downloadable = $( 'input#_downloadable:checked' ).length;

            if ( is_virtual ) {
                $( '.hide_if_virtual' ).addClass( 'dokan-hide' );
                $( '.show_if_virtual' ).removeClass( 'dokan-hide' );
            } else {
                $( '.hide_if_virtual' ).removeClass( 'dokan-hide' );
                $( '.show_if_virtual' ).addClass( 'dokan-hide' );
            }

            if ( is_downloadable ) {
                $( '.auction-product-downloadable' ).removeClass( 'dokan-hide' );
            } else {
                $( '.auction-product-downloadable' ).addClass( 'dokan-hide' );
            }
        },

    };

    $(window).on( 'load', function(){
        Dokan_Auction.init();
    });
})(jQuery);
