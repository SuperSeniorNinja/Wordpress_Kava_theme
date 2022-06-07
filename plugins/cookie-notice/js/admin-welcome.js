( function ( $ ) {

	// ready event
	$( function () {
		var btClient = false;
		var btCreditCardsInitialized = false;
		var btPayPalInitialized = false;

		var btInit = function () {
			var result = btInitToken();

			if ( result !== false && btCreditCardsInitialized === false ) {
				// AJAX was successful
				result.done( function ( response ) {
					// token received
					try {
						// parse response
						data = JSON.parse( response );

						// first step, init braintree client
						btClient = braintree.client.create( {
							authorization: data.token
						} );

						btInitPaymentMethod( 'credit_card' );
						// token failed
					} catch ( e ) {
						btGatewayFail( 'btInit catch' );
					}
					// AJAX failed
				} ).fail( function () {
					btGatewayFail( 'btInit AJAX failed' );
				} );
			}
		}

		var btInitToken = function () {
			// payment screen?
			var payment = $( '.cn-sidebar form[data-action="payment"]' );

			// init braintree
			if ( payment.length ) {
				payment.addClass( 'cn-form-disabled' );

				if ( typeof braintree !== 'undefined' ) {
					return $.ajax( {
						url: cnWelcomeArgs.ajaxURL,
						type: 'POST',
						dataType: 'html',
						data: {
							action: 'cn_api_request',
							request: 'get_bt_init_token',
							nonce: cnWelcomeArgs.nonce
						}
					} );
				} else
					return false;
			} else
				return false;
		}

		var btInitPaymentMethod = function ( type ) {
			// console.log( 'btInitPaymentMethod' );

			if ( btClient !== false ) {
				if ( type === 'credit_card' && btCreditCardsInitialized === false ) {
					$( 'form.cn-form[data-action="payment"]' ).addClass( 'cn-form-disabled' );

					btClient.then( btCreditCardsInit ).then( btHostedFieldsInstance ).catch( btGatewayFail );
				} else if ( type === 'paypal' && btPayPalInitialized === false ) {
					$( 'form.cn-form[data-action="payment"]' ).addClass( 'cn-form-disabled' );

					btClient.then( btPaypalCheckoutInit ).then( btPaypalCheckoutSDK ).then( btPaypalCheckoutInstance ).then( btPaypalCheckoutButton ).catch( btGatewayFail );
				}
			} else
				btGatewayFail( 'btInitPaymentMethod btClient is false' );
		}

		var btCreditCardsInit = function ( clientInstance ) {
			// console.log( 'btCreditCardsInit' );

			return braintree.hostedFields.create( {
				client: clientInstance,
				styles: {
					'input': {
						'font-size': '14px',
						'font-family': '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif',
						'color': '#fff'
					},
					':focus': {
						'color': '#fff'
					},
					"::placeholder": {
						'color': '#aaa'
					}
				},
				fields: {
					number: {
						'selector': '#cn_card_number',
						'placeholder': '0000 0000 0000 0000'
					},
					expirationDate: {
						'selector': '#cn_expiration_date',
						'placeholder': 'MM / YY'
					},
					cvv: {
						'selector': '#cn_cvv',
						'placeholder': '123'
					}
				}
			} );
		}

		var btHostedFieldsInstance = function ( hostedFieldsInstance ) {
			// console.log( 'btHostedFieldsInstance' );

			btCreditCardsInitialized = true;

			var form = $( 'form.cn-form[data-action="payment"]' );

			form.removeClass( 'cn-form-disabled' );

			form.on( 'submit', function () {
				if ( form.hasClass( 'cn-payment-in-progress' ) )
					return false;

				form.find( '.cn-form-feedback' ).addClass( 'cn-hidden' );

				// spin the spinner, if exists
				if ( form.find( '.cn-spinner' ).length )
					form.find( '.cn-spinner' ).addClass( 'spin' );

				var invalidForm = false;
				var state = hostedFieldsInstance.getState();

				// check hosted fields
				Object.keys( state.fields ).forEach( function ( field ) {
					if ( !state.fields[field].isValid ) {
						$( state.fields[field].container ).addClass( 'braintree-hosted-fields-invalid' );

						invalidForm = true;
					}
				} );

				if ( invalidForm ) {
					setTimeout( function () {
						cnDisplayError( cnWelcomeArgs.invalidFields, form );

						// spin the spinner, if exists
						if ( form.find( '.cn-spinner' ).length )
							form.find( '.cn-spinner' ).removeClass( 'spin' );
					}, 500 );

					return false;
				}

				hostedFieldsInstance.tokenize( function ( err, payload ) {
					if ( err ) {
						cnDisplayError( cnWelcomeArgs.error );

						return false;
					} else {
						form.addClass( 'cn-payment-in-progress' );
						form.find( 'input[name="payment_nonce"]' ).val( payload.nonce );

						$( '#cn_submit_paid' ).find( '.cn-screen-button[data-screen="4"]' ).trigger( 'click' );
					}
				} );

				return false;
			} );
		}

		var btPaypalCheckoutInit = function ( clientInstance ) {
			// console.log( 'btPaypalCheckoutInit' );

			return braintree.paypalCheckout.create( {
				client: clientInstance
			} );
		}

		var btPaypalCheckoutSDK = function ( paypalCheckoutInstance ) {
			// console.log( 'btPaypalCheckoutSDK' );

			return paypalCheckoutInstance.loadPayPalSDK( {
				vault: true,
				intent: 'tokenize'
			} );
		}

		var btPaypalCheckoutInstance = function ( paypalCheckoutInstance ) {
			// console.log( 'btPaypalCheckoutInstance' );

			var form = $( 'form.cn-form[data-action="payment"]' );

			return paypal.Buttons( {
				fundingSource: paypal.FUNDING.PAYPAL,
				createBillingAgreement: function () {
					// console.log( 'createBillingAgreement' );

					form.addClass( 'cn-form-disabled' );

					return paypalCheckoutInstance.createPayment( {
						flow: 'vault',
						intent: 'tokenize',
						currency: 'EUR'
					} );
				},
				onApprove: function ( data, actions ) {
					// console.log( 'onApprove' );

					return paypalCheckoutInstance.tokenizePayment( data ).then( function ( payload ) {
						form.addClass( 'cn-payment-in-progress' );
						form.find( 'input[name="payment_nonce"]' ).val( payload.nonce );

						// console.log( 'onApprove inside' );
						// console.log( $( '#cn_submit_paid' ).find( '.cn-screen-button[data-screen="4"]' ) );

						$( '#cn_submit_paid' ).find( '.cn-screen-button[data-screen="4"]' ).trigger( 'click' );
					} );
				},
				onCancel: function ( data ) {
					// console.log( 'onCancel' );

					form.removeClass( 'cn-form-disabled' );
				},
				onError: function ( err ) {
					// console.log( 'onError' );

					form.removeClass( 'cn-form-disabled' );
				}
			} ).render( '#cn_paypal_button' );
		}

		var btPaypalCheckoutButton = function () {
			// console.log( 'btPaypalCheckoutButton' );

			btPayPalInitialized = true;

			$( 'form.cn-form[data-action="payment"]' ).removeClass( 'cn-form-disabled' );
		}

		var btGatewayFail = function ( error ) {
			// console.log( 'btGatewayFail' );

			if ( typeof error !== 'undefined' )
				console.log( error );

			cnDisplayError( cnWelcomeArgs.error );
		}

		var cnDisplayError = function ( message, form ) {
			if ( typeof form === 'undefined' )
				form = $( 'form.cn-form[data-action="payment"]' );

			form.find( '.cn-form-feedback' ).html( '<p class="cn-error">' + message + '</p>' ).removeClass( 'cn-hidden' );
		}

		var cnWelcomeScreen = function ( e ) {
			var screen = $( e.target ).data( 'screen' );
			var steps = [ 1, 2, 3, 4 ];
			var sidebars = [ 'login', 'register', 'configure', 'payment' ];

			// continue with screen loading
			var requestData = {
				action: 'cn_welcome_screen',
				nonce: cnWelcomeArgs.nonce
			};

			if ( $.inArray( screen, steps ) != -1 ) {
				var container = $( '.cn-welcome-wrap' );

				requestData.screen = screen;
			} else if ( $.inArray( screen, sidebars ) != -1 ) {
				var container = $( '.cn-sidebar' );

				requestData.screen = screen;
			} else
				return false;

			// add loading overlay
			$( container ).addClass( 'cn-loading' );

			$.ajax( {
				url: cnWelcomeArgs.ajaxURL,
				type: 'POST',
				dataType: 'html',
				data: requestData
			} ).done( function ( response ) {
				$( container ).replaceWith( response );
			} ).fail( function ( jqXHR, textStatus, errorThrown ) {
				//
			} ).always( function ( response ) {
				// remove spinner
				$( container ).removeClass( 'cn-loading' );

				// trigger event
				var event = $.Event( 'screen-loaded' );

				$( document ).trigger( event );
			} );

			return this;
		};

		var cnWelcomeForm = function ( form ) {
			var formAction = $( form[0] ).data( 'action' );
			var formResult = null;
			var formData = {
				action: 'cn_api_request',
				nonce: cnWelcomeArgs.nonce
			};

			// clear feedback
			$( form[0] ).find( '.cn-form-feedback' ).addClass( 'cn-hidden' );

			// build request data
			formData.request = formAction;

			// convert form data to object
			$( form[0] ).serializeArray().map( function ( x ) {
				// exception for checkboxes
				if ( x.name === 'cn_laws' ) {
					var arrayVal = typeof formData[x.name] !== 'undefined' ? formData[x.name] : [ ];

					arrayVal.push( x.value );

					formData[x.name] = arrayVal;
				} else {
					formData[x.name] = x.value;
				}
			} );

			formResult = $.ajax( {
				url: cnWelcomeArgs.ajaxURL,
				type: 'POST',
				dataType: 'json',
				data: formData
			} );

			return formResult;
		};

		// handle screen loading
		$( document ).on( 'click', '.cn-screen-button', function ( e ) {
			var form = $( e.target ).closest( 'form' );
			var result = false;

			// spin the spinner, if exists
			if ( $( e.target ).find( '.cn-spinner' ).length )
				$( e.target ).find( '.cn-spinner' ).addClass( 'spin' );

			// no form?
			if ( form.length === 0 )
				return cnWelcomeScreen( e );

			var formData = { };
			var formDataset = $( form[0] ).data();
			var formAction = formDataset.hasOwnProperty( 'action' ) ? formDataset.action : '';

			// get form data
			$( form[0] ).serializeArray().map( function ( x ) {
				// exception for checkboxes
				if ( x.name === 'cn_laws' ) {
					var arrayVal = typeof formData[x.name] !== 'undefined' ? formData[x.name] : [ ];

					arrayVal.push( x.value );

					formData[x.name] = arrayVal;
				} else {
					formData[x.name] = x.value;
				}
			} );

			// console.log( form[0] );
			// console.log( formData );
			// console.log( formAction );

			// payment?
			if ( formAction === 'payment' ) {
				if ( formData.plan !== 'free' ) {
					// only credit cards
					if ( $( form[0] ).find( 'input[name="payment_nonce"]' ).val() === '' ) {
						form.trigger( 'submit' );

						return false;
					}
				} else {
					// load screen
					cnWelcomeScreen( e );

					return false;
				}
			} else
				e.preventDefault();

			// get form and process it
			result = cnWelcomeForm( form );

			result.done( function ( response ) {
				// error
				if ( response.hasOwnProperty( 'error' ) ) {
					cnDisplayError( response.error, $( form[0] ) );

					return false;
					// message
				} else if ( response.hasOwnProperty( 'message' ) ) {
					cnDisplayError( response.message, $( form[0] ) );

					return false;
					// all good
				} else {
					switch ( formAction ) {
						// logged in, go to success or billing
						case 'login' :
						// register complete, go to success or billing
						case 'register' :
							var accountPlan = formData.hasOwnProperty( 'plan' ) ? formData.plan : 'free';

							// trigger payment
							var accordionItem = $( form[0] ).closest( '.cn-accordion-item' );

							// collapse account
							$( accordionItem ).addClass( 'cn-collapsed cn-disabled' );

							// show billing
							$( accordionItem ).next().removeClass( 'cn-disabled' ).removeClass( 'cn-collapsed' );
							$( accordionItem ).find( 'form' ).removeClass( 'cn-form-disabled' );

							// init braintree after payment screen is loaded via AJAX
							btInit();

							break;

						case 'configure' :
						default :
							// load screen
							cnWelcomeScreen( e );
							break;
					}
				}
			} );

			result.always( function ( response ) {
				if ( $( e.target ).find( '.cn-spinner' ).length )
					$( e.target ).find( '.cn-spinner' ).removeClass( 'spin' );

				// after invalid payment?
				if ( formAction === 'payment' ) {
					$( form[0] ).removeClass( 'cn-payment-in-progress' );
					$( form[0] ).find( 'input[name="payment_nonce"]' ).val( '' );
				}
			} );

			return result;
		} );

		//
		$( document ).on( 'screen-loaded', function () {
			var configureFields = $( '#cn-form-configure' ).serializeArray() || [ ];
			var frame = window.frames[ 'cn_iframe_id' ];

			if ( configureFields.length > 0 ) {
				$( configureFields ).each( function ( index, field ) {
				} );
			}
		} );

		// change payment method
		$( document ).on( 'change', 'input[name="method"]', function () {
			var input = $( this );

			$( '#cn_payment_method_credit_card, #cn_payment_method_paypal' ).toggle();

			input.closest( 'form' ).find( '.cn-form-feedback' ).addClass( 'cn-hidden' );

			// init payment method if needed
			btInitPaymentMethod( input.val() );
		} );

		// 
		$( document ).on( 'click', '.cn-accordion > .cn-accordion-item .cn-accordion-button', function () {
			var accordionItem = $( this ).closest( '.cn-accordion-item' );
			var activeItem = $( this ).closest( '.cn-accordion' ).find( '.cn-accordion-item:not(.cn-collapsed)' );

			if ( $( accordionItem ).hasClass( 'cn-collapsed' ) ) {
				$( activeItem ).addClass( 'cn-collapsed' );
				$( accordionItem ).removeClass( 'cn-collapsed' );
			}

			return false;
		} );

		// live preview
		$( document ).on( 'change', 'input[name="cn_position"]', function () {
			var val = $( this ).val();
			var frame = window.frames['cn_iframe_id'];

			frame.contentWindow.postMessage( { call: 'position', value: val } );
		} );
		
		$( document ).on( 'change', 'input[name="cn_laws"]', function () {
			var val = [ ];

			$( 'input[name="cn_laws"]:checked' ).each( function () {
				val.push( $( this ).val() );
			} );

			var frame = window.frames['cn_iframe_id'];

			frame.contentWindow.postMessage( { call: 'laws', value: val } );
		} );
		
		$( document ).on( 'change', 'input[name="cn_naming"]', function () {
			var val = [ ];

			$( 'input[name="cn_naming"]:checked' ).each( function () {
				val.push( $( this ).val() );
			} );

			var frame = window.frames['cn_iframe_id'];

			frame.contentWindow.postMessage( { call: 'naming', value: val } );
		} );
		
		$( document ).on( 'change', 'input[name="cn_privacy_paper"]', function () {
			var val = $( this ).prop( 'checked' );
			var frame = window.frames['cn_iframe_id'];

			frame.contentWindow.postMessage( { call: 'privacy_paper', value: val } );
		} );

		$( document ).on( 'change', 'input[name="cn_privacy_contact"]', function () {
			var val = $( this ).prop( 'checked' );
			var frame = window.frames['cn_iframe_id'];

			frame.contentWindow.postMessage( { call: 'privacy_contact', value: val } );
		} );

		$( document ).on( 'change', 'input[name="cn_color_primary"]', function () {
			var val = $( this ).val();
			var frame = window.frames['cn_iframe_id'];

			frame.contentWindow.postMessage( { call: 'color_primary', value: val } );
		} );

		$( document ).on( 'change', 'input[name="cn_color_background"]', function () {
			var val = $( this ).val();
			var frame = window.frames['cn_iframe_id'];

			frame.contentWindow.postMessage( { call: 'color_background', value: val } );
		} );

		$( document ).on( 'change', 'input[name="cn_color_border"]', function () {
			var val = $( this ).val();
			var frame = window.frames['cn_iframe_id'];

			frame.contentWindow.postMessage( { call: 'color_border', value: val } );
		} );

		$( document ).on( 'change', 'input[name="cn_color_text"]', function () {
			var val = $( this ).val();
			var frame = window.frames['cn_iframe_id'];

			frame.contentWindow.postMessage( { call: 'color_text', value: val } );
		} );

		$( document ).on( 'change', 'input[name="cn_color_heading"]', function () {
			var val = $( this ).val();
			var frame = window.frames['cn_iframe_id'];

			frame.contentWindow.postMessage( { call: 'color_heading', value: val } );
		} );

		$( document ).on( 'change', 'input[name="cn_color_button_text"]', function () {
			var val = $( this ).val();
			var frame = window.frames['cn_iframe_id'];

			frame.contentWindow.postMessage( { call: 'color_button_text', value: val } );
		} );

		// plan selection
		$( document ).on( 'change', 'input[name="plan"]', function () {
			var availablePlans = [ 'free', 'monthly', 'yearly' ];

			var input = $( this ),
				inputVal = input.val();

			inputVal = availablePlans.indexOf( inputVal ) != -1 ? inputVal : 'free';

			if ( inputVal === 'free' ) {
				$( '#cn_submit_free' ).removeClass( 'cn-hidden' );
				$( '#cn_submit_paid' ).addClass( 'cn-hidden' );
			} else {
				$( '#cn_submit_free' ).addClass( 'cn-hidden' );
				$( '#cn_submit_paid' ).removeClass( 'cn-hidden' );
			}

			$( document ).find( '.cn-pricing-item input[value="' + inputVal + '"' ).prop( 'checked', true );
		} );

		// highlight form
		$( document ).on( 'click', 'input[name="cn_pricing_plan"]', function () {
			$( '.cn-accordion .cn-accordion-item:first-child:not(.cn-collapsed)' ).focus();
		} );

		// select plan
		$( document ).on( 'change', 'input[name="cn_pricing_plan"]', function () {
			var availablePlans = [ 'free', 'monthly', 'yearly' ];

			var input = $( this ),
				inputVal = input.val();

			inputVal = availablePlans.indexOf( inputVal ) != -1 ? inputVal : 'free';

			if ( inputVal === 'free' ) {
				$( '#cn_submit_free' ).removeClass( 'cn-hidden' );
				$( '#cn_submit_paid' ).addClass( 'cn-hidden' );
			} else {
				$( '#cn_submit_free' ).addClass( 'cn-hidden' );
				$( '#cn_submit_paid' ).removeClass( 'cn-hidden' );
			}

			$( document ).find( '#cn_field_plan_' + inputVal ).prop( 'checked', true );


		} );

		// color picker
		initSpectrum();

		// init welcome modal
		if ( cnWelcomeArgs.initModal == true )
			initModal();

	} );

	$( document ).on( 'ajaxComplete', function () {
		// color picker
		initSpectrum();
	} );

	function initSpectrum() {
		$( '.cn-color-picker' ).spectrum( {
			showInput: true,
			showInitial: true,
			allowEmpty: false,
			showAlpha: false
		} );
	}

	function initModal() {
		var progressbar,
			timerId,
			modal = $( "#cn-modal-trigger" );

		if ( modal ) {

			$( "#cn-modal-trigger" ).modaal( {
				content_source: cnWelcomeArgs.ajaxURL + '?action=cn_welcome_screen' + '&nonce=' + cnWelcomeArgs.nonce + '&screen=1',
				type: 'ajax',
				width: 1600,
				custom_class: 'cn-modal',
				// is_locked: true
				ajax_success: function () {
					progressbar = $( document ).find( '.cn-progressbar' );

					if ( progressbar ) {
						timerId = initProgressBar( progressbar );
					}
				},
				before_close: function () {
					clearInterval( timerId );

					var currentStep = $( '.cn-welcome-wrap' );

					// reload if on success screen
					if ( currentStep.length > 0 ) {
						if ( $( currentStep[0] ).hasClass( 'cn-welcome-step-4' ) === true )
							window.location.reload( true );
					}
				},
				after_close: function () {
					progressbar = $( document ).find( '.cn-progressbar' );

					$( progressbar ).progressbar( "destroy" );
				}
			} );

			$( modal ).trigger( 'click' );

			$( document ).on( 'click', '.cn-skip-button', function ( e ) {
				$( '#modaal-close' ).trigger( 'click' );
			} );
		}
	}

	function initProgressBar( progressbar ) {
		var progressbarObj,
			progressLabel = $( document ).find( '.cn-progress-label' ),
			complianceResults = $( document ).find( '.cn-compliance-results' ),
			currentProgress = 0,
			timerId;

		if ( progressbar ) {

			$( document ).on( 'click', '.cn-screen-button', function ( e ) {
				e.preventDefault();

				// console.log( e );

				clearInterval( timerId );
			} );

			$( progressbar ).progressbar( {
				value: 5,
				max: 100,
				create: function ( event, ui ) {
					// console.log( event );

					timerId = setInterval( function () {
						// increment progress bar
						currentProgress += 5;

						// console.log( currentProgress );

						// update progressbar
						progressbar.progressbar( 'value', currentProgress );

						var lastItem = $( complianceResults ).find( 'div:visible' ).last(),
							lastItemText = $( lastItem ).find( '.cn-compliance-status' ).text();

						$( lastItem ).find( '.cn-compliance-status' ).text( lastItemText + ' .' );

						switch ( currentProgress ) {
							case 25:
								$( lastItem ).find( '.cn-compliance-status' ).addClass( 'cn-passed' ).text( cnWelcomeArgs.statusPassed );

								$( lastItem ).next().slideDown( 200 );
								break;
							case 50:
								if ( cnWelcomeArgs.complianceStatus === 'active' ) {
									$( lastItem ).find( '.cn-compliance-status' ).addClass( 'cn-passed' ).text( cnWelcomeArgs.statusPassed );
								} else {
									$( lastItem ).find( '.cn-compliance-status' ).addClass( 'cn-failed' ).text( cnWelcomeArgs.statusFailed );
								}

								$( lastItem ).next().slideDown( 200 );
								break;
							case 75:
								if ( cnWelcomeArgs.complianceStatus === 'active' ) {
									$( lastItem ).find( '.cn-compliance-status' ).addClass( 'cn-passed' ).text( cnWelcomeArgs.statusPassed );
								} else {
									$( lastItem ).find( '.cn-compliance-status' ).addClass( 'cn-failed' ).text( cnWelcomeArgs.statusFailed );
								}

								$( lastItem ).next().slideDown( 200 );
								break;
							case 100:
								if ( cnWelcomeArgs.complianceStatus === 'active' ) {
									$( lastItem ).find( '.cn-compliance-status' ).addClass( 'cn-passed' ).text( cnWelcomeArgs.statusPassed );
								} else {
									$( lastItem ).find( '.cn-compliance-status' ).addClass( 'cn-failed' ).text( cnWelcomeArgs.statusFailed );
								}
								break;
						}

						// complete
						if ( currentProgress >= 100 ) {
							clearInterval( timerId );
						}
					}, 300 );
				},
				change: function ( event, ui ) {
					// console.log( event );

					progressLabel.text( progressbar.progressbar( 'value' ) + '%' );
				},
				complete: function ( event, ui ) {
					// console.log( event );

					setTimeout( function () {
						if ( cnWelcomeArgs.complianceStatus )
							$( '.cn-compliance-check' ).find( '.cn-compliance-feedback' ).html( '<p class="cn-message">' + cnWelcomeArgs.compliancePassed + '</p>' ).removeClass( 'cn-hidden' );
						else
							$( '.cn-compliance-check' ).find( '.cn-compliance-feedback' ).html( '<p class="cn-error">' + cnWelcomeArgs.complianceFailed + '</p>' ).removeClass( 'cn-hidden' );
					}, 500 );

					// $( progressbar ).progressbar( "destroy" );
				}
			} );

			progressbarObj = $( progressbar ).progressbar( "instance" );

			return timerId;
		}
	}

	$( document ).on( 'click', '.cn-run-upgrade, .cn-run-welcome', function ( e ) {
		e.preventDefault();

		// console.log( e );

		// modal
		initModal();
	} );

	$( document ).ready( function () {
		var welcome = false;
		
		welcome = cnGetUrlParam( 'welcome' );
		
		if ( welcome ) {
			// modal
			initModal();
		}
	} );

	$( document ).on( 'click', '.cn-sign-up', function ( e ) {
		e.preventDefault();

		$( '.cn-screen-button' ).trigger( 'click' );
	} );

	var cnGetUrlParam = function cnGetUrlParam( parameter ) {
		var pageURL = window.location.search.substring( 1 ),
			urlVars = pageURL.split( '&' ),
			parameterName,
			i;

		for ( i = 0; i < urlVars.length; i++ ) {
			parameterName = urlVars[i].split( '=' );

			if ( parameterName[0] === parameter ) {
				return typeof parameterName[1] === undefined ? true : decodeURIComponent( parameterName[1] );
			}
		}
		return false;
	};

} )( jQuery );