<template>
	<div
		:class="itemClasses"
	>
		<span class="dashicons dashicons-plus-alt2" v-if="isInclude"></span>
		<span class="dashicons dashicons-minus" v-if="!isInclude"></span>
		<span class="jet-theme-builder__template-conditions-sub-group">{{ conditionData.group }}</span>
		<i class="jet-theme-builder__template-conditions-sub-value">{{ subGroupValue }}</i>
	</div>
</template>

<script>

export default {
	name: 'templateConditionItem',
	props: {
		conditionData: Object
	},
	data() {
		return {}
	},
	computed: {
		itemClasses() {
			return [
				'jet-theme-builder__template-conditions-item',
				! this.isInclude ? 'exclude' : '',
			];
		},
		isInclude() {
			return 'true' === this.conditionData.include ? true : false;
		},
		subGroupValue() {

			if ( '' === this.conditionData.subGroupValue ) {
				return '';
			}

			if ( Array.isArray( this.conditionData.subGroupValue ) ) {
				return ': ' +  this.conditionData.subGroupValue.join( ', ' );
			}

			return ': ' + this.conditionData.subGroupValue;
		}
	},
}
</script>

<style lang="scss">
	.jet-theme-builder__template-conditions-item {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		gap: 3px;

		.dashicons {
			font-size: 12px;
			display: flex;
			justify-content: center;
			align-items: center;
		}
	}
</style>
