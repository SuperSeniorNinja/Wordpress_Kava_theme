<template>
	<div
		class="jet-theme-builder-form jet-theme-builder-form--create-template-form"
		:class="itemClasses"
	>
		<div class="jet-theme-builder-form__header" v-if="!isTemplateCreated">
			<div class="jet-theme-builder-form__header-title">Create a template</div>
			<p class="jet-theme-builder-form__header-sub-title">Here you can create a new theme template for the header, body, and footer sections.</p>
		</div>
		<div class="jet-theme-builder-form__header" v-if="isTemplateCreated">
			<div class="jet-theme-builder-form__header-icon svg-icon">
				<svg width="37" height="36" viewBox="0 0 37 36" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M18.5 3C10.22 3 3.5 9.72 3.5 18C3.5 26.28 10.22 33 18.5 33C26.78 33 33.5 26.28 33.5 18C33.5 9.72 26.78 3 18.5 3ZM14.435 24.435L9.05 19.05C8.465 18.465 8.465 17.52 9.05 16.935C9.635 16.35 10.58 16.35 11.165 16.935L15.5 21.255L25.82 10.935C26.405 10.35 27.35 10.35 27.935 10.935C28.52 11.52 28.52 12.465 27.935 13.05L16.55 24.435C15.98 25.02 15.02 25.02 14.435 24.435Z" fill="#45B450"/>
				</svg>
			</div>
			<div class="jet-theme-builder-form__header-title">Template created</div>
			<p class="jet-theme-builder-form__header-sub-title">Now you can go to editor or back to all templates. You will be able to edit the template later.</p>
		</div>
		<div class="jet-theme-builder-form__body" v-if="!isTemplateCreated">
			<cx-vui-select
				name="templateContentType"
				label="Template content type"
				placeholder="Select template content type"
				:wrapper-css="[ 'vertical-fullwidth' ]"
				size="fullwidth"
				:options-list="getTemplateContentTypeOptions"
				:value="templateContentType"
				@on-input="templateContentType=$event"
			>
			</cx-vui-select>
			<cx-vui-input
				name="templateName"
				label="Template name"
				placeholder="Enter template name(optional)"
				:wrapper-css="[ 'vertical-fullwidth' ]"
				size="fullwidth"
				type="text"
				:value="templateName"
				@on-change="templateName=$event.target.value"
			>
			</cx-vui-input>
		</div>
		<div class="jet-theme-builder-form__footer" v-if="!isTemplateCreated">
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
				@on-click="createTemplateHandler"
				:loading="progressState"
			>
				<template v-slot:label>
					<span>Create</span>
				</template>
			</cx-vui-button>
		</div>
		<div class="jet-theme-builder-form__footer center-align" v-if="isTemplateCreated">
			<cx-vui-button
				button-style="default"
				class="cx-vui-button--style-accent-border"
				size="mini"
				@on-click="cancelHandler"
			>
				<template v-slot:label>
					<span>Edit later</span>
				</template>
			</cx-vui-button>
			<cx-vui-button
				button-style="default"
				class="cx-vui-button--style-accent"
				size="mini"
				@on-click="goToEditorHandler"
				:loading="progressState"
			>
				<template v-slot:label>
					<span>Go to editor</span>
				</template>
			</cx-vui-button>
		</div>
	</div>
</template>

<script>

export default {
	name: 'createTemplateForm',
	emits: {
		click: null,
	},
	data() {
		return {
			progressState: false,
			templateName: '',
			templateContentType: 'default',
			isTemplateCreated: false,
		}
	},
	computed: {
		itemClasses() {
			return [
				this.progressState ? 'progress-state' : ''
			];
		},
		getTemplateContentTypeOptions () {
			return window.JetThemeBuilderConfig.templateContentTypeOptions;
		},
		getStructureType() {
			return this.$store.state.templateStructureType;
		}
	},
	methods: {
		createTemplateHandler() {
			this.progressState = true;

			wp.apiFetch( {
				method: 'post',
				path: window.JetThemeBuilderConfig.createTemplatePath,
				data: {
					name: this.templateName,
					type: this.getStructureType,
					content: this.templateContentType,
				},
			} ).then( ( response ) => {
				this.progressState = false;

				console.log( response.message );

				if ( response.success ) {

					this.$store.commit( 'updateRawTemplateList', {
						list: response.data.templatesList,
					} );

					this.$store.commit( 'updateTemplateId', {
						id: response.data.newTemplateId,
					} );

					this.$store.dispatch( 'updatePageTemplateStructureId', {
						id: response.data.newTemplateId,
					} );

					//this.$store.dispatch( 'closeCreateTemplatePopup' );
					this.isTemplateCreated = true;
				} else {
					this.templateCreatingStatus = false;
				}
			} );
		},

		goToEditorHandler() {
			let templatesList = this.$store.state.rawTemplateList;

			let index = templatesList.findIndex( ( templateData, index ) => {
				return templateData.id === this.$store.state.templateId;
			} );

			if ( -1 === index ) {
				return false;
			}

			let editLink = templatesList[ index ].editLink;

			window.open( editLink, '_blank' ).focus();

			this.$store.dispatch( 'closeCreateTemplatePopup' );
		},

		cancelHandler() {
			this.$store.dispatch( 'closeCreateTemplatePopup' );
		},
	},
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">

</style>
