<template>
	<div
		class="jet-theme-builder__context-menu"
		v-click-away="clickAwayHandler"
	>
		<div
			:class="[ 'jet-theme-builder__context-menu-item', optionData.action ]"
			v-for="(optionData, index) in optionsList"
			:key="index"
			@click="itemClickHandler(optionData.action)"
		>
			<span class="context-menu-item-icon" v-html="optionData.icon"></span><span class="context-menu-item-text">{{ optionData.label }}</span>
		</div>
	</div>
</template>

<script>

export default {
	name: 'contextMenu',
	props: {
		options: Array,
	},
	computed: {
		optionsList() {
			return this.options;
		}
	},
	methods: {
		itemClickHandler( action ) {
			this.$emit( 'onClickOption', { action } );
		},
		clickAwayHandler( event ) {
			this.$emit( 'onClickAway' );
		}
	}
}
</script>

<style lang="scss">
	.jet-theme-builder__context-menu {
		display: flex;
		flex-direction: column;
		justify-content: flex-start;
		align-items: stretch;
		min-width: 150px;
		border-radius: 4px;
		background-color: white;
		position: absolute;
		overflow: hidden;
		top: calc(100% + 5px);
		right: 0;
		box-shadow: 0px 4px 10px rgba(35, 40, 45, 0.3);
		z-index: 1;
		color: var( --secondary-text-color );
	}

	.jet-theme-builder__context-menu-item {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		gap: 8px;
		padding: 10px 12px;
		background-color: white;
		cursor: pointer;
		font-size: 13px;
		transition: all .3s cubic-bezier(.35,.77,.38,.96);

		.context-menu-item-icon {
			display: flex;
			justify-content: center;
			align-items: center;
			color: var(--secondary-text-color);
			width: 20px;

			svg, path {
				fill: var(--secondary-text-color);
			}
		}

		.context-menu-item-text {
			white-space: nowrap;
		}

		&:hover {
			background-color: #F0F0F1;
		}

		&.delete-template,
		&.clear-structure {
			.context-menu-item-icon {
				color: var(--error-color);

				svg, path {
					fill: var(--error-color);
				}
			}
		}
	}
</style>
