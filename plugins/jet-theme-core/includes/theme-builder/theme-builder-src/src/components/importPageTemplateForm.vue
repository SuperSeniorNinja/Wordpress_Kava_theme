<template>
	<div
		class="jet-theme-builder-form jet-theme-builder-form--import-page-template-form"
		:class="itemClasses"
	>
		<div class="jet-theme-builder-form__header">
			<div class="jet-theme-builder-form__header-title">Import page template</div>
			<p class="jet-theme-builder-form__header-sub-title">Here you can select a page template file in the .json format and import it.</p>
		</div>
		<div class="jet-theme-builder-form__body">
			<form enctype="multipart/form-data" novalidate>
				<div class="dropbox">
					<input
						type="file"
						ref="file"
						:disabled="progressState"
						@change="prepareToImport( $event.target.files )"
						accept=".json,application/json"
					>
				</div>
			</form>
		</div>
		<div class="jet-theme-builder-form__footer">
			<cx-vui-button
				button-style="default"
				class="cx-vui-button--style-accent-border"
				size="mini"
				@on-click="cancelHandler"
			>
				<template v-slot:label>
					<span>Cancel</span>
				</template>
			</cx-vui-button>
			<cx-vui-button
				button-style="default"
				class="cx-vui-button--style-accent"
				size="mini"
				@on-click="importPageTemplateHandler"
				:loading="progressState"
				:disabled="!readyToImport"
			>
				<template v-slot:label>
					<span>Import</span>
				</template>
			</cx-vui-button>
		</div>
	</div>
</template>

<script>

export default {
	name: 'importPageTemplateForm',
	data() {
		return {
			progressState: false,
			readyToImport: false,
			file: false,
		}
	},
	computed: {
		itemClasses() {
			return [
				this.progressState ? 'progress-state' : ''
			];
		},
	},
	methods: {
		prepareToImport( files ) {
			this.file = files[0];
			this.readyToImport = true
		},

		importPageTemplateHandler( name, files ) {

			if ( ! this.readyToImport ) {
				return false;
			}

			let formData = new FormData(),
			    xhr      = null;

			formData.append( '_file', this.file );
			formData.append( 'action', 'jet_theme_core_import_page_template' );
			this.progressState = true;

			xhr = new XMLHttpRequest();

			xhr.open( 'POST', window.ajaxurl, true );
			xhr.onload = ( event, request ) => {
				this.progressState = false;

				if ( xhr.status == 200 ) {
					let response = event.currentTarget.response;

					response = JSON.parse( response );

					if ( response.success ) {

						this.$store.commit( 'updateRawPageTemplateList', {
							list: response.data.templatesList,
						} );

						this.$store.dispatch( 'closeImportPageTemplatePopup' );
					} else {
						console.log( response.data.message );
					}
				} else {
					console.log( xhr.status )
				}
			};

			xhr.send( formData );
		},

		cancelHandler: function() {
			this.$store.dispatch( 'closeImportPageTemplatePopup' );
		},
	},
}
</script>

<style lang="scss">
	.jet-theme-builder-form--import-page-template-form {
		display: flex;
		flex-direction: column;
		align-items: stretch;

		.dropbox {
			border-radius: 4px;
			padding: 16px;
			background-color: white;
			border: 1px dashed #DCDCDD;
		}

	}
</style>
