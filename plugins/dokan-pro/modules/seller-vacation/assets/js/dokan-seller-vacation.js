( function( $ ) {
    dokanSellerVacation.sellerSettings = {
        fieldset: $( '#dokan-seller-vacation-settings' ),
        is_activate: $( '#dokan-seller-vacation-activate' ).is( ':checked' ),
        closing_style: $( 'select', '#dokan-seller-vacation-closing-style' ).val(),
        form_data: {},
        schedules: [],
        date_format: 'yy-mm-dd',
        date_from: null,
        date_to: null,
        from_input: $( '#dokan-seller-vacation-date-from' ),
        to_input: $( '#dokan-seller-vacation-date-to' ),
        message_input: $( '#dokan-seller-vacation-message' ),
        save_button: $( '#dokan-seller-vacation-save-edit' ),
        cancel_button: $( '#dokan-seller-vacation-cancel-edit' ),

        init: function () {
            this.triggers();
            this.actions();
            this.print_list_table();
        },

        triggers: function () {
            var self = this;

            $( '#dokan-seller-vacation-activate' ).on( 'click', function() {
                self.is_activate = $( this ).prop('checked');
                $( 'body' ).trigger( 'dokan:seller_vacation:activate' );
            });

            $( 'select', '#dokan-seller-vacation-closing-style' ).on( 'change', function () {
                self.closing_style = $( this ).val();
                $( 'body' ).trigger( 'dokan:seller_vacation:switch_style' );
            } );

            self.date_from = self.from_input.datepicker( {
                defaultDate: 'today',
                changeMonth: true,
                numberOfMonths: 1,
                dateFormat: self.date_format
            } ).on( 'change', function() {
                self.date_to.datepicker( 'option', 'minDate', self.getDate( this ) );
                self.enable_disable_form_button();
            } );

            self.date_to = self.to_input.datepicker( {
                defaultDate: 'today',
                changeMonth: true,
                numberOfMonths: 1,
                dateFormat: self.date_format
            } ).on( 'change', function() {
                self.date_from.datepicker( 'option', 'maxDate', self.getDate( this ) );
                self.enable_disable_form_button();
            } );

            self.message_input.on( 'input', function () {
                self.enable_disable_form_button();
            } );

            self.save_button.on( 'click', function () {
                try {
                    var from = moment( self.from_input.val() ),
                        to = moment( self.to_input.val() ),
                        message = self.message_input.val();
                    if ( ! from.isValid() ) {
                        throw new Error( dokanSellerVacation.i18n.invalid_from_date );
                    }

                    if ( ! to.isValid() ) {
                        throw new Error( dokanSellerVacation.i18n.invalid_to_date );
                    }

                    if ( ! message ) {
                        throw new Error( dokanSellerVacation.i18n.empty_message );
                    }

                    self.form_data = ( self.form_data.index !== undefined ) ? self.form_data : {};

                    self.form_data.item = {
                        to: to.format( 'YYYY-MM-DD' ),
                        from: from.format( 'YYYY-MM-DD' ),
                        message: message
                    };

                    self.form_is_working();
                    self.save_form_data();
                } catch( error ) {
                    dokan_sweetalert( error.message, { 
                        icon: 'error',
                    } );
                }
            } );

            self.cancel_button.on( 'click', function () {
                self.form_finished_working();
                self.cleanup_form();
                self.enable_disable_form_button();
            } );

            $( '#dokan-seller-vacation-list-table' ).on( 'click', '.dokan-seller-vacation-edit-schedule', function () {
                var index = $( this ).data( 'index' ),
                    item = self.schedules[index];

                self.form_data = {
                    index: index,
                    item: item
                };

                self.from_input.val( item.from );
                self.to_input.val( item.to );
                self.message_input.val( item.message );

                self.enable_disable_form_button();
            } );

            $( '#dokan-seller-vacation-list-table' ).on( 'click', '.dokan-seller-vacation-remove-schedule', async function () {
                const answer = await dokan_sweetalert( dokanSellerVacation.i18n.confirm_delete, { 
                    action : 'confirm', 
                    icon   : 'warning',
                } );

                if ( 'undefined' !== answer && ! answer.isConfirmed ) {
                    return;
                }

                self.fieldset.addClass( 'working' );

                $.ajax( {
                    url: dokan.ajaxurl,
                    method: 'post',
                    data: {
                        action: 'dokan_seller_vacation_delete_item',
                        _wpnonce: dokan.nonce,
                        index: $( this ).data( 'index' ),
                    }
                } ).done( function ( response ) {
                    if ( response.data && response.data.schedules ) {
                        self.refresh_schedule_list( response.data.schedules );
                    }
                } ).fail( function ( jqXHR ) {
                    if ( jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.length ) {
                        dokan_sweetalert( jqXHR.responseJSON.data.pop().message, { 
                            icon: 'error',
                        } );
                    }
                } ).always( function () {
                    self.fieldset.removeClass( 'working' );
                } );
            } );
        },

        actions: function () {
            var self = this;
            
            if ( self.is_activate && 'datewise' !== self.closing_style ) {
                $( '#dokan-seller-vacation-vacation-instant-vacation-message' ).removeClass( 'dokan-hide' );
            }

            $( 'body' ).on( 'dokan:seller_vacation:activate', function () {
                if ( self.is_activate ) {
                    $( '#dokan-seller-vacation-closing-style' ).removeClass( 'dokan-hide' );
                } else {
                    $( '#dokan-seller-vacation-closing-style' ).addClass( 'dokan-hide' );
                }
            } );

            $( 'body' ).on( 'dokan:seller_vacation:activate dokan:seller_vacation:switch_style', function () {
                if ( self.is_activate && 'datewise' === self.closing_style ) {
                    $( '#dokan-seller-vacation-vacation-dates' ).removeClass( 'dokan-hide' );
                } else {
                    $( '#dokan-seller-vacation-vacation-dates' ).addClass( 'dokan-hide' );
                }
            } );

            $( 'body' ).on( 'dokan:seller_vacation:activate', function () {
                if ( ! self.is_activate ) {
                    $( '#dokan-seller-vacation-vacation-instant-vacation-message' ).addClass( 'dokan-hide' );
                } else if ( 'datewise' !== self.closing_style ) {
                    $( '#dokan-seller-vacation-vacation-instant-vacation-message' ).removeClass( 'dokan-hide' );
                }
            } );

            $( 'body' ).on( 'dokan:seller_vacation:switch_style', function () {
                if ( 'datewise' === self.closing_style ) {
                    $( '#dokan-seller-vacation-vacation-instant-vacation-message' ).addClass( 'dokan-hide' );
                } else {
                    $( '#dokan-seller-vacation-vacation-instant-vacation-message' ).removeClass( 'dokan-hide' );
                }
            } );
        },

        getDate: function( element ) {
            var date;

            try {
                date = $.datepicker.parseDate( this.date_format, $( element ).val() );
            } catch( error ) {
                date = null;
            }

            return date;
        },

        print_list_table: function() {
            var rows = '',
                i = 0;

            try {
                this.schedules = JSON.parse( $( '#dokan-seller-vacation-schedules' ).val() );
            } catch( err ) {
                this.schedules = [];
            }

            if ( ! this.schedules.length ) {
                rows = '<tr><td colspan="4">' + dokanSellerVacation.i18n.vacation_date_is_not_set + '</td></tr>';
            } else {
                for ( i = 0; i < this.schedules.length; i++ ) {
                    var schedule = this.schedules[i];

                    rows += '<tr>';
                    rows += '<td class="dokan-seller-vacation-list-from">' + schedule.from + '</td>';
                    rows += '<td class="dokan-seller-vacation-list-to">' + schedule.to + '</td>';
                    rows += '<td class="dokan-seller-vacation-list-message">' + schedule.message + '</td>';
                    rows += '<td class="dokan-seller-vacation-list-action">';
                    rows += '<button type="button" class="dokan-btn dokan-btn-default dokan-btn-sm dokan-seller-vacation-remove-schedule" data-index="' + i + '"><i class="far fa-trash-alt"></i></button>';
                    rows += '<button type="button" class="dokan-btn dokan-btn-default dokan-btn-sm dokan-seller-vacation-edit-schedule" data-index="' + i + '"><i class="fas fa-pencil-alt"></i></button>';
                    rows += '</td>';
                    rows += '</tr>';
                }
            }


            $( 'tbody', '#dokan-seller-vacation-list-table' ).html( rows );
        },

        form_is_working: function () {
            this.fieldset.addClass( 'working' );
            this.fieldset.prop( 'disabled', true );
            this.save_button.prop( 'disabled', true );
            this.save_button.children( 'i' ).removeClass().addClass( 'fa fa-refresh fa-spin' );
            this.save_button.children( 'span' ).text( dokanSellerVacation.i18n.saving + '...' );
            this.cancel_button.prop( 'disabled', true );
        },

        form_finished_working: function () {
            this.fieldset.removeClass( 'working' );
            this.fieldset.prop( 'disabled', false );
            this.save_button.children( 'i' ).removeClass().addClass( 'fa fa-check' );
            this.save_button.children( 'span' ).text( dokanSellerVacation.i18n.save );
            this.enable_disable_form_button();
        },

        cleanup_form: function () {
            this.from_input.val( '' );
            this.to_input.val( '' );
            this.message_input.val( '' );
            this.form_data = {};
        },

        save_form_data: function () {
            var self = this;

            $.ajax( {
                url: dokan.ajaxurl,
                method: 'post',
                data: {
                    action: 'dokan_seller_vacation_save_item',
                    _wpnonce: dokan.nonce,
                    data: self.form_data
                }
            } ).done( function ( response ) {
                self.cleanup_form();

                if ( response.data && response.data.schedules ) {
                    self.refresh_schedule_list( response.data.schedules );
                }

            } ).fail( function ( jqXHR ) {
                if ( jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.length ) {
                    dokan_sweetalert( jqXHR.responseJSON.data.pop().message, { 
                        icon: 'error',
                    } );
                }
            } ).always( function () {
                self.form_finished_working();
            } );
        },

        refresh_schedule_list: function ( schedules ) {
            $( '#dokan-seller-vacation-schedules' ).val( JSON.stringify( schedules ) );
            this.print_list_table();
        },

        enable_disable_form_button: function () {
            var from = this.from_input.val();
            var to = this.to_input.val();
            var message = this.message_input.val();

            this.cancel_button.prop( 'disabled', ! ( from + to + message ) );

            this.save_button.prop( 'disabled', !( from && to && message ) );
        }
    };

    dokanSellerVacation.sellerSettings.init();

    // list table


    // print_vacation_schedules();
} )( jQuery );
