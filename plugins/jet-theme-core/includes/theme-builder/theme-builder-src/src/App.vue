<template>
	<mainHeader/>
	<div :class="templateListClasses">
		<templateList></templateList>
	</div>
	<transition name="cx-popup">
		<cx-vui-popup
			class="jet-theme-builder__popup create-template-popup"
			:value="isNewTemplatePopupVisible"
			@on-cancel="newTemplatePopupCloseHandler"
			:header="false"
			:footer="false"
			body-width="false"
		>
			<template v-slot:content>
			<createTemplateForm></createTemplateForm>
			</template>
		</cx-vui-popup>
	</transition>
	<transition name="cx-popup">
		<cx-vui-popup
			class="jet-theme-builder__popup conditions-popup"
			:value="isConditionsPopupVisible"
			@on-cancel="conditionsPopupCloseHandler"
			:header="false"
			:footer="false"
			body-width="false"
		>
			<template v-slot:content>
				<conditionsManager/>
			</template>
		</cx-vui-popup>
	</transition>
	<transition name="cx-popup">
		<cx-vui-popup
			class="jet-theme-builder__popup template-library-popup"
			:value="isTemplateLibraryPopupVisible"
			@on-cancel="templateLibraryPopupCloseHandler"
			:header="false"
			:footer="false"
			body-width="false"
		>
			<template v-slot:content>
				<templateLibraryForm/>
			</template>
		</cx-vui-popup>
	</transition>
	<transition name="cx-popup">
		<cx-vui-popup
			class="jet-theme-builder__popup import-page-template-popup"
			:value="isImportPageTemplatePopupVisible"
			@on-cancel="importPageTemplatePopupCloseHandler"
			:header="false"
			:footer="false"
			body-width="false"
		>
			<template v-slot:content>
				<importPageTemplateForm/>
			</template>
		</cx-vui-popup>
	</transition>
</template>

<script>
import mainHeader from './components/mainHeader.vue';
import templateList from './components/templateList.vue';
import createTemplateForm from './components/createTemplateForm.vue';
import templateLibraryForm from './components/templateLibraryForm.vue';
import importPageTemplateForm from './components/importPageTemplateForm.vue';
import conditionsManager from './components/conditionsManager.vue';

export default {
	name: 'App',
	components: {
		mainHeader,
		templateList,
		createTemplateForm,
		templateLibraryForm,
		importPageTemplateForm,
		conditionsManager
	},
	data() {
		return {
			config: window.JetThemeBuilderConfig,
			progressState: true,
			debounceInterval: null,
		}
	},
	created() {
		this.getTeplatePageList();

		this.$store.commit( 'updateRawConditionsData', {
			list: window.JetThemeBuilderConfig.rawConditionsData,
		} );
	},
	watch: {
		pageTemplateList: {
			deep: true,
			handler: function( current ) {
				clearInterval( this.debounceInterval );
				this.debounceInterval = setTimeout( this.savePageTemplateData, 500 );
			}
		},
		templateList: {
			deep: true,
			handler: function( current ) {
				clearInterval( this.debounceInterval );
				this.debounceInterval = setTimeout( this.saveTemplateData, 500 );
			}
		}
	},
	computed: {
		templateListClasses() {
			return [
				'jet-theme-builder__template-list',
				this.$store.state.updatePageTemplatesProgressState ? 'progress-state' : ''
			];
		},
		pageTemplateList() {
			return this.$store.getters.getPageTemplateList;
		},
		templateList() {
			return this.$store.getters.getTemplateList;
		},
		isNewTemplatePopupVisible() {
			return this.$store.state.popupVisible.createTemplate;
		},
		isConditionsPopupVisible() {
			return this.$store.state.popupVisible.conditionsPopup;
		},
		isTemplateLibraryPopupVisible() {
			return this.$store.state.popupVisible.templateLibrary;
		},
		isImportPageTemplatePopupVisible() {
			return this.$store.state.popupVisible.importPageTemplate;
		},
	},
	methods: {
		getTeplatePageList() {
			this.$store.state.updatePageTemplatesProgressState = true;

			wp.apiFetch( {
				method: 'post',
				path: window.JetThemeBuilderConfig.getPageTemplateListPath
			} ).then( ( response ) => {
				this.$store.state.updatePageTemplatesProgressState = false;

				if ( response.success ) {
					this.$store.commit( 'updateRawPageTemplateList', {
						list: response.data,
					} );
				}
			} );
		},
		newTemplatePopupCloseHandler() {
			this.$store.dispatch( 'closeCreateTemplatePopup' );
		},
		conditionsPopupCloseHandler() {
			this.$store.dispatch( 'closeConditionsPopup' );
		},
		templateLibraryPopupCloseHandler() {
			this.$store.dispatch( 'closeTemplateLibraryPopup' );
		},
		importPageTemplatePopupCloseHandler() {
			this.$store.dispatch( 'closeImportPageTemplatePopup' );
		},
		savePageTemplateData() {
			let index = this.pageTemplateList.findIndex( ( templateData, index ) => {
				return templateData.id === this.$store.state.pageTemplateId;
			} );

			if ( -1 === index ) {
				return false;
			}

			let templateData = this.pageTemplateList[ index ];

			wp.apiFetch( {
				method: 'post',
				path: window.JetThemeBuilderConfig.updatePageTemplateDataPath,
				data: {
					template_id: this.$store.state.pageTemplateId,
					template_data: templateData,
				}
			} ).then( ( response ) => {
				console.log(response.message)
			} );
		},

		saveTemplateData() {
			let index = this.templateList.findIndex( ( templateData, index ) => {
				return templateData.id === this.$store.state.templateId;
			} );

			if ( -1 === index ) {
				return false;
			}

			let templateData = this.templateList[ index ];

			wp.apiFetch( {
				method: 'post',
				path: window.JetThemeBuilderConfig.updateTemplateDataPath,
				data: {
					template_id: this.$store.state.templateId,
					template_data: templateData,
				}
			} ).then( ( response ) => {

				console.log(response.message)

			} );
		}


	}
}
</script>

