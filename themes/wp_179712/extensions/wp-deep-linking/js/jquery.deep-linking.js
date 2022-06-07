var deep_linking = deep_linking||{};

(function($){
	"use strict";

	window.deepLinking = {

		settings:{
			menuSelector: '.menu',
			baseURL: deep_linking['base_url'],
		},

		init: function(options){
			var self = this;

			self.settings = $.extend( self.settings, options );

			$(document).ready(this.onReady.bind(this));
			$(window).load(this.windowOnload.bind(this));
		},

		onReady: function(){
			var self = this;

			$(window).on('hashchange', this.onHashChange.bind(this));

			$(self.settings.menuSelector).find('a').on('click', function(e){

				e.preventDefault();

				var $this = $(this),
					current_path = window.location.origin + window.location.pathname,
					base_url     = self.settings.baseURL,
					is_subpage   = !!current_path.replace( base_url, '' ).length,
					has_query    = !!window.location.href.match( /^.+\?.+(#.*)?$/i ),
					has_no_hash  = !this.hash.length;

				if( has_no_hash ){
					window.location.href = this.href;
				}else if( is_subpage || has_query ){
					window.location.href =  base_url + this.hash;
				}else{
					var state = window.history.state;
					window.history.replaceState(state, '', this.hash);

					self.scrollTo( this.hash );
					$(this).parent().addClass('current-menu-item');
				}
			});
		},

		windowOnload: function(){
			$(window).on("scroll", this.onScroll.bind(this)).scroll();
		},

		onScroll: function (event) {

			var self = this,
				menu = $(self.settings.menuSelector),
				timeoutID;

			menu.find('a').each(function () {
				var $this = $(this),
					state = window.history.state,
					$target = this.hash.length ? $(this.hash) : [],
					e_top, e_bottom, w_top, w_bottom, topEdge_test, bottomEdge_test;

				$this.parent().removeClass('current-menu-item');

				if ( $target[0] ) {

					e_top = $target.offset().top;
					e_bottom = e_top + $target.height();
					w_top = $(document).scrollTop();
					w_bottom = w_top + 100;

					topEdge_test = e_top <= w_bottom;
					bottomEdge_test = e_bottom > w_top;

					if ( topEdge_test && bottomEdge_test ) {

						window.history.replaceState( state, '', this.hash );

						$this.parent().addClass("current-menu-item");
					}
				}
			});
		},

		onHashChange: function(e){

		},

		scrollTo: function( hash ){
			var self = this,
				$target = $(hash),
				time = 1000;

			//console.log($target);

			if( !!$target.length ){
				$('html, body').stop().animate({
					'scrollTop': $target.offset().top - 20
				}, time, 'swing' );
				// setTimeout( function(){ window.location.hash = hash }, time );
			}
		}
	}
}(jQuery));
