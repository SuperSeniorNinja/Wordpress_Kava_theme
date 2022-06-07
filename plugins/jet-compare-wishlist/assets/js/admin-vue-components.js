'use strict';

let jetCWSettinsMixin = {
	data: function() {
		return {
			pageOptions: window.jetCWSettingsConfig.settingsData,
			preparedOptions: {},
			savingStatus: false,
			ajaxSaveHandler: null,
		};
	},

	watch: {
		pageOptions: {
			handler( options ) {
				let prepared = {};

				for ( let option in options ) {

					if ( options.hasOwnProperty( option ) ) {
						prepared[ option ] = options[option]['value'];
					}
				}

				this.preparedOptions = prepared;

				this.saveOptions();
			},
			deep: true
		}
	},

	methods: {
		saveOptions: function() {
			var self = this;

			self.savingStatus = true;

			self.ajaxSaveHandler = jQuery.ajax( {
				type: 'POST',
				url: window.jetCWSettingsConfig.settingsApiUrl,
				dataType: 'json',
				data: self.preparedOptions,
				beforeSend: function( jqXHR, ajaxSettings ) {

					if ( null !== self.ajaxSaveHandler ) {
						self.ajaxSaveHandler.abort();
					}
				},
				success: function( responce, textStatus, jqXHR ) {
					self.savingStatus = false;

					if ( 'success' === responce.status ) {
						self.$CXNotice.add( {
							message: responce.message,
							type: 'success',
							duration: 3000,
						} );
					}

					if ( 'error' === responce.status ) {
						self.$CXNotice.add( {
							message: responce.message,
							type: 'error',
							duration: 3000,
						} );
					}
				}
			} );
		},
	},
	created: function() {
		if ( ! this.pageOptions.wishlist_store_type.value || 'false' === this.pageOptions.wishlist_store_type.value ) {
			this.pageOptions.wishlist_store_type.value = 'session';
		}

		if ( ! this.pageOptions.compare_store_type.value || 'false' === this.pageOptions.compare_store_type.value ) {
			this.pageOptions.compare_store_type.value = 'session';
		}
	}
}

Vue.component( 'jet-cw-compare-settings', {
	template: '#jet-dashboard-jet-cw-compare-settings',
	mixins: [ jetCWSettinsMixin ],
} );

Vue.component( 'jet-cw-wishlist-settings', {
	template: '#jet-dashboard-jet-cw-wishlist-settings',
	mixins: [ jetCWSettinsMixin ],
} );

Vue.component( 'jet-cw-avaliable-addons', {
	template: '#jet-dashboard-jet-cw-avaliable-addons',
	mixins: [ jetCWSettinsMixin ],
} );
