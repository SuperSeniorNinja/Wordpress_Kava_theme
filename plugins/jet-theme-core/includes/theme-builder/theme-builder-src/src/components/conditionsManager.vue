<template>
	<div
		:class="itemClasses"
	>
		<div class="jet-theme-builder-conditions-manager__container">
			<div class="jet-theme-builder-conditions-manager__header" v-if="!isPageTemplateCreateMode">
				<div class="jet-theme-builder-conditions-manager__header-title">Set the page template visibility conditions</div>
				<div class="jet-theme-builder-conditions-manager__header-message">
					<span>Here you can set one or multiple conditions, according to which the given template will be shown on specific pages or not.</span>
				</div>
			</div>
			<div class="jet-theme-builder-conditions-manager__header" v-if="isPageTemplateCreateMode">
				<div class="jet-theme-builder-conditions-manager__header-title">Create page template</div>
				<div class="jet-theme-builder-conditions-manager__header-message">
					<span>Here you can set one or multiple conditions, according to which the given template will be shown on specific pages or not.</span>
				</div>
			</div>
			<div class="jet-theme-builder-conditions-manager__list">
				<div class="jet-theme-builder-conditions-manager__list-inner" v-if="!emptyConditions">
					<transition-group name="conditions-list-anim" tag="div">
						<conditionsItem
							v-for="сondition in templateConditions"
							:key="сondition.id"
							:id="сondition.id"
							:rawCondition="сondition"
							@remove-condition="removeCondition"
						></conditionsItem>
					</transition-group>
				</div>
				<div class="jet-theme-builder-conditions-manager__add-condition">
					<cx-vui-button
						button-style="default"
						class="cx-vui-button--style-link-accent"
						size="mini"
						@on-click="addCondition"
					>
						<template v-slot:label>
							<span class="svg-icon">
								<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M16.3332 10.8334H11.3332V15.8334H9.6665V10.8334H4.6665V9.16675H9.6665V4.16675H11.3332V9.16675H16.3332V10.8334Z" fill="#007CBA"/>
								</svg>
							</span>
							<span>Add Condition</span>
						</template>
					</cx-vui-button>
				</div>
				<div class="jet-theme-builder-conditions-manager__controls">
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
						v-if="!isPageTemplateCreateMode"
						:loading="progressState"
						size="mini"
						@on-click="saveConditions"
					>
						<template v-slot:label>
							<span>Save</span>
						</template>
					</cx-vui-button>
					<cx-vui-button
						button-style="default"
						class="cx-vui-button--style-accent"
						v-if="isPageTemplateCreateMode"
						:loading="progressState"
						size="mini"
						@on-click="createPageTemplate"
					>
						<template v-slot:label>
							<span>Create</span>
						</template>
					</cx-vui-button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import conditionsItem from './conditionsItem.vue';

export default {
	name: 'conditionsManager',
	components: {
		conditionsItem
	},
	data() {
		return {
			progressState: false,
			rawConditionsData: window.JetThemeBuilderConfig.rawConditionsData,
			conditions: [],
			getConditionsStatus: false,
		}
	},
	created() {
		this.getConditions();
	},
	computed: {
		itemClasses() {
			return [
				'jet-theme-builder-conditions-manager',
				this.progressState ? 'progress-state' : '',
			];
		},
		emptyConditions() {
			return ( 0 === this.conditions.length ) ? true : false;
		},
		templateConditions() {
			return this.conditions;
		},
		isPageTemplateCreateMode() {
			return ! this.$store.state.pageTemplateId ? true : false;
		}
	},
	methods: {
		genetateUniqId: function() {
			return '_' + Math.random().toString(36).substr(2, 9);
		},

		addCondition: function() {
			let newCond = {
				id: this.genetateUniqId(),
				include: 'true',
				group: 'entire',
				subGroup: 'entire',
				subGroupValue: '',
				subGroupValueVerbose: '',
				priority: 100,
			};

			this.conditions.unshift( newCond );
		},

		removeCondition: function( conditionId = false ) {
			this.conditions = this.conditions.filter( function( condition ) {
				return condition['id'] !== conditionId;
			} );
		},

		getConditions: function () {

			if ( ! this.$store.state.pageTemplateId ) {
				return false;
			}

			this.progressState = true;

			wp.apiFetch( {
				method: 'post',
				path: window.JetThemeBuilderConfig.getPageTemplateConditionsPath,
				data: {
					template_id: this.$store.state.pageTemplateId,
				},
			} ).then( ( response ) => {

				this.progressState = false;

				if ( response.success ) {
					this.conditions = response.data.conditions;

					this.$store.commit( 'updateEditablePageTemplateConditions', {
						conditions: this.conditions,
					} );
				} else {
					console.log('getPageTemplateConditions')
				}
			} );
		},

		saveConditions: function() {
			this.progressState = true;

			this.$store.commit( 'updateEditablePageTemplateConditions', {
				conditions: this.templateConditions,
			} );

			this.$store.dispatch( 'updatePageTemplateConditions', {
				conditions: this.templateConditions,
			} );

			setTimeout( () => {
				this.progressState = false;
			}, 500 );

		},

		cancelHandler: function() {
			this.$store.dispatch( 'closeConditionsPopup' );
		},

		createPageTemplate() {
			this.progressState = true;

			wp.apiFetch( {
				method: 'post',
				path: window.JetThemeBuilderConfig.createPageTemplatePath,
				data: {
					name: '',
					conditions: this.conditions,
				},
			} ).then( ( response ) => {
				this.progressState = false;

				if ( response.success ) {
					this.$store.commit( 'updateRawPageTemplateList', {
						list: response.data.list,
					} );

					this.$store.commit( 'updatePageTemplateId', {
						id: response.data.newTemplateId,
					} );

					this.$store.dispatch( 'updatePageTemplateConditions', {
						conditions: this.templateConditions,
					} );

					this.$store.dispatch( 'closeConditionsPopup' );
				}
			} );
		}
	}
}
</script>

<style lang="scss">

.jet-theme-builder-conditions-manager {
	display: flex;
	flex-direction: column;
	align-items: stretch;

	&__container {
		display: flex;
		flex-direction: column;
		align-items: stretch;
	}

	&__controls {
		display: flex;
		justify-content: flex-end;
		align-items: center;
		gap: 16px;
		margin-top: 16px;
		border-top: 1px solid #E0E0E0;
		padding-top: 32px;
	}

	&__header {
		display: flex;
		flex-direction: column;
		align-items: center;
		text-align: center;
		margin-bottom: 32px;

		&-title {
			font-style: normal;
			font-size: 21px;
			color: var(--primary-text-color);
			margin-bottom: 10px;
		}

		&-message {
			display: flex;
			flex-direction: column;
			color: #7b7e81;
			max-width: 600px;
		}
	}

	&__list {
		display: flex;
		flex-direction: column;
		align-items: stretch;
	}

	&__list-inner {
		max-height: 280px;
		margin-bottom: 16px;

		& > div {
			display: flex;
			flex-direction: column;
			align-items: stretch;
			gap: 16px;
		}
	}

	&__add-condition {
		display: flex;
		justify-content: flex-start;
		align-items: center;
	}
}

.conditions-list-enter-active,
.conditions-list-leave-active {
	transition: all .3s cubic-bezier(.35,.77,.38,.96);
}
.conditions-list-enter-from {
	opacity: 0;
}
.conditions-list-leave-to {
	opacity: 0;
	transition-duration: 0s;
}
.conditions-list-move {
	transition: all .3s cubic-bezier(.35,.77,.38,.96);
}

</style>
