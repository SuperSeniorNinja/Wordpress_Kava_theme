<template>
	<div :class="itemClasses">
		<div class="jet-theme-builder__template-structure-enable" v-if="isStructureDefined">
			<VTooltip
				:triggers="['hover', 'focus']"
			>
				<div class="svg-icon warning" v-if="!structureEditLink">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M4.47 21H19.53C21.07 21 22.03 19.33 21.26 18L13.73 4.99C12.96 3.66 11.04 3.66 10.27 4.99L2.74 18C1.97 19.33 2.93 21 4.47 21ZM12 14C11.45 14 11 13.55 11 13V11C11 10.45 11.45 10 12 10C12.55 10 13 10.45 13 11V13C13 13.55 12.55 14 12 14ZM13 18H11V16H13V18Z" fill="black"/>
					</svg>
				</div>
				<template #popper>
					<span>Template cannot be used, please install required content editor</span>
				</template>
			</VTooltip>
			<div class="svg-icon visible" v-if="structureData.override && structureEditLink" @click="switchEnabledHandler">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M12.0002 4.5C7.00018 4.5 2.73018 7.61 1.00018 12C2.73018 16.39 7.00018 19.5 12.0002 19.5C17.0002 19.5 21.2702 16.39 23.0002 12C21.2702 7.61 17.0002 4.5 12.0002 4.5ZM12.0002 17C9.24018 17 7.00018 14.76 7.00018 12C7.00018 9.24 9.24018 7 12.0002 7C14.7602 7 17.0002 9.24 17.0002 12C17.0002 14.76 14.7602 17 12.0002 17ZM12.0002 9C10.3402 9 9.00018 10.34 9.00018 12C9.00018 13.66 10.3402 15 12.0002 15C13.6602 15 15.0002 13.66 15.0002 12C15.0002 10.34 13.6602 9 12.0002 9Z" fill="#23282D"/>
				</svg>
			</div>
			<div class="svg-icon hidden" v-if="!structureData.override && structureEditLink" @click="switchEnabledHandler">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M12.0002 7C14.7602 7 17.0002 9.24 17.0002 12C17.0002 12.65 16.8702 13.26 16.6402 13.83L19.5602 16.75C21.0702 15.49 22.2602 13.86 22.9902 12C21.2602 7.61 16.9902 4.5 11.9902 4.5C10.5902 4.5 9.25018 4.75 8.01018 5.2L10.1702 7.36C10.7402 7.13 11.3502 7 12.0002 7ZM2.00018 4.27L4.28018 6.55L4.74018 7.01C3.08018 8.3 1.78018 10.02 1.00018 12C2.73018 16.39 7.00018 19.5 12.0002 19.5C13.5502 19.5 15.0302 19.2 16.3802 18.66L16.8002 19.08L19.7302 22L21.0002 20.73L3.27018 3L2.00018 4.27ZM7.53018 9.8L9.08018 11.35C9.03018 11.56 9.00018 11.78 9.00018 12C9.00018 13.66 10.3402 15 12.0002 15C12.2202 15 12.4402 14.97 12.6502 14.92L14.2002 16.47C13.5302 16.8 12.7902 17 12.0002 17C9.24018 17 7.00018 14.76 7.00018 12C7.00018 11.21 7.20018 10.47 7.53018 9.8ZM11.8402 9.02L14.9902 12.17L15.0102 12.01C15.0102 10.35 13.6702 9.01 12.0102 9.01L11.8402 9.02Z" fill="#23282D"/>
				</svg>
			</div>
		</div>
		<div class="jet-theme-builder__template-structure-label">
			<div class="jet-theme-builder__template-add-structure"
			     v-if="!isStructureDefined"
			     @click="openTemplateAttachOptionsHandler"
			>
				<span class="add-structure-icon svg-icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M18.9998 13.0001H12.9998V19.0001H10.9998V13.0001H4.99982V11.0001H10.9998V5.00009H12.9998V11.0001H18.9998V13.0001Z" fill="#23282D"/>
					</svg>
				</span>
				<span class="add-structure-text">Add {{ structureName }}</span>
				<contextMenu
					:options="contextMenuTemplateAttachOptions"
					v-if="isTemplateAttachOptionsVisible"
					@on-click-option="templateAttachOptionClickHandler"
					@on-click-away="onClickAwayHandler"
				></contextMenu>
			</div>

			<div class="jet-theme-builder__template-structure-name" v-if="isStructureDefined">
				<editableInput
					:value="structureName"
					placeholder=""
					:icon="structureContentTypeIcon"
					@on-blur:value="onInputStructureNameHandler"
				/>
			</div>
		</div>
		<div class="jet-theme-builder__template-structure-controls" v-if="isStructureDefined">
			<div class="jet-theme-builder__template-structure-control edit-structure">
				<div
					class="svg-icon jet-theme-builder__template-control options-template"
					@click="openStructureOptionsHandler"
				>
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12.0002 7.6C13.1002 7.6 14.0002 6.7 14.0002 5.6C14.0002 4.5 13.1002 3.6 12.0002 3.6C10.9002 3.6 10.0002 4.5 10.0002 5.6C10.0002 6.7 10.9002 7.6 12.0002 7.6ZM12.0002 9.6C10.9002 9.6 10.0002 10.5 10.0002 11.6C10.0002 12.7 10.9002 13.6 12.0002 13.6C13.1002 13.6 14.0002 12.7 14.0002 11.6C14.0002 10.5 13.1002 9.6 12.0002 9.6ZM12.0002 15.6C10.9002 15.6 10.0002 16.5 10.0002 17.6C10.0002 18.7 10.9002 19.6 12.0002 19.6C13.1002 19.6 14.0002 18.7 14.0002 17.6C14.0002 16.5 13.1002 15.6 12.0002 15.6Z" fill="#23282D"/>
					</svg>
				</div>
			</div>
			<contextMenu
				:options="contextMenuOptions"
				v-if="isOptionsVisible"
				@on-click-option="structureOptionClickHandler"
				@on-click-away="onClickAwayHandler"
			></contextMenu>
		</div>
	</div>