<style lang="scss">
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap');

.jet-theme-builder {
	display: flex;
	flex-direction: column;
	align-items: stretch;
	gap: 30px;
	margin-top: 20px;
	margin-right: 20px;
	font-family: 'Roboto', sans-serif;
	font-size: 13px;
	line-height: 1.5;

	h1, h2, h3, h4, h5, h6 {
		margin: 0 0 10px 0;
	}

	--primary-text-color: #23282D;
	--secondary-text-color: #7B7E81;
	--border-color: #CACBCD;
	--link-color: #0073aa;
	--success-color: #46B450;
	--warning-color: #EAB413;
	--error-color: #D6336C;

	--page-type-color: #6AAC1E;
	--archive-type-color: #EE7B16;
	--single-type-color: #4272F9;
}

.jet-theme-builder__template-list {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	grid-template-rows: auto;
	gap: 20px;
	/*display: flex;
	flex-wrap: wrap;
	justify-content: flex-start;
	*/

	@media (max-width: 1439px) {
		grid-template-columns: repeat(3, 1fr);
	}

	@media (max-width: 1023px) {
		grid-template-columns: repeat(2, 1fr);
	}
}

.jet-theme-builder-form {
	display: flex;
	flex-direction: column;
	align-items: stretch;

	&__header {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: flex-start;
		margin-bottom: 24px;
	}

	&__header-icon {
		margin-bottom: 16px;
	}

	&__header-title {
		font-size: 21px;
		font-weight: 500;
		text-align: center;
		color: var(--primary-text-color);
		margin-bottom: 10px;
	}

	&__header-sub-title {
		text-align: center;
		color: var(--secondary-text-color);
		max-width: 670px;
	}

	&__body {
		margin-bottom: 32px;
	}

	&__footer {
		display: flex;
		justify-content: flex-end;
		align-items: center;
		gap: 16px;

		&.center-align {
			justify-content: center;
		}
	}

	.cx-vui-component {
		padding: 0;
		margin-bottom: 24px;
		border: none;

		&:last-child {
			margin-bottom: 0;
		}
	}

	.cx-vui-component__meta {
		padding: 0;
		margin: 0 0 5px 0;
		border: none;
	}
}

.svg-icon {
	display: flex;
	justify-content: center;
	align-items: center;
}

.capitalize-format {
	text-transform: capitalize;
}

.panel {
	border-radius: 4px;
	padding: 16px;
	background-color: white;
	box-shadow: 0px 2px 6px rgb(35 40 45 / 7%);
}

.jet-theme-builder__popup {
	z-index: 9999;

	.cx-vui-popup__body {
		width: 850px;
		max-height: calc(100% - 160px);
		padding: 40px;

		@media (max-width: 1439px) {
			width: 700px;
		}
	}

	.cx-vui-popup__close {
		display: flex;
		justify-content: center;
		align-items: center;
		top: 17px;
		right: 17px;

		svg, path {
			fill: #7B7E81;
		}
	}
	.cx-vui-popup__content {
		min-width: 500px;
		line-height: 1.5;
	}

	&.create-template-popup,
	&.import-page-template-popup {
		.cx-vui-popup__body {
			width: 500px;
		}
	}

	&.template-library-popup {
		.cx-vui-popup__body {
			width: 960px;
			overflow-y: scroll;
		}
	}

}

.cx-vui-button {

	.cx-vui-button__content {
		gap: 4px;
	}

	&--style-link-accent {

		&:hover {
			color: #066EA2;
			background-color: transparent;
		}
	}
}

.cx-vui-f-select {
	.cx-vui-f-select__selected-not-empty {
		margin: 0;
	}
}

.progress-state {
	opacity: 0.5;
	pointer-events: none;
}

.cx-popup-enter-active,
.cx-popup-leave-active {
	transition: opacity 0.5s ease;
}

.cx-popup-enter-from,
.cx-popup-leave-to {
	opacity: 0;
}

</style>
