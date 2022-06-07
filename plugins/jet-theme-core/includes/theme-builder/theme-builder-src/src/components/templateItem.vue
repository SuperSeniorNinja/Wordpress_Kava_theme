<template>
	<div
		:class="itemClasses"
	>
		<div class="jet-theme-builder__template-header">
			<div class="jet-theme-builder__template-header-main">
				<div class="jet-theme-builder__template-type-badge" v-if="'unassigned' !== pageTemplateType">
					<span>{{ pageTemplateTypeName }}</span>
				</div>
				<div class="jet-theme-builder__template-label">
					<editableInput
						:value="templateData.templateName"
						placeholder=""
						@on-blur:value="onInputTemplateNameHandler"
					/>
				</div>
				<div class="jet-theme-builder__template-controls">
					<div
						class="svg-icon jet-theme-builder__template-control options-template"
						@click="openOptionsHandler"
					>
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12.0002 7.6C13.1002 7.6 14.0002 6.7 14.0002 5.6C14.0002 4.5 13.1002 3.6 12.0002 3.6C10.9002 3.6 10.0002 4.5 10.0002 5.6C10.0002 6.7 10.9002 7.6 12.0002 7.6ZM12.0002 9.6C10.9002 9.6 10.0002 10.5 10.0002 11.6C10.0002 12.7 10.9002 13.6 12.0002 13.6C13.1002 13.6 14.0002 12.7 14.0002 11.6C14.0002 10.5 13.1002 9.6 12.0002 9.6ZM12.0002 15.6C10.9002 15.6 10.0002 16.5 10.0002 17.6C10.0002 18.7 10.9002 19.6 12.0002 19.6C13.1002 19.6 14.0002 18.7 14.0002 17.6C14.0002 16.5 13.1002 15.6 12.0002 15.6Z" fill="#23282D"/>
						</svg>
					</div>
					<contextMenu
						:options="contextMenuOptions"
						v-if="isOptionsVisible"
						@on-click-option="optionClickHandler"
						@on-click-away="onClickAwayHandler"
					></contextMenu>
				</div>
			</div>
		</div>
		<div class="jet-theme-builder__template-body">
			<templateStructure
				type="header"
				:structureData="templateData.layout.header"
				:pageTemplateId="templateData.id"
				:pageTemplateType="pageTemplateType"
			></templateStructure>
			<templateStructure
				type="body"
				:structureData="templateData.layout.body"
				:pageTemplateId="templateData.id"
				:pageTemplateType="pageTemplateType"
			></templateStructure>
			<templateStructure
				type="footer"
				:structureData="templateData.layout.footer"
				:pageTemplateId="templateData.id"
				:pageTemplateType="pageTemplateType"
			></templateStructure>
		</div>
		<div class="jet-theme-builder__template-footer">
			<VTooltip
				:triggers="['hover', 'focus']"
			>
				<div class="jet-theme-builder__template-conditions-main">
					<span class="unassigned-conditions" v-if="isUnassignedConditions">Unassigned</span>
					<span class="assigned-conditions" v-if="!isUnassignedConditions">
					{{ mainConditionVerbose }}
					<span class="assigned-conditions__more" v-if="moreConditionsLength">and {{ moreConditionsLength }} more</span>
				</span>
				</div>

				<template #popper>
					<div class="jet-theme-builder__template-conditions" v-if="!isUnassignedConditions">
						<templateConditionItem
							v-for="(conditionData, index) in conditionVerboseList"
							:key="index"
							:conditionData="conditionData"
						>
						</templateConditionItem>
					</div>
					<div v-if="isUnassignedConditions">Conditions are not assigned to this page template</div>
				</template>
			</VTooltip>
			<div class="jet-theme-builder__template-meta">
				<div class="jet-theme-builder__template-meta-item template-date">
					<span>{{ templateData.date.format }}</span>
				</div>
				<div class="jet-theme-builder__template-meta-item template-author">
					<span>by {{ templateData.author.name }}</span>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import templateStructure from './templateStructure.vue';
import contextMenu from './contextMenu.vue';
import editableInput from './editableInput.vue';
import templateConditionItem from './templateConditionItem.vue';
import VueClickAway from "vue3-click-away";