</template>

<script>
import contextMenu from './contextMenu.vue';
import editableInput from './editableInput.vue';

export default {
	name: 'templateStructure',
	components: {
		contextMenu,
		editableInput
	},
	props: {
		type: String,
		structureData: Object,
		pageTemplateId: Number,
		pageTemplateType: [String, Boolean]
	},
	data() {
		return {
			progressState: false,
			isOptionsVisible: false,
			isTemplateAttachOptionsVisible: false,
		}
	},
	computed: {
		itemClasses() {
			return [
				'jet-theme-builder__template-structure',
				this.progressState ? 'progress-state' : '',
				this.isStructureDefined ? 'template-defined' : '',
				! this.structureData.override || ! this.structureEditLink ? 'template-override-disable' : '',
			];
		},
		isStructureDefined() {
			return this.structureData.id ? true : false;
		},
		structureTemplateData() {

			if ( ! this.isStructureDefined ) {
				return false;
			}

			let templateData = this.$store.state.rawTemplateList.find( ( templateData ) => {
				return templateData.id === this.structureData.id;
			} );

			if ( ! templateData ) {
				return false;
			}

			return templateData;
		},
		structureName() {
			let structureTemplateData = this.structureTemplateData;

			if ( ! structureTemplateData ) {
				return this.type;
			}

			return this.structureTemplateData.title;
		},
		structureContentType() {
			let structureTemplateData = this.structureTemplateData;

			if ( ! structureTemplateData ) {
				return 'default';
			}

			return this.structureTemplateData.contentType;
		},
		structureContentTypeIcon() {
			let templateContentTypeIcons = window.JetThemeBuilderConfig.templateContentTypeIcons;

			return templateContentTypeIcons.hasOwnProperty( this.structureContentType ) ? templateContentTypeIcons[ this.structureContentType ] : false;
		},
		structureType() {

			if ( 'body' !== this.type ) {
				return `jet_${ this.type }`;
			}

			let bodyStructure = this.pageTemplateType;

			if ( ! bodyStructure ) {
				return 'jet_page';
			}

			return bodyStructure;
		},
		structureEditLink() {
			let structureTemplateData = this.structureTemplateData;

			if ( ! structureTemplateData ) {
				return false;
			}

			return structureTemplateData.editLink;
		},
		contextMenuOptions() {
			let options = [
				{
					icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.99982 19C5.99982 20.1 6.89982 21 7.99982 21H15.9998C17.0998 21 17.9998 20.1 17.9998 19V7H5.99982V19ZM18.9998 4H15.4998L14.4998 3H9.49982L8.49982 4H4.99982V6H18.9998V4Z" fill="#23282D"/></svg>',
					label: 'Remove',
					action: 'clear-structure',
				},
			];

			if ( this.structureEditLink ) {
				options.unshift( {
					icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 17.2501V21.0002H6.75L17.81 9.94015L14.06 6.19015L3 17.2501ZM20.71 7.04015C21.1 6.65015 21.1 6.02015 20.71 5.63015L18.37 3.29015C17.98 2.90015 17.35 2.90015 16.96 3.29015L15.13 5.12015L18.88 8.87015L20.71 7.04015Z" fill="#23282D"/></svg>',
					label: 'Edit content',
					action: 'edit-structure',
				} );
			}

			return options;
		},
		contextMenuTemplateAttachOptions() {
			return [
				{
					icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.99982 5.9998H1.99982V19.9998C1.99982 21.0998 2.89982 21.9998 3.99982 21.9998H17.9998V19.9998H3.99982V5.9998ZM19.9998 1.9998H7.99982C6.89982 1.9998 5.99982 2.8998 5.99982 3.9998V15.9998C5.99982 17.0998 6.89982 17.9998 7.99982 17.9998H19.9998C21.0998 17.9998 21.9998 17.0998 21.9998 15.9998V3.9998C21.9998 2.8998 21.0998 1.9998 19.9998 1.9998ZM18.9998 10.9998H14.9998V14.9998H12.9998V10.9998H8.99982V8.9998H12.9998V4.9998H14.9998V8.9998H18.9998V10.9998Z" fill="#23282D"/></svg>',
					label: `Create template`,
					action: 'create-template',
				},
				{
					icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.99982 4.0002H3.99982C2.89982 4.0002 2.00982 4.9002 2.00982 6.0002L1.99982 18.0002C1.99982 19.1002 2.89982 20.0002 3.99982 20.0002H19.9998C21.0998 20.0002 21.9998 19.1002 21.9998 18.0002V8.0002C21.9998 6.9002 21.0998 6.0002 19.9998 6.0002H11.9998L9.99982 4.0002Z" fill="#23282D"/></svg>',
					label: 'Add from library',
					action: 'add-from-library',
				},
			];
		}
	},
	methods: {
		switchEnabledHandler() {
			this.structureData.override = ! this.structureData.override;

			this.$store.dispatch( 'updatePageTemplateLayout', {
				pageTemplateId: this.pageTemplateId,
				structure: this.type,
				structureData: this.structureData,
			} );
		},
		openStructureOptionsHandler() {
			this.isOptionsVisible = ! this.isOptionsVisible;
		},
		openTemplateAttachOptionsHandler() {
			this.isTemplateAttachOptionsVisible = ! this.isTemplateAttachOptionsVisible;
		},
		structureOptionClickHandler( payload ) {

			switch ( payload.action ) {
				case 'edit-structure':

					if ( this.structureEditLink ) {
						window.open( this.structureEditLink, '_blank' ).focus();
					}
					break;
				case 'clear-structure':

					this.structureData.id = false;

					this.$store.dispatch( 'updatePageTemplateLayout', {
						pageTemplateId: this.pageTemplateId,
						structure: this.type,
						structureData: this.structureData,
					} );
					break;
			}
		},
		templateAttachOptionClickHandler( payload ) {

			switch ( payload.action ) {
				case 'create-template':
					this.$store.dispatch( 'openCreateTemplatePopup', {
						pageTemplateId: this.pageTemplateId,
						layoutStructureType: this.type,
						templateStructureType: this.structureType,
					} );

					break;
				case 'add-from-library':
					this.$store.dispatch( 'openTemplateLibraryPopup', {
						pageTemplateId: this.pageTemplateId,
						layoutStructureType: this.type,
						templateStructureType: this.structureType,
					} );

					break;
			}
		},
		onClickAwayHandler() {
			this.isOptionsVisible = false;
			this.isTemplateAttachOptionsVisible = false;
		},
		onInputStructureNameHandler( structureName ) {
			this.$store.dispatch( 'updateTemplateData', {
				id: this.structureData.id,
				name: structureName,
			} );
		},

	}
}
</script>

