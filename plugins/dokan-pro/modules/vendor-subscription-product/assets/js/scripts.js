(function( $ ) {
    'use strict';

    $(document).ready(function(){
        $( document.body ).on( 'dokan_variations_added, dokan_variations_loaded', function() {
            $('#product_type').trigger('change');
        } )

        var regularPrice = $('#_regular_price');
        var subscriptionPrice = $('#_subscription_price');

        if ( regularPrice.length && subscriptionPrice.length ) {
            subscriptionPrice.on( 'change', function (){
                var changedPrice = subscriptionPrice.val();
                regularPrice.val( changedPrice );
            });
        }


        $('body').on('dokan-product-type-change', function(e, select_val, el){
            if ( 'variable-subscription' === select_val ) {
                $( 'input#_manage_stock' ).trigger( 'change' );
                $( 'input#_downloadable' ).prop( 'checked', false );
                $( 'input#_virtual' ).removeAttr( 'checked' );
            }

            dokan_show_and_hide_panels();
        });

        $(document).on('change','[name^="_subscription_period"], [name^="_subscription_period_interval"], [name^="variable_subscription_period"], [name^="variable_subscription_period_interval"]',function(){
            setSubscriptionLengths();
            showHideSyncOptions();
            setSyncOptions( $(this) );
        });

        // for sales price calculation and validation and instant type switch support.
        $(document).on('change keyup','[name^="variable_subscription_price"]',function(){
            var changed   = $(this).val(),
                inputName = $(this).attr( 'name' );
                inputName = inputName.replace( '_subscription', '_regular' );
            $( '[name^="' + inputName + '"]' ).val(changed);

        });

        $(document).on('keyup input paste change','[name^="_subscription_trial_length"], [name^="variable_subscription_trial_length"]',function(){
            setTrialPeriods( $(this) );
        });

        $('.product-edit-container').on('change', 'input#_downloadable, input#_virtual', function() {
            dokan_show_and_hide_panels();
        }).trigger( 'change' );

        $( 'input#_downloadable' ).trigger( 'change' );
        $( 'input#_virtual' ).trigger( 'change' );

        $( '.date-picker' ).datepicker({ dateFormat: 'yy-mm-dd' });

        // Validate entire date
        $('.hour, .minute').on( 'change',function(){
            $(this).closest( '.wcs-date-input' ).find('.woocommerce-subscriptions.date-picker').trigger( 'change' );
        });

        $( '.woocommerce-subscriptions.date-picker' ).on( 'change',function(){

            // The date was deleted, clear hour/minute inputs values and set the UTC timestamp to 0
            if( '' == $(this).val() ) {
                $( '#' + $(this).attr( 'id' ) + '_hour' ).val('');
                $( '#' + $(this).attr( 'id' ) + '_minute' ).val('');
                $( '#' + $(this).attr( 'id' ) + '_timestamp_utc' ).val(0);
                return;
            }

            var time_now  = moment(),
            one_hour_from_now = moment().add(1,'hours' ),
            $date_input   = $(this),
            date_type     = $date_input.attr( 'id' ),
            date_pieces   = $date_input.val().split( '-' ),
            $hour_input   = $( '#'+date_type+'_hour' ),
            $minute_input = $( '#'+date_type+'_minute' ),
            chosen_hour   = (0 == $hour_input.val().length) ? one_hour_from_now.format( 'HH' ) : $hour_input.val(),
            chosen_minute = (0 == $minute_input.val().length) ? one_hour_from_now.format( 'mm' ) : $minute_input.val(),
            chosen_date   = moment({
                years:   date_pieces[0],
                months: (date_pieces[1] - 1),
                date:   (date_pieces[2]),
                hours:   chosen_hour,
                minutes: chosen_minute
            });

            $('#'+date_type+'_timestamp_utc').val(chosen_date.unix());

            $( 'body' ).trigger( 'mdsp-updated-date',date_type);
        });

        $( "input.dokan-product-subscription-price" ).on( 'keyup', _.debounce( () => {
            dokan_show_earning_suggestion( function() {

                if ( $( '#product_type' ).val() == 'subscription' || $( '#product_type' ).text() == '' ) {
                    if ( Number( $('.subscription-product span.vendor-price').text() ) < 0  ) {
                        $( $('.dokan-product-less-price-alert').removeClass('dokan-hide') );
                        $( 'input[type=submit]' ).attr( 'disabled', 'disabled' );
                        $( 'button[type=submit]' ).attr( 'disabled', 'disabled' );
                    } else {
                        $( $('.dokan-product-less-price-alert').addClass('dokan-hide') );
                        $( 'input[type=submit]' ).removeAttr( 'disabled');
                        $( 'button[type=submit]' ).removeAttr( 'disabled');
                    }
                }
            } );

        }, 750 ) );

        $('form#dokan-subscription-status-form').on('submit', function(e) {
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
                    alert( response.data );
                }
            });
        });

        $('select#discount_type').on('change', function() {
            var value = $(this).val();

            if ( 'recurring_fee' === value || 'recurring_percent' === value ) {
                $('.dokan-subscription-active-recurring-payment').removeClass('dokan-hide');
            } else {
                $('.dokan-subscription-active-recurring-payment').addClass('dokan-hide');
            }
        });

        $('select#discount_type').trigger('change');
    });

    function dokan_show_and_hide_panels() {
        var product_type    = $( '#product_type' ).val();
        var is_virtual      = $( 'input#_virtual:checked' ).length;
        var is_downloadable = $( 'input#_downloadable:checked' ).length;
        var sale_price_cont = $('.content-width-subscription');

        // Hide/Show all with rules.
        var hide_classes = '.hide_if_downloadable, .hide_if_virtual';
        var show_classes = '.show_if_downloadable, .show_if_virtual';

        $.each( [ 'simple', 'variable', 'grouped', 'subscription', 'variable-subscription' ], function( index, value ) {
            hide_classes = hide_classes + ', .hide_if_' + value;
            show_classes = show_classes + ', .show_if_' + value;
        });
        $( hide_classes ).show();
        $( show_classes ).hide();

        $( '.show_if_' + product_type ).show();
        $( '.hide_if_' + product_type ).hide();

        if( is_downloadable ) {
            $( '.dokan-download-options' ).show();
        }

        sale_price_cont.width('50%');
        if ( 'variable-subscription' === product_type ) {
            $(sale_price_cont).width('100%');
        }

        if ( is_virtual ) {
            $( '.hide_if_virtual' ).hide();
            $( '.show_if_virtual' ).show();
        }
    }

    function setSubscriptionLengths(){
        $('[name^="_subscription_length"], [name^="variable_subscription_length"]').each(function(){
            var $lengthElement = $(this),
            selectedLength = $lengthElement.val(),
            hasSelectedLength = false,
            matches = $lengthElement.attr('name').match(/\[(.*?)\]/),
            periodSelector,
            interval,
            billingInterval;

            if (matches) { // Variation
                periodSelector = '[name="variable_subscription_period['+matches[1]+']"]';
                billingInterval = parseInt($('[name="variable_subscription_period_interval['+matches[1]+']"]').val());
            } else {
                periodSelector = '#_subscription_period';
                billingInterval = parseInt($('#_subscription_period_interval').val());
            }

            $lengthElement.empty();

            $.each(dokanVPS.subscriptionLengths[ $(periodSelector).val() ], function(length,description) {
                if(parseInt(length) == 0 || 0 == (parseInt(length) % billingInterval)) {
                    $lengthElement.append($('<option></option>').attr('value',length).text(description));
                }
            });

            $lengthElement.children('option').each(function(){
                if (this.value == selectedLength) {
                    hasSelectedLength = true;
                    return false;
                }
            });

            if(hasSelectedLength){
                $lengthElement.val(selectedLength);
            } else {
                $lengthElement.val(0);
            }

        });
    }

    function showHideSyncOptions(){
            if($('#_subscription_payment_sync_date').length > 0 || $('.wc_input_subscription_payment_sync').length > 0){
                $('.subscription-sync, .variable-subscription-sync').each(function(){ // loop through all sync field groups
                    var $syncWeekMonthContainer = $(this).find('.subscription_sync_week_month'),
                        $syncWeekMonthSelect = $syncWeekMonthContainer.find('select'),
                        $syncAnnualContainer = $(this).find('.subscription_sync_annual'),
                        $varSubField = $(this).find('[name^="variable_subscription_payment_sync_date"]'),
                        $slideSwitch = false, // stop the general sync field group sliding down if editing a variable subscription
                        billingPeriod;

                    if ($varSubField.length > 0) { // Variation
                        var matches = $varSubField.attr('name').match(/\[(.*?)\]/);
                        var $subscriptionPeriodElement = $('[name="variable_subscription_period['+matches[1]+']"]');
                        if ($('select#product_type').val()=='variable-subscription') {
                            $slideSwitch = true;
                        }
                    } else {
                        $subscriptionPeriodElement = $('#_subscription_period');
                        if ($('select#product_type').val()==dokanVPS.productType) {
                            $slideSwitch = true;
                        }
                    }

                    billingPeriod = $subscriptionPeriodElement.val();

                    if('day'==billingPeriod) {
                        $(this).slideUp(400);
                    } else {
                        if ( $slideSwitch ) {
                            $(this).slideDown(400);
                            if('year'==billingPeriod) {
                                // Make sure the year sync fields are visible
                                $syncAnnualContainer.slideDown(400);
                                // And the week/month field is hidden
                                $syncWeekMonthContainer.slideUp(400);
                            } else {
                                // Make sure the year sync fields are hidden
                                $syncAnnualContainer.slideUp(400);
                                // And the week/month field is visible
                                $syncWeekMonthContainer.slideDown(400);
                            }
                        }
                    }
                });
            }
    }

    function setTrialPeriods( field ) {
        $('[name^="_subscription_trial_length"], [name^="variable_subscription_trial_length"]').each(function(){
            var $trialLengthElement = field,
                trialLength = $trialLengthElement.val(),
                matches = $trialLengthElement.attr('name').match(/\[(.*?)\]/),
                periodStrings, $trialPeriodElement;

            if (matches) { // Variation
                $trialPeriodElement = jQuery('[name="variable_subscription_trial_period['+matches[1]+']"]');
            } else {
                $trialPeriodElement = jQuery('#_subscription_trial_period');
            }

            var selectedTrialPeriod = $trialPeriodElement.val();

            $trialPeriodElement.empty();

            if( parseInt(trialLength) == 1 ) {
                periodStrings = dokanVPS.trialPeriodSingular;
            } else {
                periodStrings = dokanVPS.trialPeriodPlurals;
            }

            $.each(periodStrings, function(key,description) {
                $trialPeriodElement.append($('<option></option>').attr('value',key).text(description));
            });

            $trialPeriodElement.val(selectedTrialPeriod);
        });
    }

    function dokan_show_earning_suggestion( callback ) {
        let commission = $('span.vendor-earning').attr( 'data-commission' );
        let product_id = $( 'span.vendor-earning' ).attr( 'data-product-id' );
        let product_price = $( 'input.dokan-product-subscription-price' ).val();
        let sale_price = 0;
        let earning_suggestion = $('.subscription-product span.vendor-price');

        earning_suggestion.html( 'Calculating' );

        $.get( dokan.ajaxurl, {
            action: 'get_vendor_earning',
            product_id: product_id,
            product_price: sale_price ? sale_price : product_price,
            _wpnonce: dokan.nonce
        } )
        .done( ( response ) => {
            earning_suggestion.html( response );

            if ( typeof callback === 'function' ) {
                callback();
            }
        } );
    }

    function setSyncOptions(periodField) {

            if ( typeof periodField != 'undefined' ) {

                if ($('select#product_type').val()=='variable-subscription') {
                    var $container = periodField.closest('.dokan-product-variation-itmes').find('.variable-subscription-sync');
                } else {
                    $container = periodField.closest('.dokan-product-meta').find('.subscription-sync')
                }

                var $syncWeekMonthContainer = $container.find('.subscription_sync_week_month'),
                    $syncWeekMonthSelect = $syncWeekMonthContainer.find('select'),
                    $syncAnnualContainer = $container.find('.subscription_sync_annual'),
                    $varSubField = $container.find('[name^="variable_subscription_payment_sync_date"]'),
                    billingPeriod;

                if ($varSubField.length > 0) { // Variation
                    var matches = $varSubField.attr('name').match(/\[(.*?)\]/),
                    $subscriptionPeriodElement = $('[name="variable_subscription_period['+matches[1]+']"]');
                } else {
                    $subscriptionPeriodElement = $('#_subscription_period');
                }

                billingPeriod = $subscriptionPeriodElement.val();

                if('day'==billingPeriod) {
                    $syncWeekMonthSelect.val(0);
                    $syncAnnualContainer.find('input[type="number"]').val(0);
                } else {
                    if('year'==billingPeriod) {
                        // Make sure the year sync fields are reset
                        $syncAnnualContainer.find('input[type="number"]').val(0);
                        // And the week/month field has no option selected
                        $syncWeekMonthSelect.val(0);
                    } else {
                        // Make sure the year sync value is 0
                        $syncAnnualContainer.find('input[type="number"]').val(0);
                        // And the week/month field has the appropriate options
                        $syncWeekMonthSelect.empty();
                        $.each(dokanVPS.syncOptions[billingPeriod], function(key,description) {
                            $syncWeekMonthSelect.append($('<option></option>').attr('value',key).text(description));
                        });
                    }
                }
            }
    }

})( jQuery );
