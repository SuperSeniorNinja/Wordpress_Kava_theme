(function($){
	"use strict";
jQuery(document).ready(function($){
	
	jQuery('.datetimepicker').datetimepicker(
		{defaultDate: "",
		dateFormat: "yy-mm-dd",
		numberOfMonths: 1,
		showButtonPanel: true,
		showOn: "button",
		buttonImage: SA_Ajax.calendar_image,
		buttonImageOnly: true
		});	
	
	var productType = jQuery('#product-type').val();
	
	if (productType=='auction'){
		jQuery('.show_if_simple').show();
		jQuery('.inventory_options').show();
		jQuery('.general_options').show();
		jQuery('#inventory_product_data ._manage_stock_field').addClass('hide_if_auction').hide();
        jQuery('#inventory_product_data ._sold_individually_field').parent().addClass('hide_if_auction').hide();
        jQuery('#inventory_product_data ._sold_individually_field').addClass('hide_if_auction').hide();
        jQuery('#inventory_product_data ._stock_field ').addClass('hide_if_auction').hide();
        jQuery('#inventory_product_data ._backorders_field ').parent().addClass('hide_if_auction').hide();
        jQuery('#inventory_product_data ._stock_status_field ').addClass('hide_if_auction').hide();
        jQuery('.options_group.pricing ').addClass('hide_if_auction').hide();
        jQuery('#auction_tab .required').each(function(index, el) {
        	jQuery(this).attr("required", true);
        });
	} else{
		jQuery('#Auction.postbox').hide();
		jQuery('#Automatic_relist_auction.postbox').hide();
		jQuery('#auction_tab .required').each(function(index, el) {
			jQuery(this).attr("required", false);
		});
	}
	jQuery('#product-type').on('change', function(){
		if  (jQuery(this).val() =='auction'){
			jQuery('.show_if_simple').show();
			jQuery('.inventory_options').show();
			jQuery('.general_options').show();
			jQuery('#inventory_product_data ._manage_stock_field').addClass('hide_if_auction').hide();
            jQuery('#inventory_product_data ._sold_individually_field').parent().addClass('hide_if_auction').hide();
            jQuery('#inventory_product_data ._sold_individually_field').addClass('hide_if_auction').hide();
            jQuery('#inventory_product_data ._stock_field ').addClass('hide_if_auction').hide();
            jQuery('#inventory_product_data ._backorders_field ').parent().addClass('hide_if_auction').hide();
            jQuery('#inventory_product_data ._stock_status_field ').addClass('hide_if_auction').hide();
            jQuery('.options_group.pricing ').addClass('hide_if_auction').hide();
			jQuery('#Auction.postbox').show();
			jQuery('#Automatic_relist_auction.postbox').show();
			jQuery(this).attr("required", "true");
			jQuery('#auction_tab .required').each(function(index, el) {
				jQuery(this).attr("required", true);
			});
		} else{
			jQuery('#Auction.postbox').hide();
			jQuery('#Automatic_relist_auction.postbox').hide();
			jQuery('#auction_tab .required').each(function(index, el) {
				jQuery(this).attr("required", false);
			});
		}
	});
	jQuery('label[for="_virtual"]').addClass('show_if_auction');
	jQuery('label[for="_downloadable"]').addClass('show_if_auction');

	var disabledclick = false;
	
	jQuery('.auction-table .action a:not(.disabled)').on('click',function(event){

		
		if(disabledclick)
			return;

		jQuery('.auction-table .action a').addClass('disabled');
		disabledclick = true;
		var logid = $(this).data('id');
		var postid = $(this).data('postid');
		var curent = $(this);
		
		jQuery.ajax({
         type : "post",
         url : SA_Ajax.ajaxurl,
         data : {action: "delete_bid", logid : logid, postid: postid, SA_nonce : SA_Ajax.SA_nonce },
         success: function(response) {
         			if (response.action == 'deleted'){
         				curent.parent().parent().addClass('deleted').fadeOut('slow');
         			}
         			
                    if (response.auction_current_bid ){
                    	
                    	$('.postbox#Auction span.higestbid').html(response.auction_current_bid )
                    }

                    if (response.auction_current_bider ){
                    	$('.postbox#Auction span.higestbider').html(response.auction_current_bider )
                    }

                    disabledclick = false;
                    jQuery('.auction-table .action a').removeClass('disabled');


        	}
      	});
      	event.preventDefault();      	
	});


	jQuery('#Auction .removereserve').on('click',function(event){
		var postid = $(this).data('postid');
		var curent = $(this);		
		jQuery.ajax({
         type : "post",
         url : SA_Ajax.ajaxurl,
         data : {action: "remove_reserve_price", postid: postid, SA_nonce : SA_Ajax.SA_nonce },
         success: function(response) {
         			if (response.error){
         				curent.after(response.error)
         			} else{
         				if (response.succes){
         					$('.postbox#Auction .reservefail').html(response.succes)
         				}
         			}
         		}	         			
      	});
      	event.preventDefault();      	
	});

	jQuery('#general_product_data #_regular_price').on('keyup',function(){
		jQuery('#auction_tab #_regular_price').val(jQuery(this).val());
   	});

	jQuery('#relistauction').on('click',function(event){
		event.preventDefault();
		jQuery('.relist_auction_dates_fields').toggle();
            
      	
   	});

	if(jQuery('#_auction_proxy:checkbox:checked').length > 0){
		$('.form-field._auction_sealed_field ').hide();
	}

	if(jQuery('#_auction_sealed:checkbox:checked').length > 0){
			$('.form-field._auction_proxy_field ').hide();
	}
	
	$("#_auction_proxy").on('change' ,function() {
		if(this.checked) {
			$('.form-field._auction_sealed_field ').slideUp('fast');
			$('#_auction_sealed').prop('checked', false);
		} else{
			$('.form-field._auction_sealed_field ').slideDown('fast');
		}
	});

	$("#_auction_sealed").on('change' ,function() {
		if(this.checked) {
			$('.form-field._auction_proxy_field ').slideUp('fast');
			$('#_auction_proxy').prop('checked', false);
		} else{
			$('.form-field._auction_proxy_field ').slideDown('fast');
		}
	});

	jQuery('.inventory_options').addClass('show_if_auction').show();

	$('#Auction .auction-table').DataTable({
	    dom: 'lfBrtip',
	    "order": [0, 'desc'],
	    stateSave: true,
	    "pageLength": 20,
	    responsive: true,
	    "columns": [
	        null,
	        null,
	        null,
	        {
	            "visible": false
	        }, {
	            "visible": false
	        }, {
	            "visible": false
	        }, {
	            "visible": false
	        },
	        null, {
	            "orderable": false
	        },
    ],
	    buttons: [
	        'colvis', {
	            extend: 'csv',
	            exportOptions: {
	                columns: 'th:not(:last-child)'
	            }
	        }, {
	            extend: 'excel',
	            exportOptions: {
	                columns: 'th:not(:last-child)'
	            }
	        },

	    ],
	    "language": {
	        "sEmptyTable": SA_Ajax.datatable_language.sEmptyTable,
	        "sInfo": SA_Ajax.datatable_language.sInfo,
	        "sInfoEmpty": SA_Ajax.datatable_language.sInfoEmpty,
	        "sInfoFiltered": SA_Ajax.datatable_language.sInfoFiltered,
	        "sLengthMenu": SA_Ajax.datatable_language.sLengthMenu,
	        "sLoadingRecords": SA_Ajax.datatable_language.sLoadingRecords,
	        "sProcessing": SA_Ajax.datatable_language.sProcessing,
	        "sSearch": SA_Ajax.datatable_language.sSearch,
	        "sZeroRecords": SA_Ajax.datatable_language.sZeroRecords,
	        "oPaginate": {
	            "sFirst": SA_Ajax.datatable_language.oPaginate.sFirst,
	            "sLast": SA_Ajax.datatable_language.oPaginate.sLast,
	            "sNext": SA_Ajax.datatable_language.oPaginate.sNext,
	            "sPrevious": SA_Ajax.datatable_language.oPaginate.sPrevious
	        },
	        "oAria": {
	            "sSortAscending": SA_Ajax.datatable_language.oAria.sSortAscending,
	            "sSortDescending": SA_Ajax.datatable_language.oAria.sSortDescending,
	        }
	    }
	});
 
});
})(jQuery);