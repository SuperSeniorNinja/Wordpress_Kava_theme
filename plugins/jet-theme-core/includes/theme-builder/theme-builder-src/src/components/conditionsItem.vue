<template>
	<div
		:class="itemClasses"
	>
		<div class="jet-theme-builder-conditions-manager__conditions-item-control include-control select-type">
			<cx-vui-select
				:prevent-wrap="true"
				:options-list="[
					{
						value: 'true',
						label: 'Include'
					},
					{
						value: 'false',
						label: 'Exclude'
					}
				]"
				:value="сondition.include"
				@on-input="includeInputHandler"
			></cx-vui-select>
		</div>
		<div class="jet-theme-builder-conditions-manager__conditions-item-control group-control select-type" v-if="groupVisible">
			<cx-vui-select
				:wrapper-css="[ 'equalwidth' ]"
				:prevent-wrap="true"
				:options-list="groupOptions"
				:value="сondition.group"
				@on-input="groupInputHandler"
			>
			</cx-vui-select>
		</div>
		<div class="jet-theme-builder-conditions-manager__conditions-item-control sub-group-control select-type" v-if="subGroupVisible">
			<cx-vui-select
				:wrapper-css="[ 'equalwidth' ]"
				:prevent-wrap="true"
				:options-list="subGroupOptions"
				:value="сondition.subGroup"
				@on-input="subGroupInputHandler"
			>
			</cx-vui-select>
		</div>
		<div class="jet-theme-builder-conditions-manager__conditions-item-control sub-group-value-control" :class="[ `${subGroupValueControl.type}-type` ]" v-if="subGroupValueVisible">
			<cx-vui-input
				v-if="'input' === subGroupValueControl.type"
				:placeholder="subGroupValueControl.placeholder"
				:wrapper-css="[ 'equalwidth' ]"
				:prevent-wrap="true"
				:value="сondition.subGroupValue"
				@on-change="subGroupValueInputHandler($event.target.value)"
			></cx-vui-input>
			<cx-vui-select
				v-if="'select' === subGroupValueControl.type"
				:placeholder="subGroupValueControl.placeholder"
				:wrapper-css="[ 'equalwidth' ]"
				:prevent-wrap="true"
				:options-list="subGroupValueOptions"
				:value="сondition.subGroupValue"
				@on-input="subGroupValueInputHandler"
			>
			</cx-vui-select>
			<cx-vui-f-select
				v-if="'f-select' === subGroupValueControl.type"
				:placeholder="subGroupValueControl.placeholder"
				:wrapper-css="[ 'equalwidth' ]"
				:prevent-wrap="true"
				:options-list="subGroupValueOptions"
				:multiple="true"
				:value="сondition.subGroupValue"
				@on-input="subGroupValueInputHandler"
			></cx-vui-f-select>
		</div>
		<div class="jet-theme-builder-conditions-manager__conditions-item-delete">
			<div @click="removeCondition" class="svg-icon">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M5.99982 19C5.99982 20.1 6.89982 21 7.99982 21H15.9998C17.0998 21 17.9998 20.1 17.9998 19V7H5.99982V19ZM18.9998 4H15.4998L14.4998 3H9.49982L8.49982 4H4.99982V6H18.9998V4Z" fill="#23282D"/>
				</svg>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'conditionsItem',
	components: {
		//contextMenu
	},
	props: {
		id: String,
		rawCondition: Object
	},
	data() {
		return {
			progressState: false,
			сondition: this.rawCondition,
			requestList: [],
		}
	},
	mounted() {},
	watch: {
		'сondition.group': function( curr, old ) {

			if ( this.subGroupAvaliable ) {
				let subGroups     = this.rawConditionsData[ this.сondition.group ]['sub-groups'],
				    subGroupsKeys = Object.keys( subGroups );

				this.сondition.subGroupValueVerbose = [];

				if ( 0 !== subGroupsKeys.length ) {
					this.сondition.subGroup = subGroupsKeys[0];

					switch ( this.subGroupValueControl.type ) {
						case 'f-select':
							this.сondition.subGroupValue = [];
							break;
						default:
							this.сondition.subGroupValue = '';
							break;
					}
				}
			}
		},
		'сondition.subGroup': function( subGroup ) {
			let conditionsList    = this.$store.getters.getConditionsList,
				conditionPriority = conditionsList.hasOwnProperty( subGroup ) ? conditionsList[ subGroup ].priority : 100;

			this.сondition.priority = conditionPriority;
			this.сondition.subGroupValueVerbose = [];

			if ( this.subGroupAvaliable ) {
				switch ( this.subGroupValueControl.type ) {
					case 'f-select':
						this.сondition.subGroupValue = [];
						break;
					default:
						this.сondition.subGroupValue = '';
						break;
				}
			}
		}
	},
	computed: {
		itemClasses() {
			return [
				'jet-theme-builder-conditions-manager__conditions-item',
				this.progressState ? 'progress-state' : '',
			];
		},
		rawConditionsData: function () {
			return this.$store.state.rawConditionsData;
		},
		groupVisible: function() {
			return true;
		},
		subGroupVisible: function() {
			return 0 !== this.subGroupOptions.length ? true : false;
		},
		subGroupValueVisible: function() {
			return this.subGroupValueControl ? true : false;
		},
		subGroupValueControl: function() {

			if ( ! this.subGroupAvaliable ) {
				return false;
			}

			let subGroupData = this.rawConditionsData[ this.сondition.group ]['sub-groups'][ this.сondition.subGroup ];

			return subGroupData.control;
		},
		subGroupItemAction: function() {

			if ( ! this.subGroupAvaliable ) {
				return false;
			}

			let subGroupData = this.rawConditionsData[ this.сondition.group ]['sub-groups'][ this.сondition.subGroup ];

			return subGroupData.action;
		},
		groupOptions: function() {
			let groupList = [],
			    groups    = this.rawConditionsData;

			for ( let group in groups ) {
				groupList.push( {
					value: group,
					label: groups[ group ]['label']
				} );
			}

			return groupList;
		},
		subGroupAvaliable: function() {
			return this.rawConditionsData[ this.сondition.group ].hasOwnProperty( 'sub-groups' );
		},
		subGroupOptions: function() {

			let optionsList = [];

			if ( ! this.subGroupAvaliable ) {
				return optionsList;
			}

			let subGroups = this.rawConditionsData[ this.сondition.group ]['sub-groups'];

			for ( let subGroup in subGroups ) {
				optionsList.push( {
					value: subGroup,
					label: subGroups[ subGroup ]['label']
				} );
			}

			return optionsList;
		},
		subGroupValueOptions: function() {
			let optionsList = [];

			if ( ! this.subGroupAvaliable ) {
				return optionsList;
			}

			if ( ! this.rawConditionsData[ this.сondition.group ]['sub-groups'].hasOwnProperty( this.сondition.subGroup ) ) {
				return optionsList;
			}

			let subGroupData = this.rawConditionsData[ this.сondition.group ]['sub-groups'][ this.сondition.subGroup ];

			if ( subGroupData.options ) {
				return subGroupData.options;
			}

			if ( this.subGroupItemAction ) {
				this.getRemoteItems();
			}

			return optionsList;
		}
	},
	methods: {
		removeCondition: function() {
			this.$emit( 'removeCondition', this.rawCondition.id );
		},

		includeInputHandler: function ( value ) {
			this.сondition.include = value;
		},

		groupInputHandler: function ( value ) {
			this.сondition.group = value;
		},

		subGroupInputHandler: function ( value ) {
			this.сondition.subGroup = value;
		},

		subGroupValueInputHandler: function ( value ) {
			this.сondition.subGroupValue = value;

			let subGroupValueVerbose = [];

			if ( Array.isArray( value ) ) {
				value.forEach( ( item ) => {
					let findedOption = this.subGroupValueOptions.find( ( option ) => {
						return option.value === item;
					} );

					if ( findedOption ) {
						subGroupValueVerbose.push( findedOption.label );
					}
				} );
			} else {
				let findedOption = this.subGroupValueOptions.find( ( option ) => {
					return option.value === value;
				} );

				if ( findedOption ) {
					subGroupValueVerbose.push( findedOption.label );
				} else {
					subGroupValueVerbose.push( value );
				}
			}

			this.сondition.subGroupValueVerbose = subGroupValueVerbose;
		},

		getRemoteItems: function( query = '' ) {
			this.progressState = true;

			wp.apiFetch( {
				method: 'post',
				path: `/jet-theme-core-api/v2/${ this.subGroupItemAction }`,
			} ).then( ( response ) => {
				this.progressState = false;

				if ( response.success ) {
					this.requestList = response.data;

					let conditionsData = this.rawConditionsData;

					conditionsData[ this.сondition.group ]['sub-groups'][ this.сondition.subGroup ]['options'] = response.data;

					this.$store.commit( 'updateRawConditionsData', {
						list: conditionsData,
					} );

					console.log( 'getRemoteItems Success' );
				} else {
					console.log( 'getRemoteItems Error' );
				}
			} );
		}
	}
}
</script>

<style lang="scss">

.jet-theme-builder-conditions-manager__conditions-item {
	display: flex;
	align-items: flex-start;
	gap: 10px;

	&-control {
		&.select-type,
		&.f-select-type,
		&.input-type {
			flex: 1 1 auto;

			input,
			select {
				width: 100%;
			}
		}

		&.include-control {
			max-width: 120px;
		}
	}

	&-delete {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 32px;

		.svg-icon {
			display: flex;
			justify-content: center;
			align-items: center;
			cursor: pointer;
			width: 20px;

			svg, path {
				fill: var(--error-color);
			}
		}
	}
}

</style>
