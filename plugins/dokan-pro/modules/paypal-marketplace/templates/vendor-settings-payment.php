<?php echo wc_print_notices(); ?>

<div class="dokan-paypal-marketplace-container">
    <?php if ( $is_seller_enabled ) : ?>
            <p class="dokan-text-left">
                <a class="dokan-btn dokan-btn-danger dokan-btn-theme"
                    href="<?php echo esc_url_raw( $disconnect_url ); ?>"
                ><?php esc_html_e( 'Disconnect', 'dokan' ); ?></a>
            </p>
            <div class="dokan-alert dokan-alert-success dokan-text-left">
                <?php esc_html_e( 'Merchant ID: ', 'dokan' ); echo '<strong>' . $merchant_id . '</strong>'; ?>
            </div>
            <div class="dokan-alert dokan-alert-success dokan-text-left">
                <?php esc_html_e( 'Your account is connected with PayPal Marketplace. You can disconnect your account using the above button.', 'dokan' ); ?>
            </div>
    <?php elseif ( ! empty( $merchant_id ) && empty( $primary_email ) ) : ?>
            <div class="dokan-w12">
                <div class="dokan-alert dokan-alert-warning dokan-text-left" style="margin-top: 15px">
                    <?php esc_html_e( 'Your primary email is not confirmed yet. To receive payment you must need to confirm your PayPal primary email.', 'dokan' ); ?>
                </div>
            </div>
            <p class="dokan-text-left">
                <?php
                $url = add_query_arg(
                    [
						'action'   => 'dokan-paypal-merchant-status-update',
						'_wpnonce' => wp_create_nonce( 'dokan-paypal-merchant-status-update' ),
					]
                );
                ?>
                <a href="<?php echo $url; ?>" class="button button-primary">
                    <?php echo esc_html_e( 'Update', 'dokan' ); ?>
                </a>
            </p>
    <?php else : ?>
            <div class="dokan-form-group">
                <div class="dokan-w8">
                    <input
                        name="settings[paypal][email]"
                        value="<?php echo esc_attr( $email ); ?>"
                        class="dokan-form-control"
                        id="vendor_paypal_email_address"
                        placeholder="<?php esc_html_e( 'Your PayPal email address', 'dokan' ); ?>"
                        type="email"
                        required
                    >
                </div>
                <div class="dokan-w4">
                    <a href="javascript:void(0)" class="button button-primary vendor_paypal_connect">
                        <?php esc_html_e( 'Sign Up' ); ?>
                    </a>
                </div>
            </div>
            <div id="paypal_connect_button"></div>
    <?php endif; ?>
</div>

<?php
if ( $load_connect_js ) :
	?>
<script type="text/javascript">
    ;(function($, document) {
        var paypal_connect = {
            clicked: false,
            load_partner_js: function () {
                (function(d, s, id) {
                   var js, ref = d.getElementsByTagName(s)[0];
                   if (!d.getElementById(id)) {
                       js = d.createElement(s);
                       js.id = id;
                       js.async = true;
                       js.src = "https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js";
                       ref.parentNode.insertBefore(js, ref);
                   }
                }(document, "script", "paypal-js"));
            },
            render_button: function (connect_url) {
                $('#paypal_connect_button').append('<div dir="ltr" style="text-align: left;" trbidi="on">\n' +
                    '        <p class="dokan-text-left">\n' +
                    '            <a\n' +
                    '                data-paypal-button="true"\n' +
                    '                target="PPFrame"\n' +
                    '                href="'+ connect_url +'"\n' +
                    '                id="vendor_paypal_connect"\n' +
                    '                class="button button-primary"\n' +
                    '            >Connect To Paypal</a>\n' +
                    '        </p>\n' +
                    '    </div>');

                $('#paypal_connect_button').hide();
            },
            init: function () {
                $('.vendor_paypal_connect').on('click', function(e) {
                    if (paypal_connect.clicked) {
                        return;
                    }

                    e.preventDefault();

                    if ( typeof $.validate !== "undefined") {
                        var validator = $( "form#payment-form" ).validate();
                        if ( ! validator.element( "#vendor_paypal_email_address" ) ) {
                            return;
                        }
                    }


                    $(this).addClass('disabled');
                    var vendor_email = $('#vendor_paypal_email_address').val();

                    let connect_data = {
                        action: "dokan_paypal_marketplace_connect",
                        vendor_paypal_email_address: vendor_email,
                        nonce: '<?php echo $nonce; ?>'
                    };

                    $.ajax({
                        type: 'POST',
                        url: '<?php echo $ajax_url; ?>',
                        data: connect_data,
                        dataType: 'json',
                    }).done(function(result) {
                        try {
                            if (result.success) {
                                paypal_connect.load_partner_js();
                                paypal_connect.render_button(result.data.url);

                                paypal_connect.clicked = true;

                                setTimeout(function () {
                                    document.getElementById('vendor_paypal_connect').click();
                                }, 3000);
                            } else {
                                throw new Error(result.data.message);
                            }
                        } catch (err) {
                            // Reload page
                            if (result.data.reload === true) {
                                window.location.href = result.data.url;
                                return;
                            }
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                    });
                });
            }

        };

        paypal_connect.init();
    })(jQuery, document);
</script>
<?php endif; ?>
