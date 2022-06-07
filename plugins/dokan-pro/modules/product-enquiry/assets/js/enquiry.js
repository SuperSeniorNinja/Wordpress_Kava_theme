(function($){

    $(function(){

        $('#dokan-product-enquiry').on('submit', async function(e) {
            e.preventDefault();

            // Run recaptcha executer
            await dokan_execute_recaptcha( 'form#dokan-product-enquiry .dokan_recaptcha_token', 'dokan_product_enquiry_recaptcha' );

            var self             = $(this),
                data             = self.serialize(),
                recaptcha_token  = self.find('input[name=dokan_product_enquiry_recaptcha_token]'),
                button           = self.find('input[type=submit]');

            var message = $('textarea#dokan-enq-message').val();

            if ( $.trim(message) === '' ) {
                return;
            }

            $('#tab-seller_enquiry_form .alert').remove();
            button.attr('disabled', true);
            self.append('<i class="fas fa-sync-alt fa-spin"></i>');

            $.post(DokanEnquiry.ajaxurl, data, function(resp) {

                if ( typeof resp.data !== 'undefined' ) {

                    if ( resp.success === true ) {

                        $(resp.data).insertBefore(self);
                        self.find('textarea').val('');

                    } else {
                        dokan_sweetalert( resp.data, {
                            icon: 'error',
                        } );
                    }
                }

                recaptcha_token.val('');
                button.removeAttr('disabled');
                self.find('i.fa-spin').remove();
            });

        });

    });

})(jQuery);
