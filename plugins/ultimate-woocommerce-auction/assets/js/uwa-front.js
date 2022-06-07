jQuery(document).ready(function($){
	Uwa_ajax_url = WooUa.ajaxurl;	Utnonce = WooUa.ua_nonce;	
	Uwa_last_activity = WooUa.last_timestamp;
//Ajax query String
	Ajax_qry_str = UWA_Ajax_Qry.ajaqry;
	running = false;
//Interval refresh page	
	var refresh_time_interval = ''; 	
	if(uwa_data.refresh_interval){
	   refresh_time_interval =  setInterval(function(){
	     getLiveStatusAuction();
    	}, uwa_data.refresh_interval*1000);
	}
	//Time Count Down
	$( ".uwa_auction_product_countdown" ).each(function( index ) {
		var time 	= $(this).data('time');
		var format 	= $(this).data('format');
		if(format == ''){
			format = 'yowdHMS';
		}				if(uwa_data.hide_compact == 'yes'){			compact	 = true;		} else{			compact	 = false;		}			
	var etext = '<p>'+uwa_data.expired+'</p>';	
		$(this).WooUacountdown({
			until:   $.WooUacountdown.UTCDate(-(new Date().getTimezoneOffset()),new Date(time*1000)),
			format: format,
			compact:  compact,
			onExpiry: CheckExpired,
			expiryText: etext
		});
	});
	$('form.cart').submit(function() {
		clearInterval(refresh_time_interval);
	});

	$( "input[name=wua_bid_value]" ).on('changein', function( event ) {
	 	$(this).addClass('changein');
	});
/* --------------------------------------------------------
 Add/ Remove Watchlist
----------------------------------------------------------- */
	$( ".uwa-watchlist-action" ).on('click', watchlist);
	function watchlist( event ) {
	var auction_id = jQuery(this).data('auction-id');
	var currentelement  =  $(this);           
		jQuery.ajax({
         type : "get",
         url : Uwa_ajax_url,
         data : { post_id : auction_id, 'uwa-ajax' : "watchlist", 'Utnonce' : Utnonce},
         success: function(response) {
                     currentelement.parent().replaceWith(response);
                     $( ".uwa-watchlist-action" ).on('click', watchlist);
                     jQuery( document.body).trigger('uwa-watchlist-action',[response,auction_id] );
        	}
      	});
	}
/* --------------------------------------------------------
Send Private Message
----------------------------------------------------------- */
	$( document ).on( 'click', 'button#uwa_private_send', function() {
		var error = 0;
		var thisObj = $(this);
		var private_msg_form = $('#uwa_private_msg_form');
		
		// collect the data
		var firstnameObj	= private_msg_form.find( '.uwa_pri_name' );
		var firstname 		= firstnameObj.val();
		var emailObj 		= private_msg_form.find( '.uwa_pri_email' );
		var email 			= emailObj.val();
		var messageObj 		= private_msg_form.find( '.uwa_pri_message' );
		var message 		= messageObj.val();
		var product_idObj 		= private_msg_form.find( '.uwa_pri_product_id' );
		var product_id 		= product_idObj.val();
		if( error == 0 ) {
			// Hide / show for ajax loader
			thisObj.hide();
			private_msg_form.find( 'img.uwa_private_msg_ajax_loader' ).show();	
			var data = {
						action: 	'send_private_message_process',
						firstname: 		firstname,
						email: 		    email,
						message: 		message,
						product_id: 	product_id,						Utnonce : Utnonce
					}
				$.post( Uwa_ajax_url, data, function(response) {
				var data = $.parseJSON( response );                       
				if( data.status == 0 ) {
					if (data.error_name) {
						private_msg_form.find( '#error_fname' ).html( data.error_name );
					}else {
						private_msg_form.find( '#error_fname' ).html( "" );
					}
					if (data.error_email) {
						private_msg_form.find( '#error_email' ).html( data.error_email );
					}else {
						private_msg_form.find( '#error_email' ).html( "" );
					}
					if (data.error_message) {
						private_msg_form.find( '#error_message' ).html( data.error_message );
					}else {
						private_msg_form.find( '#error_message' ).html( "" );
					}
				} else {
					private_msg_form.find( '#error_message' ).html( "" );
					private_msg_form.find( '#error_fname' ).html( "" );
					private_msg_form.find( '#error_email' ).html( "" );
					private_msg_form.find( '#uwa_private_msg_success' ).html( data.success_message );
				}
				// Hide / show for ajax loader
				thisObj.show();
				private_msg_form.find( 'img.uwa_private_msg_ajax_loader' ).hide();
			});
		}
		return false;
	});
	//  CheckExpired(); /* no need for this */
});
function CheckExpired(){
		var auction_id = jQuery(this).data('auction-id');
		var auction_product_container = jQuery(this).parent().next('.uwa_auction_product_ajax_change'); 		auction_product_container.empty().prepend('<div class="ajax-loading"></div>');		auction_product_container.parent().children('form.buy-now').remove();
		var ajaxurl = Ajax_qry_str+'=expired_auction';
		request =  jQuery.ajax({
         type : "post",
         url : ajaxurl,
         cache : false,
         data : {action: "expired_auction", post_id : auction_id, ret: auction_product_container.length,Utnonce : Utnonce},
         success: function(response) {
         			if (response.length  != 0){ 						auction_product_container.children('.ajax-loading').remove();
         				auction_product_container.prepend(response);         				
         			}
        	}
      	});
}