<style lang="scss">
	.jet-theme-builder__template-structure {
		position: relative;
		display: flex;
		justify-content: flex-start;
		align-items: center;
		gap: 10px;
		border-radius: 4px;
		padding: 4px 8px;
		background-color: white;
		border: 1px dashed #DCDCDD;

		&.template-defined {
			border-color: transparent;
			box-shadow: 0px 2px 4px rgba(35, 40, 45, 0.1);
			transition: box-shadow .3s cubic-bezier(.35,.77,.38,.96);
		}

		&.template-override-disable {
			box-shadow: 0px 1px 1px rgba(35, 40, 45, 0.1);

			.jet-theme-builder__template-structure-label {
				.jet-editable-input {
					color: var(--secondary-text-color);
				}
			}
		}
	}

	.jet-theme-builder__template-structure-enable {
		width: 20px;

		.visible {
			cursor: pointer;
			svg, path {
				fill: var(--link-color);
			}
		}

		.hidden {
			cursor: pointer;
			svg, path {
				fill: var(--secondary-text-color);
			}
		}

		.warning {
			svg, path {
				fill: var(--warning-color);
			}
		}

	}

	.jet-theme-builder__template-structure-label {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		gap: 5px;
		flex: 1 1 auto;

		.jet-editable-input {
			position: relative;
			color: var(--primary-text-color);

			.jet-editable-input__icon {
				svg, path {
					fill: var(--border-color);
				}
			}

			.jet-editable-input__input {
				font-size: 15px;
				line-height: 18px;
				transition: background-color .3s cubic-bezier(.35,.77,.38,.96);
			}

			&--focus {
				.jet-editable-input__input {
					box-shadow: inset 0 0 0 2px #99CBE3;
				}
			}

			&:not(.jet-editable-input--focus):hover {
				.jet-editable-input__input {
					background-color: rgba( 236, 236, 236, 0.4);
				}
			}
		}
	}

	.jet-theme-builder__template-structure-name {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		gap: 5px;
		flex: 1 1 auto;
	}

	.jet-theme-builder__template-structure-icon {
		display: flex;
		justify-content: center;
		align-items: center;
		width: 20px;

		svg {
			width: 100%;
			height: auto;
		}
	}

	.jet-theme-builder__template-add-structure {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		gap: 12px;
		width: 100%;
		font-size: 15px;
		line-height: 30px;
		cursor: pointer;
		color: var(--secondary-text-color);

		.add-structure-icon {
			width: 20px;

			svg, path {
				fill: var(--link-color);
			}
		}

		.add-structure-text {
			flex: 1 1 auto;
		}
	}

	.jet-theme-builder__template-structure-controls {
		position: relative;
		display: flex;
		justify-content: flex-end;
		align-items: center;
		gap: 5px;

		svg, path {
			fill: var(--link-color);
		}
	}

	.jet-theme-builder__template-structure-control {
		width: 20px;
		cursor: pointer;

		.options-template {
			color: var( --primary-text-color );
		}
	}
</style>