export default {
	name: 'templateItem',
	components: {
		templateStructure,
		contextMenu,
		templateConditionItem,
		editableInput
	},
	props: {
		templateData: Object
	},
	data() {
		return {
			progressState: false,
			isOptionsVisible: false,
		}
	},
	provide() {
		return {
			//pageTemplateId: this.getPageTemplateId,
		}
	},
	computed: {
		itemClasses() {
			return [
				'jet-theme-builder__template-item',
				this.pageTemplateType ? `${ this.pageTemplateType }-type` : 'unassigned-type',
				this.progressState ? 'progress-state' : ''
			];
		},
		contextMenuOptions() {
			return [
				{
					icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 17.2501V21.0002H6.75L17.81 9.94015L14.06 6.19015L3 17.2501ZM20.71 7.04015C21.1 6.65015 21.1 6.02015 20.71 5.63015L18.37 3.29015C17.98 2.90015 17.35 2.90015 16.96 3.29015L15.13 5.12015L18.88 8.87015L20.71 7.04015Z" fill="#23282D"/></svg>',
					label: 'Edit conditions',
					action: 'edit-conditions',
				},
				{
					icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.9998 1.00018H3.99982C2.89982 1.00018 1.99982 1.90018 1.99982 3.00018V17.0002H3.99982V3.00018H15.9998V1.00018ZM14.9998 5.00018L20.9998 11.0002V21.0002C20.9998 22.1002 20.0998 23.0002 18.9998 23.0002H7.98982C6.88982 23.0002 5.99982 22.1002 5.99982 21.0002L6.00982 7.00018C6.00982 5.90018 6.89982 5.00018 7.99982 5.00018H14.9998ZM13.9998 12.0002H19.4998L13.9998 6.50018V12.0002Z" fill="#23282D"/></svg>',
					label: 'Duplicate',
					action: 'copy-template',
				},
				{
					icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.79999 16.6H14.8V10.6H18.8L11.8 3.60001L4.79999 10.6H8.79999V16.6ZM4.79999 18.6H18.8V20.6H4.79999V18.6Z" fill="#23282D"/></svg>',
					label: 'Export',
					action: 'export-template',
				},
				{
					icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.99982 19C5.99982 20.1 6.89982 21 7.99982 21H15.9998C17.0998 21 17.9998 20.1 17.9998 19V7H5.99982V19ZM18.9998 4H15.4998L14.4998 3H9.49982L8.49982 4H4.99982V6H18.9998V4Z" fill="#23282D"/></svg>',
					label: 'Delete',
					action: 'delete-template',
				},
			];
		},
		pageTemplateType() {
			return this.templateData.type;
		},
		pageTemplateTypeName() {
			let templateTypeOptions = window.JetThemeBuilderConfig.templateTypeOptions,
				index = templateTypeOptions.findIndex( ( optionData, index ) => {
					return optionData.value === this.pageTemplateType;
				} );

			if ( -1 === index ) {
				return this.pageTemplateType;
			}

			return templateTypeOptions[ index ].label;
		},
		conditionVerboseList() {
			return this.templateData.conditions.map( ( condition ) => {
				let rawConditionsData = this.$store.state.rawConditionsData,
				    groupData = rawConditionsData[ condition.group ],
				    groupLabel,
				    subGroupLabel;

				groupLabel = groupData['label'];
				subGroupLabel = groupData.hasOwnProperty( 'sub-groups' ) ? groupData['sub-groups'][ condition.subGroup ]['label'] : '';

				return {
					include: condition.include,
					group: groupLabel,
					subGroup: subGroupLabel,
					subGroupValue: condition.subGroupValue,
					subGroupValueVerbose: condition.subGroupValueVerbose,
				};
			} );
		},
		mainConditionVerbose() {
			let conditionData,
			    group = '',
			    subGroup = '',
			    subGroupValuesVerbose = '';

			if ( 0 === this.conditionVerboseList.length ) {
				return '';
			}

			conditionData = this.conditionVerboseList.find( ( conditionData ) => {
				return 'true' === conditionData.include;
			} );

			if ( ! conditionData ) {
				return '';
			}

			group = conditionData.group;
			subGroup = conditionData.subGroup;

			if ( subGroup ) {
				subGroup = ` - ${ subGroup }`;
			}

			if ( conditionData.subGroupValue ) {
				subGroupValuesVerbose = `: ${ conditionData.subGroupValueVerbose.join( ', ' ) }`;
			}

			return `${ group }${ subGroup }${ subGroupValuesVerbose }`;
		},
		moreConditionsLength() {

			if ( 1 >= this.conditionVerboseList.length ) {
				return false;
			}

			return this.conditionVerboseList.length - 1;
		},
		isUnassignedConditions() {
			return '' === this.mainConditionVerbose;
		}
	},
	methods: {
		openOptionsHandler() {
			this.isOptionsVisible = ! this.isOptionsVisible;
		},
		optionClickHandler( payload ) {
			this.isOptionsVisible = false;

			switch ( payload.action ) {
				case 'edit-conditions':
					this.$store.dispatch( 'openConditionsPopup', {
						pageTemplateId: this.templateData.id,
					} );
					break;
				case 'copy-template':
					this.copyTemplateHandler();
					break;
				case 'export-template':
					window.open( this.templateData.exportLink, '_self' );
					break;
				case 'delete-template':
					this.removeTemplateHandler();
					break;
			}
		},
		onClickAwayHandler( ) {
			this.isOptionsVisible = false;
		},
		copyTemplateHandler() {
			this.progressState = true;

			wp.apiFetch( {
				method: 'post',
				path: window.JetThemeBuilderConfig.copyPageTemplatePath,
				data: {
					id: this.templateData.id,
				}
			} ).then( ( response ) => {
				this.progressState = false;

				if ( response.success ) {
					this.$store.commit( 'updateRawPageTemplateList', {
						list: response.data.list,
					} );
				}
			} );
		},
		removeTemplateHandler() {
			this.progressState = true;

			wp.apiFetch( {
				method: 'post',
				path: window.JetThemeBuilderConfig.removePageTemplatePath,
				data: {
					id: this.templateData.id,
				}
			} ).then( ( response ) => {
				this.progressState = false;

				if ( response.success ) {
					this.$store.commit( 'updateRawPageTemplateList', {
						list: response.data.list,
					} );
				}
			} );
		},
		onInputTemplateNameHandler( templateName ) {
			this.$store.dispatch( 'updatePageTemplateName', {
				pageTemplateId: this.templateData.id,
				name: templateName,
			} );
		},
		getPageTemplateType() {
			let pageTemplateConditions = this.templateData.conditions;

			if ( 0 == pageTemplateConditions.length ) {
				return false
			}

			let subGroup = pageTemplateConditions[0].subGroup,
			    allConditionsList = this.$store.getters.getConditionsList;

			if ( ! allConditionsList.hasOwnProperty( subGroup ) ) {
				return false;
			}

			return allConditionsList[ subGroup ].bodyStructure;
		}
	}
}

