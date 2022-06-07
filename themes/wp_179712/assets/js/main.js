;var wp_179712;

(function($) {
	'use strict';

	wp_179712 = {

		init: function() {
			
			//this.deepLinkingB();
			this.preloaderInit();
			this.footerMoveDokaStore();
			//this.buttonNuka();
		},

		deepLinkingB:function(){
			deepLinking.init({
			      menuSelector : '#main-menu'
			});
		},

		preloaderInit: function(){

			var preloader = $( '.preloader' );
			
				if ( preloader.length ) {
				var loader = setTimeout(function() {
		            preloader.addClass("loaded");
		            $(window).trigger("resize");
		        }, 1000);
			}
		},
		
		footerMoveDokaStore: function(){
			$('body.dokan-store #content .site-content__wrap footer#colophon').insertAfter($('body.dokan-store #content .site-content__wrap'));
		},

		buttonNuka: function(){
			var buttonNuka = $( '.button-nuka a.elementor-icon.elementor-animation-' );
    		$('.button-nuka a.elementor-icon.elementor-animation-').append('<div class="anim-icon"><span class="icon icon-1"></span><span class="icon icon-2"></span><span class="icon icon-3"></span></div>');
		}

	};

	$(document).ready(function(){
		wp_179712.init();
	})

}(jQuery));