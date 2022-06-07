jQuery(document).ready(function($){
	Woo_ajax_url = WooUa.ajaxurl;
	Woo_last_activity = WooUa.last_timestamp;
//Ajax query String
	Ajax_qry_str = WooUa_Ajax_Qry.ajaqry;
	running = false;
//Interval refresh page	
	var refresh_time_interval = ''; 	
	if(woo_ua_data.refresh_interval){
	   refresh_time_interval =  setInterval(function(){
	     getLiveStatusAuction();
    	}, woo_ua_data.refresh_interval*1000);
	}
	//Time Count Down
	$( ".auction_product_countdown" ).each(function( index ) {
		var time 	= $(this).data('time');
		var format 	= $(this).data('format');
		if(format == ''){
			format = 'yowdHMS';
		}	
	var etext = '<p>'+woo_ua_data.finished+'</p>';	
		$(this).WooUacountdown({
			until:   $.WooUacountdown.UTCDate(-(new Date().getTimezoneOffset()),new Date(time*1000)),
			format: format,
			compact:  false,
			onExpiry: closeWooUaAuction,
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
	$( ".ua-watchlist-action" ).on('click', watchlist);
	function watchlist( event ) {
	var auction_id = jQuery(this).data('auction-id');
	var currentelement  =  $(this);           
		jQuery.ajax({
         type : "get",
         url : Woo_ajax_url,
         data : { post_id : auction_id, 'woo-ua-ajax' : "watchlist"},
         success: function(response) {
                     currentelement.parent().replaceWith(response);
                     $( ".ua-watchlist-action" ).on('click', watchlist);
                     jQuery( document.body).trigger('ua-watchlist-action',[response,auction_id] );
        	}
      	});
	}
/* --------------------------------------------------------
Send Private Message
----------------------------------------------------------- */
	$( document ).on( 'click', 'button#woo_ua_private_send', function() {
		var error = 0;
		var thisObj = $(this);
		var private_msg_form = $('#woo_ua_private_msg_form');
		// Remove Error Message
		private_msg_form.find( '.woo_ua_private_msg_error' ).slideUp();
		// collect the data
		var firstnameObj	= private_msg_form.find( '.woo_ua_pri_name' );
		var firstname 		= firstnameObj.val();
		var emailObj 		= private_msg_form.find( '.woo_ua_pri_email' );
		var email 			= emailObj.val();
		var messageObj 		= private_msg_form.find( '.woo_ua_pri_message' );
		var message 		= messageObj.val();
		var product_idObj 		= private_msg_form.find( '.woo_ua_pri_product_id' );
		var product_id 		= product_idObj.val();
		if( error == 0 ) {
			// Hide / show for ajax loader
			thisObj.hide();
			private_msg_form.find( 'img.woo_ua_private_msg_ajax_loader' ).show();	
			var data = {
						action: 	'send_private_message_process',
						firstname: 		firstname,
						email: 		    email,
						message: 		message,
						product_id: 	product_id,
					}
				$.post( Woo_ajax_url, data, function(response) {
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
					private_msg_form.find( '#woo_ua_private_msg_success' ).html( data.success_message );
				}
				// Hide / show for ajax loader
				thisObj.show();
				private_msg_form.find( 'img.woo_ua_private_msg_ajax_loader' ).hide();
			});
		}
		return false;
	});
	closeWooUaAuction();
});
function closeWooUaAuction(){
		var auctionid = jQuery(this).data('auction-id');
		var ajaxcontainer = jQuery(this).parent().next('.woo_ua_auction_product_ajax_change'); 
		ajaxcontainer.empty().prepend('<div class="ajax-loading"></div>');
		ajaxcontainer.parent().children('form.buy-now').remove();
		var ajaxurl = Ajax_qry_str+'=finish_auction';
		request =  jQuery.ajax({
         type : "post",
         url : ajaxurl,
         cache : false,
         data : {action: "finish_auction", post_id : auctionid, ret: ajaxcontainer.length},
         success: function(response) {
         			if (response.length  != 0){
         				ajaxcontainer.children('.ajax-loading').remove();
         				ajaxcontainer.prepend(response);         				
         			}
        	}
      	});
}
jQuery(function($){
	$(".woo_ua_auction_form div.quantity:not(.buttons_added),.woo_ua_auction_form td.quantity:not(.buttons_added)").addClass("buttons_added").append('<input type="button" value="+" class="plus" />').prepend('<input type="button" value="-" class="minus" />'),	
	$(document).on("click",".woo_ua_auction_form .plus,.woo_ua_auction_form .minus",function(){		
		var t=$(this).closest(".quantity").find("input[name=wua_bid_value]"),
		a=parseFloat(t.val()),
		n=parseFloat(t.attr("max")),
		s=parseFloat(t.attr("min")),
		e=t.attr("step");a&&""!==a&&"NaN"!==a||(a=0),(""===n||"NaN"===n)&&(n=""),(""===s||"NaN"===s)&&(s=0),("any"===e||""===e||void 0===e||"NaN"===parseFloat(e))&&(e=1),$(this).is(".plus")?t.val(n&&(n==a||a>n)?n:a+parseFloat(e)):s&&(s==a||s>a)?t.val(s):a>0&&t.val(a-parseFloat(e)),t.trigger("change")})
});
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
		data : {action: "get_live_stutus_auction", "last_timestamp" : Woo_last_activity},
		success: function(response) {
			if(response != null ) {			
				if (typeof response.last_timestamp != 'undefined') {
					Woo_last_activity = response.last_timestamp;
				}
				jQuery.each( response, function( key, value ) {	
				  auction = jQuery("body").find(".woo-ua-auction-price[data-auction-id='" + key + "']");
				  auction.replaceWith(value.wua_curent_bid);
				if (typeof value.wua_curent_bid != 'undefined' ) {
					jQuery( ".wua_curent_bid" ).html(value.wua_curent_bid);
				}								
				//countdown timer
				if (typeof value.wua_timer != 'undefined') {
					var curenttimer = jQuery("body").find(".auction_product_countdown[data-auctionid='" + key + "']");
						if(curenttimer.attr('data-time') != value.wua_timer){
							curenttimer.attr('data-time',value.wua_timer );
						}
				}								if (typeof value.wua_current_bider != 'undefined' ) {											var currentuser = jQuery("input[name=user_id]");						var mainauction = jQuery("input[name=wua-place-bid]").val();						if (currentuser.length){							if(value.wua_current_bider != currentuser.val() && mainauction == key ){								jQuery('.woocommerce-message').replaceWith(woo_ua_data.outbid_message );							}						}						if(jQuery( "span.woo_ua_winning[data-auction_id='"+key+"']" ).attr('data-user_id') != value.wua_current_bider){							jQuery( "span.woo_ua_winning[data-auction_id='"+key+"']" ).remove()						}					}				
				if (typeof value.wua_bid_value != 'undefined' ) {
					if(!jQuery( "input[name=wua_bid_value][data-auction-id='"+key+"']" ).hasClass('wuachangedin')){
						jQuery( "input[name=wua_bid_value][data-auction-id='"+key+"']" ).val(value.wua_bid_value).removeClass('wuachangedin');
					}
				}
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