</script>

<style lang="scss">
	.jet-theme-builder__template-item {
		display: flex;
		flex-direction: column;
		justify-content: flex-start;
		position: relative;
		min-width: 25%;
		height: auto;
		min-height: 275px;
		background-color: white;
		border-radius: 4px;
		box-shadow: 0px 2px 6px rgba(35, 40, 45, 0.07);
		transition: box-shadow .3s cubic-bezier(.35,.77,.38,.96);
	}

	.jet-theme-builder__template-header {
		display: flex;
		flex-direction: column;
		justify-content: flex-start;
		align-items: stretch;
		gap: 12px;
		padding: 14px 12px;
		position: relative;
		padding-bottom: 15px;
		margin-bottom: 12px;

		&:after {
			content: '';
			display: block;
			position: absolute;
			bottom: -1px;
			left: 12px;
			width: calc(100% - 24px);
			height: 2px;
			background-color: #E0E0E0;
		}

		.unassigned-type & {
			background-color: #E0E0E0;

			&:after {
				display: none;
			}
		}

		&-main {
			display: flex;
			justify-content: flex-start;
			align-items: center;
			gap: 6px;
		}
	}

	.jet-theme-builder__template-label {
		flex: 1 1 auto;

		.jet-editable-input {
			position: relative;

			.jet-editable-input__icon {
				svg, path {
					fill: var(--border-color);
				}
			}

			.jet-editable-input__input {
				color: var(--primary-text-color);
				font-size: 15px;
				line-height: 1.5;
				font-weight: 500;
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

	.jet-theme-builder__template-type-badge {
		color: white;
		padding: 4px 6px;
		border-radius: 4px;
		line-height: 1.3;
		background-color: var(--border-color);
		font-weight: 500;

		.jet_page-type & {
			background-color: var(--page-type-color);
		}

		.jet_archive-type & {
			background-color: var(--archive-type-color);
		}

		.jet_single-type & {
			background-color: var(--single-type-color);
		}
	}

	.jet-theme-builder__template-body {
		display: flex;
		flex-direction: column;
		justify-content: flex-start;
		align-items: stretch;
		gap: 12px;
		flex: 1 1 auto;
		padding: 0 12px;
	}

	.jet-theme-builder__template-footer {
		display: flex;
		flex-direction: column;
		justify-content: flex-start;
		align-items: stretch;
		padding: 0 12px 12px 12px;
		margin-top: 18px;
	}

	.jet-theme-builder__template-controls {
		display: flex;
		justify-content: flex-end;
		align-items: center;
		gap: 5px;
		position: relative;

		svg, path {
			fill: var(--link-color);
		}

		.options-template {
			color: var(--link-color);
		}
	}

	.jet-theme-builder__template-control {
		width: 20px;
		cursor: pointer;
	}

	.jet-theme-builder__template-conditions-main {
		color: var(--primary-text-color);
		font-size: 13px;

		.unassigned-conditions {
			color: var( --error-color );
			font-weight: 400;
		}
	}

	.jet-theme-builder__template-meta {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		gap: 12px;
		margin-top: 10px;
		border-top: 1px solid #E0E0E0;
		padding-top: 8px;

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

</style>