function getLiveStatusAuction () {
	
	 if(jQuery('.woo-ua-auction-price').length<1){
        return;
     }
	if (running == true){
    	return;
    }
	running = true;
	var ajaxurl = Ajax_qry_str+'=get_live_stutus_auction'; 
		jQuery.ajax({
		type : "post",
		encoding:"UTF-8",
		url : ajaxurl,
		dataType: 'json',
		data : {action: "get_live_stutus_auction", "last_timestamp" : Uwa_last_activity},
		success: function(response) {
			if(response != null ) {			
				if (typeof response.last_timestamp != 'undefined') {
					Uwa_last_activity = response.last_timestamp;
				}
				jQuery.each( response, function( key, value ) {					  auction = jQuery("body").find(".woo-ua-auction-price[data-auction-id='" + key + "']");				  auction.replaceWith(value.wua_curent_bid);				if (typeof value.wua_curent_bid != 'undefined' ) {					jQuery( ".wua_curent_bid" ).html(value.wua_curent_bid);				}
				  
				//countdown timer
				if (typeof value.wua_timer != 'undefined') {
					var curenttimer = jQuery("body").find(".uwa_auction_product_countdown[data-auctionid='" + key + "']");
						if(curenttimer.attr('data-time') != value.wua_timer){
							curenttimer.attr('data-time',value.wua_timer );
						}
				}								if (typeof value.wua_current_bider != 'undefined' ) {											var currentuser = jQuery("input[name=user_id]");						var mainauction = jQuery("input[name=uwa-place-bid]").val();						if (currentuser.length){							if(value.wua_current_bider != currentuser.val() && mainauction == key ){								jQuery('.woocommerce-message').replaceWith(uwa_data.outbid_message );							}						}						if(jQuery( "span.uwa_winning[data-auction_id='"+key+"']" ).attr('data-user_id') != value.wua_current_bider){							jQuery( "span.uwa_winning[data-auction_id='"+key+"']" ).remove()						}					}				
				if (typeof value.wua_bid_value != 'undefined' ) {
					if(!jQuery( "input[name=uwa_bid_value][data-auction-id='"+key+"']" ).hasClass('wuachangedin')){
						//jQuery( "input[name=uwa_bid_value][data-auction-id='"+key+"']" ).val(value.wua_bid_value).removeClass('wuachangedin');
					}					
				}				if (typeof value.wua_bid_value_inc != 'undefined' ) {													jQuery( ".uwa_inc_price_ajax_"+key ).html(value.wua_bid_value_inc + " )");								}				
				if (typeof value.wua_reserve != 'undefined' ) {					
					jQuery( ".checkreserve" ).html("<p>" + value.wua_reserve + "</p>");
				}						
				if (typeof value.add_to_cart_text != 'undefined' ) {
					jQuery( "a.button.product_type_auction[data-product_id='"+key+"']" ).text(value.add_to_cart_text);
				}								if (typeof value.wua_activity != 'undefined' ) {						jQuery("#auction-history-table-" + key +" tbody > tr:first" ).before(value.wua_activity);											}
				});	
			}
	        running = false;
		},
		 error: function() {
			running = false;
		 }
	});	 
}