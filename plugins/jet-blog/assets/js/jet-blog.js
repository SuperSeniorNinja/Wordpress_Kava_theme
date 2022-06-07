;( function( $, elementor, settings ) {

	"use strict";

	var JetBlog = {

		YT: null,
		updateCurrentPage: {},

		init: function() {

			var widgets = {
				'jet-blog-smart-listing.default': JetBlog.initSmartListing,
				'jet-blog-smart-tiles.default': JetBlog.initSmartTiles,
				'jet-blog-text-ticker.default': JetBlog.initTextTicker,
				'jet-blog-video-playlist.default': JetBlog.initPlayList
			};

			$.each( widgets, function( widget, callback ) {
				elementor.hooks.addAction( 'frontend/element_ready/' + widget, callback );
			});

		},

		initPlayList: function( $scope ) {
			var $target            = $( '.jet-blog-playlist', $scope ),
				$indexContainer    = $( '.jet-blog-playlist__item-index', $target ),
				hideIndex          = $target.data( 'hide-index' ),
				$durationContainer = $( '.jet-blog-playlist__item-duration', $target ),
				hideDuration       = $target.data( 'hide-duration' ),
				$imageContainer    = $( '.jet-blog-playlist__item-thumb', $target ),
				hideImage          = $target.data( 'hide-image' ),
				deviceMode         = elementorFrontend.getCurrentDeviceMode();

			if ( -1 != hideIndex.indexOf( deviceMode ) ) {
				$indexContainer.css( 'display', 'none' );
			}

			if ( -1 != hideDuration.indexOf( deviceMode ) ) {
				$durationContainer.css( 'display', 'none' );
			}

			if ( -1 != hideImage.indexOf( deviceMode ) ) {
				$imageContainer.css( 'display', 'none' );
			}

			$( window ).on( 'resize orientationchange', function() {
				deviceMode = elementorFrontend.getCurrentDeviceMode();

				if ( -1 != hideIndex.indexOf( deviceMode ) ) {
					$indexContainer.css( 'display', 'none' );
				} else {
					$indexContainer.css( 'display', 'block' );
				}

				if ( -1 != hideDuration.indexOf( deviceMode ) ) {
					$durationContainer.css( 'display', 'none' );
				} else {
					$durationContainer.css( 'display', 'block' );
				}

				if ( -1 != hideImage.indexOf( deviceMode ) ) {
					$imageContainer.css( 'display', 'none' );
				} else {
					$imageContainer.css( 'display', 'block' );
				}
			} )

			if ( 'undefined' !== typeof YT.Player ) {
				JetBlog.initPlayListCb( $scope, YT );
			} else {
				$( document ).on( 'JetYouTubeIframeAPIReady', function( event, YT ) {
					JetBlog.initPlayListCb( $scope, YT );
				} );
			}

		},

		initPlayListCb: function( $scope, YT ) {

			if ( null === JetBlog.YT ) {
				JetBlog.YT = YT;
			}

			if ( $scope.hasClass( 'players-initialized' ) ) {
				return;
			}

			$scope.addClass( 'players-initialized' );

			JetBlog.switchVideo( $scope.find( '.jet-blog-playlist__item.jet-blog-active' ) );

			$scope.on( 'click.JetBlog', '.jet-blog-playlist__item', function() {
				$scope.find( '.jet-blog-playlist__canvas' ).addClass( 'jet-blog-canvas-active' );
				JetBlog.switchVideo( $( this ) );
			} );

			$scope.on( 'click.JetBlog', '.jet-blog-playlist__canvas-overlay', JetBlog.stopVideo );
		},

		initTextTicker: function( $scope ) {
			var timer          = null,
				$ticker        = $scope.find( '.jet-text-ticker__posts' ),
				isTypingEffect = $ticker.data( 'typing' ),
				sliderSettings = $ticker.data( 'slider-atts' );

			/**
			 * Typing effect with JS
			 *
			 * @since 2.1.17
			 */
			if ( isTypingEffect ) {
				$ticker.on( 'init', function( event, slick ) {
					var $currentTyping = $( '[data-slick-index="' + slick.currentSlide + '"] .jet-text-ticker__item-typed-inner', $ticker );

					typing( $currentTyping );
				} );

				$ticker.on( 'beforeChange', function( event, slick, currentSlide, nextSlide ) {
					var $typedItem     = $( '[data-slick-index="' + currentSlide + '"] .jet-text-ticker__item-typed', $ticker ),
						$currentTyping = $( '[data-slick-index="' + currentSlide + '"] .jet-text-ticker__item-typed-inner', $ticker ),
						$nextTyping    = $( '[data-slick-index="' + nextSlide + '"] .jet-text-ticker__item-typed-inner', $ticker );

					clearInterval( timer );
					$typedItem.removeClass( 'jet-text-typing' );
					$currentTyping.text( '' );

					typing( $nextTyping );
				} );
			}
			/** End */

			$ticker.slick( sliderSettings );

			// Typing function
			function typing( $selector ) {

				if ( !$selector.length ) {
					return;
				}

				var typingCounter    = 0,
					$typedItem       = $selector.closest( '.jet-text-ticker__item-typed' ),
					typingText       = $selector.data( 'typing-text' ),
					typingTextLength = typingText.length;

				$typedItem.addClass( 'jet-text-typing' );
				$selector.text( typingText.substr( 0, typingCounter++ ) );

				timer = setInterval( function() {
					if ( typingCounter <= typingTextLength ) {
						$selector.text( typingText.substr( 0, typingCounter++ ) );
					} else {
						clearInterval( timer );
						$typedItem.removeClass( 'jet-text-typing' );
					}
				}, 40 );
			}
		},

		initSmartListing: function( $scope ) {
			var deviceMode     = elementorFrontend.getCurrentDeviceMode(),
				editMode       = window.elementorFrontend.isEditMode(),
				id             = $scope.data('id'),
				$wrapper       = $( '.jet-smart-listing-wrap', $scope ),
				$settings      = $wrapper.data( 'settings' );

			if ( !JetBlog.updateCurrentPage[id] ) {
				JetBlog.updateCurrentPage[id] = { updatePage: 0 }
			}

			$scope.on( 'click.JetBlog', '.jet-smart-listing__filter-item a', JetBlog.handleSmartListingFilter );
			$scope.on( 'click.JetBlog', '.jet-smart-listing__arrow', JetBlog.handleSmartListingPager );

			var $filter = $scope.find( '.jet-smart-listing__filter' ),
				rollup  = $filter.data( 'rollup' );

			if ( rollup ) {
				$filter.JetBlogMore();
			}

			$( document ).trigger( 'jet-blog-smart-list/init', [ $scope, JetBlog ] );

			var devicesPosts = JetBlog.breakpointsPosts( $wrapper );

			function updatePosts() {
				var deviceMode   = elementorFrontend.getCurrentDeviceMode(),
					filter       = $('.jet-smart-listing__filter', $scope ),
					filterTerm   = filter.find('.jet-active-item a').data('term'),
					data         = {};

				devicePosts = JetBlog.currentBreakpointPosts( devicesPosts, deviceMode );

				JetBlog.updateCurrentPage[id].updatePage = 1;

				if ( $wrapper.hasClass( 'jet-processing' ) ) {
					return;
				}

				$wrapper.addClass( 'jet-processing' );

				data = { paged: 1, posts_per_page: devicePosts };

				if ( filter[0] ) {
					data.term = filterTerm;
				}

				$.ajax({
					url: settings.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'jet_blog_smart_listing_get_posts',
						jet_request_data: data,
						jet_widget_settings: $settings
					},
				}).done( function( response ) {

					var $arrows = $wrapper.find( '.jet-smart-listing__arrows' );

					$wrapper
						.removeClass( 'jet-processing' )
						.find( '.jet-smart-listing' )
						.html( response.data.posts );

					if ( $arrows.length ) {
						$arrows.replaceWith( response.data.arrows );
					}

				}).fail(function() {
					$wrapper.removeClass( 'jet-processing' );
				});
			}

			if ( 'yes' != $settings['is_archive_template'] ) {

				if ( editMode ) {
					$( window ).on( 'resize.JetBlog orientationchange.JetBlog', JetBlog.debounce( 50, updatePosts ) );
				} else {
					$( window ).on( 'orientationchange.JetBlog', JetBlog.debounce( 50, updatePosts ) );
				}

				if ( 'desktop' != deviceMode ) {
					var devicePosts = JetBlog.currentBreakpointPosts( devicesPosts, deviceMode );

					if ( $wrapper.hasClass( 'jet-processing' ) ) {
						return;
					}

					$wrapper.addClass( 'jet-processing' );

					$.ajax({
						url: settings.ajaxurl,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'jet_blog_smart_listing_get_posts',
							jet_request_data: { posts_per_page: devicePosts },
							jet_widget_settings: $wrapper.data( 'settings' )
						},
					}).done( function( response ) {

						var $arrows = $wrapper.find( '.jet-smart-listing__arrows' );

						$wrapper
							.removeClass( 'jet-processing' )
							.find( '.jet-smart-listing' )
							.html( response.data.posts );

						if ( $arrows.length ) {
							$arrows.replaceWith( response.data.arrows );
						}

					}).fail(function() {
						$wrapper.removeClass( 'jet-processing' );
					});
				}
			}
		},

		initSmartTiles: function( $scope ) {

			var $carousel = $scope.find( '.jet-smart-tiles-carousel' );

			if ( 0 === $carousel.length ) {
				return false;
			}

			var sliderSettings = $carousel.data( 'slider-atts' );

			$carousel.slick( sliderSettings );

		},

		stopVideo: function( event ) {
			var $target         = $( event.currentTarget ),
				$canvas         = $target.closest( '.jet-blog-playlist__canvas' ),
				currentPlayer   = $canvas.data( 'player' ),
				currentProvider = $canvas.data( 'provider' );

			if ( $canvas.hasClass( 'jet-blog-canvas-active' ) ) {
				$canvas.removeClass( 'jet-blog-canvas-active' );
				JetBlog.pauseCurrentPlayer( currentPlayer, currentProvider );
			}

		},

		switchVideo: function( $el ) {

			var $canvas         = $el.closest( '.jet-blog-playlist' ).find( '.jet-blog-playlist__canvas' ),
				$counter        = $el.closest( '.jet-blog-playlist' ).find( '.jet-blog-playlist__counter-val' ),
				id              = $el.data( 'id' ),
				$iframeWrap     = $canvas.find( '#embed_wrap_' + id ),
				newPlayer       = $el.data( 'player' ),
				newProvider     = $el.data( 'provider' ),
				currentPlayer   = $canvas.data( 'player' ),
				currentProvider = $canvas.data( 'provider' );

			if ( newPlayer ) {
				JetBlog.startNewPlayer( newPlayer, newProvider );
				$canvas.data( 'provider', newProvider );
				$canvas.data( 'player', newPlayer );
			}

			if ( currentPlayer ) {
				JetBlog.pauseCurrentPlayer( currentPlayer, currentProvider );
			}

			if ( $counter.length ) {
				$counter.html( $el.data( 'video_index' ) );
			}

			$el.siblings().removeClass( 'jet-blog-active' );

			if ( ! $el.hasClass( 'jet-blog-active' ) ) {
				$el.addClass( 'jet-blog-active' );
			}

			if ( ! $iframeWrap.length ) {

				$iframeWrap = $( '<div id="embed_wrap_' + id + '"></div>' ).appendTo( $canvas );

				switch ( newProvider ) {

					case 'youtube':
						JetBlog.intYouTubePlayer( $el, {
							id: id,
							canvas: $canvas,
							currentPlayer: currentPlayer,
							playerTarget: $iframeWrap,
							height: $el.data( 'height' ),
							videoId: $el.data( 'video_id' )
						} );
					break;

					case 'vimeo':
						JetBlog.intVimeoPlayer( $el, {
							id: id,
							canvas: $canvas,
							currentPlayer: currentPlayer,
							playerTarget: $iframeWrap,
							html: $.parseJSON( $el.data( 'html' ) )
						} );
					break;

				}

				$iframeWrap.addClass( 'jet-blog-playlist__embed-wrap' );

			}

			$iframeWrap.addClass( 'jet-blog-active' ).siblings().removeClass( 'jet-blog-active' );

		},

		intYouTubePlayer: function( $el, plSettings ) {

			var $iframe = $( '<div id="embed_' + plSettings.id + '"></div>' ).appendTo( plSettings.playerTarget );
			var player  = new JetBlog.YT.Player( $iframe[0], {
				height: plSettings.height,
				width: '100%',
				videoId: plSettings.videoId,
				playerVars: { 'showinfo': 0, 'rel': 0 },
				events: {
					onReady: function( event ) {
						$el.data( 'player', event.target );

						if ( plSettings.currentPlayer ) {
							event.target.playVideo();
						}

						plSettings.canvas.data( 'provider', 'youtube' );
						plSettings.canvas.data( 'player', event.target );

					},
					onStateChange: function( event ) {

						var $index  = $el.find( '.jet-blog-playlist__item-index' );

						if ( ! $index.length ) {
							return;
						}

						switch ( event.data ) {

							case 1:
								$index.removeClass( 'jet-is-paused' ).addClass( 'jet-is-playing' );
								if ( ! plSettings.canvas.hasClass( 'jet-blog-canvas-active' ) ) {
									plSettings.canvas.addClass( 'jet-blog-canvas-active' );
								}
							break;

							case 2:
								$index.removeClass( 'jet-is-playing' ).addClass( 'jet-is-paused' );
							break;

						}
					}
				}
			});

		},

		intVimeoPlayer: function( $el, plSettings ) {

			var $iframe = $( plSettings.html ).appendTo( plSettings.playerTarget );
			var player  = new Vimeo.Player( $iframe[0] );
			var $index  = $el.find( '.jet-blog-playlist__item-index' );

			player.on( 'loaded', function( event ) {

				$el.data( 'player', this );
				if ( plSettings.currentPlayer ) {
					this.play();
				}

				plSettings.canvas.data( 'provider', 'vimeo' );
				plSettings.canvas.data( 'player', this );
			});

			player.on( 'play', function() {
				if ( $index.length ) {
					$index.removeClass( 'jet-is-paused' ).addClass( 'jet-is-playing' );
					if ( ! plSettings.canvas.hasClass( 'jet-blog-canvas-active' ) ) {
						plSettings.canvas.addClass( 'jet-blog-canvas-active' );
					}
				}
			});

			player.on( 'pause', function() {
				if ( $index.length ) {
					$index.removeClass( 'jet-is-playing' ).addClass( 'jet-is-paused' );
				}
			});

		},

		pauseCurrentPlayer: function( currentPlayer, currentProvider ) {

			switch ( currentProvider ) {
				case 'youtube':
					currentPlayer.pauseVideo();
				break;

				case 'vimeo':
					currentPlayer.pause();
				break;
			}
		},

		startNewPlayer: function( newPlayer, newProvider ) {

			switch ( newProvider ) {
				case 'youtube':
					setTimeout( function() {
						newPlayer.playVideo();
					}, 300);
				break;

				case 'vimeo':
					newPlayer.play();
				break;
			}

		},

		handleSmartListingFilter: function( event ) {

			var $this = $( this ),
				$item = $this.closest( '.jet-smart-listing__filter-item' ),
				term  = $this.data( 'term' );

			event.preventDefault();

			$item.closest('.jet-smart-listing__filter').find( '.jet-active-item' ).removeClass( 'jet-active-item' );
			$item.addClass( 'jet-active-item' );

			JetBlog.requestPosts( $this, { term: term, paged: 1 } );

		},

		handleSmartListingPager: function() {

			var $this        = $( this ),
				$wrapper     = $this.closest( '.jet-smart-listing-wrap' ),
				id           = $wrapper.closest('.elementor-widget-jet-blog-smart-listing').data('id'),
				currentPage  = parseInt( $wrapper.data( 'page' ), 10 ),
				newPage      = 1,
				currentTerm  = parseInt( $wrapper.data( 'term' ), 10 ),
				direction    = $this.data( 'dir' ),
				scrollTop    = $wrapper.data(  'scroll-top' );

			if ( $this.hasClass( 'jet-arrow-disabled' ) ) {
				return;
			}

			if ( 1 === JetBlog.updateCurrentPage[id].updatePage ) {
				currentPage = 1;
				JetBlog.updateCurrentPage[id].updatePage = 0;
			}

			if ( 'next' === direction ) {
				newPage = currentPage + 1;
			}

			if ( 'prev' === direction ) {
				newPage = currentPage - 1;
			}

			JetBlog.requestPosts( $this, { term: currentTerm, paged: newPage } );

			if ( scrollTop ) {
				$( 'html, body' ).stop().animate( { scrollTop: $wrapper.offset().top }, 500 );
			}

		},

		breakpointsPosts: function( $wrapper ) {

			var wrapper_settings  = $wrapper.data( 'settings' ),
				deviceMode        = elementorFrontend.getCurrentDeviceMode(),
				activeBreakpoints = elementor.config.responsive.activeBreakpoints,
				rows              = wrapper_settings['posts_rows'],
				breakpointPosts   = [],
				featuredPost      = 'yes' === wrapper_settings['featured_post'] ? 1 : 0,
				prevDevice;

				breakpointPosts['desktop'] = [];
				breakpointPosts['desktop'] = wrapper_settings['posts_columns'] * rows + featuredPost;
				prevDevice = 'desktop';

			Object.keys( activeBreakpoints ).reverse().forEach( function( breakpointName ) {

				if ( 'widescreen' === breakpointName ) {
					breakpointPosts[breakpointName] = wrapper_settings['posts_columns_' + breakpointName] ? wrapper_settings['posts_columns_' + breakpointName] * rows + featuredPost : breakpointPosts['desktop'];
				} else {
					breakpointPosts[breakpointName] = wrapper_settings['posts_columns_' + breakpointName] ? wrapper_settings['posts_columns_' + breakpointName] * rows + featuredPost : breakpointPosts[prevDevice];

					prevDevice = breakpointName;
				}
			} )

			return breakpointPosts;
		},

		currentBreakpointPosts: function( columnsArray, deviceMode ) {
			return columnsArray[deviceMode];
		},

		requestPosts: function( $trigger, data ) {

			var $wrapper = $trigger.closest( '.jet-smart-listing-wrap' ),
				$loader  = $wrapper.next( '.jet-smart-listing-loading' ),
				deviceMode   = elementorFrontend.getCurrentDeviceMode(),
				devicesPosts = JetBlog.breakpointsPosts( $wrapper ),
				devicePosts  = JetBlog.currentBreakpointPosts( devicesPosts, deviceMode );

			if ( $wrapper.hasClass( 'jet-processing' ) ) {
				return;
			}

			$wrapper.addClass( 'jet-processing' );

			data['posts_per_page'] = devicePosts;

			$.ajax({
				url: settings.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'jet_blog_smart_listing_get_posts',
					jet_request_data: data,
					jet_widget_settings: $wrapper.data( 'settings' )
				},
			}).done( function( response ) {

				var $arrows = $wrapper.find( '.jet-smart-listing__arrows' );

				$wrapper
					.removeClass( 'jet-processing' )
					.find( '.jet-smart-listing' )
					.html( response.data.posts );

				if ( $arrows.length ) {
					$arrows.replaceWith( response.data.arrows );
				}

			}).fail(function() {
				$wrapper.removeClass( 'jet-processing' );
			});

			if ( 'undefined' !== typeof data.paged ) {
				$wrapper.data( 'page', data.paged );
			}

			if ( 'undefined' !== typeof data.term ) {
				$wrapper.data( 'term', data.term );
			}

		},

		/**
		 * Debounce the function call
		 *
		 * @param  {number}   threshold The delay.
		 * @param  {Function} callback  The function.
		 */
		debounce: function ( threshold, callback ) {
			var timeout;

			return function debounced( $event ) {
				function delayed() {
					callback.call( this, $event );
					timeout = null;
				}

				if ( timeout ) {
					clearTimeout( timeout );
				}

				timeout = setTimeout( delayed, threshold );
			};
		}

	};

	$( window ).on( 'elementor/frontend/init', JetBlog.init );

	var JetBlogMore = function( el ) {

		this.$el        = $( el );
		this.$container = this.$el.closest( '.jet-smart-listing__heading' );

		if ( this.$container.find( '.jet-smart-listing__title' ).length ) {
			this.$heading = this.$container.find( '.jet-smart-listing__title' );
		} else {
			this.$heading = this.$container.find( '.jet-smart-listing__title-placeholder' );
		}

		this.settings = $.extend( {
			icon:      '<span class="jet-blog-icon"><i class="fa fa-ellipsis-h"></i></span>',
			className: 'jet-smart-listing__filter-item jet-smart-listing__filter-more'
		}, this.$el.data( 'more' ) );

		this.containerWidth = 0;
		this.itemsWidth     = 0;
		this.heading        = 0;

		this.init();

	};

	JetBlogMore.prototype = {

		constructor: JetBlogMore,

		init: function() {

			var self = this;

			this.containerWidth = this.$container.width();
			this.heading        = this.$heading.outerWidth();

			this.$hiddenWrap = $( '<div class="' + this.settings.className + '" hidden="hidden">' + this.settings.icon + '</div>' ).appendTo( this.$el );
			this.$hidden = $( '<div class="jet-smart-listing__filter-hidden-items"></div>' ).appendTo( this.$hiddenWrap );

			this.iter = 0;

			this.rebuildItems();

			setTimeout( function() {
				self.watch();
				self.rebuildItems();
			}, 300 );

		},

		watch: function() {

			var delay = 100;

			$( window ).on( 'resize.JetBlogMore orientationchange.JetBlogMore', JetBlog.debounce( delay, this.watcher.bind( this ) ) );
		},

		/**
		 * Responsive menu watcher callback.
		 *
		 * @param  {Object} Resize or Orientationchange event.
		 * @return {void}
		 */
		watcher: function( event ) {

			this.containerWidth = this.$container.width();
			this.itemsWidth     = 0;

			this.$hidden.html( '' );
			this.$hiddenWrap.attr( 'hidden', 'hidden' );

			this.$el.find( '> div[hidden]:not(.jet-smart-listing__filter-more)' ).each( function() {
				$( this ).removeAttr( 'hidden' );
			});

			this.rebuildItems();
		},

		rebuildItems: function() {

			var self            = this,
				$items          = this.$el.find( '> div:not(.jet-smart-listing__filter-more):not([hidden])' ),
				contentWidth    = 0,
				hiddenWrapWidth = parseInt( this.$hiddenWrap.outerWidth(), 10 );

			this.itemsWidth = 0;

			$items.each( function() {

				var $this  = $( this ),
					$clone = null;

				self.itemsWidth += $this.outerWidth();
				contentWidth = self.$heading.outerWidth() + hiddenWrapWidth + self.itemsWidth;

				if ( 0 > self.containerWidth - contentWidth && $this.is( ':visible' ) ) {

					$clone = $this.clone();

					$this.attr( { 'hidden': 'hidden' } );
					self.$hidden.append( $clone );
					self.$hiddenWrap.removeAttr( 'hidden' );
				}

			} );

		}

	};

	$.fn.JetBlogMore = function() {
		return this.each( function() {
			new JetBlogMore( this );
		} );
	};

}( jQuery, window.elementorFrontend, window.JetBlogSettings ) );

if ( 1 === window.hasJetBlogPlaylist ) {

	function onYouTubeIframeAPIReady() {
		jQuery( document ).trigger( 'JetYouTubeIframeAPIReady', [ YT ] );
	}

}
