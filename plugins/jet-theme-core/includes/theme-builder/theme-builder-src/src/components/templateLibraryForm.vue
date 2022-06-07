<template>
	<div
		class="jet-theme-builder-form jet-theme-builder-form--template-library-form"
		:class="itemClasses"
	>
		<div class="jet-theme-builder-form__header">
			<div class="jet-theme-builder-form__header-title">Template Library</div>
			<p class="jet-theme-builder-form__header-sub-title" v-if="isStructureTemplatesEmpty"><span class="capitalize-format">{{ $store.state.layoutStructureType}}</span> templates for not found. Create a template to use for further pages.</p>
			<p class="jet-theme-builder-form__header-sub-title" v-if="!isStructureTemplatesEmpty">Here you can select a custom <span class="capitalize-format">{{ $store.state.layoutStructureType}}</span> template and apply it to the needed page template. Such a custom template will override the default one from the current theme.</p>
		</div>
		<div class="jet-theme-builder-form__body">
			<div class="empty-template-library" v-if="isStructureTemplatesEmpty">
				<cx-vui-button
					button-style="default"
					class="cx-vui-button--style-accent"
					size="mini"
					@on-click="createTemplateHandler"
					:loading="progressState"
				>
					<template v-slot:label>
						<span>Create template</span>
					</template>
				</cx-vui-button>
			</div>
			<div class="template-library" v-if="!isStructureTemplatesEmpty">
				<div class="template-library__list">
					<div
						:class="[ 'template-library__item', ! templateData.editLink ? 'not-editable' : '' ]"
						v-for="(templateData, index) in getStructureTemplates"
						:key="index"
						:templateData="templateData"
					>
						<div class="template-library__item-header">
							<VTooltip
								:triggers="['hover', 'focus']"
							>
								<div class="svg-icon warning" v-if="!templateData.editLink">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M4.47 21H19.53C21.07 21 22.03 19.33 21.26 18L13.73 4.99C12.96 3.66 11.04 3.66 10.27 4.99L2.74 18C1.97 19.33 2.93 21 4.47 21ZM12 14C11.45 14 11 13.55 11 13V11C11 10.45 11.45 10 12 10C12.55 10 13 10.45 13 11V13C13 13.55 12.55 14 12 14ZM13 18H11V16H13V18Z" fill="black"/>
									</svg>
								</div>
								<template #popper>
									<span>Template cannot be used, please install required content editor</span>
								</template>
							</VTooltip>
							<div class="template-library__item-content-icon" v-html="getContentTypeIcon( templateData.contentType )"></div>
							<div class="template-library__item-name">{{ templateData.title }}</div>
						</div>
						<div class="template-library__item-meta">
							<div class="template-library__item-meta-item template-date">
								<span>{{ templateData.date.format }}</span>
							</div>
							<div class="template-library__item-meta-item template-author">
								<span>by {{ templateData.author.name }}</span>
							</div>
						</div>
						<div class="template-library__item-controls">
							<cx-vui-button
								button-style="default"
								class="cx-vui-button--style-link-accent"
								size="mini"
								@on-click="editTemplateHandler( templateData.editLink )"
							>
								<template v-slot:label>
									<span class="svg-icon">
										<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M2.5 14.3751V17.5001H5.625L14.8417 8.28346L11.7167 5.15846L2.5 14.3751ZM17.2583 5.8668C17.5833 5.5418 17.5833 5.0168 17.2583 4.6918L15.3083 2.7418C14.9833 2.4168 14.4583 2.4168 14.1333 2.7418L12.6083 4.2668L15.7333 7.3918L17.2583 5.8668Z" fill="#007CBA"/>
										</svg>
									</span>
									<span>Edit</span>
								</template>
							</cx-vui-button>
							<cx-vui-button
								button-style="default"
								class="cx-vui-button--style-link-accent"
								size="mini"
								@on-click="addTemplateHandler( templateData.id )"
							>
								<template v-slot:label>
									<span class="svg-icon">
										<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M15.8332 10.8337H10.8332V15.8337H9.1665V10.8337H4.1665V9.16699H9.1665V4.16699H10.8332V9.16699H15.8332V10.8337Z" fill="#007CBA"/>
										</svg>
									</span>
									<span>Use</span>
								</template>
							</cx-vui-button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>

export default {
	name: 'templateLibraryForm',
	components: {

	},
	data() {
		return {
			progressState: false,
		}
	},
	computed: {
		itemClasses() {
			return [
				this.progressState ? 'progress-state' : ''
			];
		},
		getStructureTemplates() {
			return this.$store.state.rawTemplateList.filter( ( template ) => {
				return this.$store.state.templateStructureType === template.type;
			} );
		},
		isStructureTemplatesEmpty() {
			return 0 == this.getStructureTemplates.length ? true : false;
		},
		structureContentTypeIcon() {

		},
	},
	methods: {
		getContentTypeIcon( contentType ) {
			let templateContentTypeIcons = window.JetThemeBuilderConfig.templateContentTypeIcons;

			return templateContentTypeIcons.hasOwnProperty( contentType ) ? templateContentTypeIcons[ contentType ] : false;
		},
		addTemplateHandler( templateId = false ) {
			this.$store.commit( 'updateTemplateId', {
				id: templateId,
			} );

			this.$store.dispatch( 'updatePageTemplateStructureId', {
				id: templateId,
			} );

			this.$store.dispatch( 'closeTemplateLibraryPopup' );
		},
		editTemplateHandler( editLink = '#' ) {
			window.open( editLink, '_blank' ).focus();
		},
		createTemplateHandler() {
			this.$store.dispatch( 'closeTemplateLibraryPopup' );
			this.$store.dispatch( 'openCreateTemplatePopup' );
		}
	}
}
</script>

<style lang="scss">

	.empty-template-library {
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.template-library__list {
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		grid-template-rows: auto;
		gap: 20px;

		@media (max-width: 1439px) {
			grid-template-columns: repeat(3, 1fr);
		}

		@media (max-width: 1023px) {
			grid-template-columns: repeat(2, 1fr);
		}
	}

	.template-library__item-header {
		display: flex;
		align-items: center;
		gap: 5px;
		padding: 12px 12px 0 12px;
		flex: 1 1 auto;

		.svg-icon.warning {
			width: 20px;

			svg, path {
				fill: var(--warning-color);
			}
		}
	}

	.template-library__item-meta {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		gap: 12px;
		padding: 0 12px 12px 12px;

		&-item {
			display: flex;
			justify-content: flex-start;
			align-items: center;
			gap: 6px;
			color: var(--secondary-text-color);
			font-size: 13px;

			.svg-icon {
				width: 16px;

				svg, path {
					fill: var(--border-color);
				}
			}
		}
	}

	.template-library__item-content-icon {
		display: flex;
		justify-content: center;
		align-items: center;
		width: 16px;

		svg, path {
			fill: var(--border-color);
		}
	}

	.template-library__item {
		display: flex;
		flex-direction: column;
		border-radius: 4px;
		background-color: white;
		border: 1px solid #F0F0F1;

		&-name {
			color: var( --primary-text-color );
		}

		&-controls {
			display: flex;
			justify-content: space-between;
			align-items: center;
			background-color: #F0F0F1;
			padding: 0 12px;
		}

		&-control {
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 5px;
			cursor: pointer;
			color: var( --primary-text-color );

			.dashicons {
				color: var( --primary-text-color );
			}
		}

		&.not-editable {
			.cx-vui-button {
				pointer-events: none;
				opacity: 0.5;
			}
		}
	}
</style>

