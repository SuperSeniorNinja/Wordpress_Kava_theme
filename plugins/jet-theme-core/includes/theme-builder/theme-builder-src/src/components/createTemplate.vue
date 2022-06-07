<template>
	<div
		class="jet-theme-builder__create-template"
		:class="itemClasses"
		@click="createPageTemplateHandler"
	>
		<div class="create-template-icon svg-icon">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18.9998 13.0001H12.9998V19.0001H10.9998V13.0001H4.9998V11.0001H10.9998V5.00012H12.9998V11.0001H18.9998V13.0001Z" fill="#23282D"/>
			</svg>
		</div>
		<div class="create-template-icon-text">Create new page template</div>
	</div>
</template>

<script>

export default {
	name: 'createTemplate',
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
		}
	},
	methods: {
		createPageTemplateHandler() {
			this.$store.dispatch( 'openConditionsPopup', {
				pageTemplateId: false,
			} );
		},
	}
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
	.jet-theme-builder__create-template {
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		gap: 20px;
		min-width: 25%;
		min-height: 275px;
		border-radius: 4px;
		background-color: white;
		cursor: pointer;
		box-shadow: 0px 2px 6px rgba(35, 40, 45, 0.07);
		transition: box-shadow .3s cubic-bezier(.35,.77,.38,.96);

		&:hover {
			box-shadow: 5px 5px 15px rgb(35 40 45 / 15%);
		}

		.create-template-icon {
			width: 28px;
			height: 28px;
			background-color: #F0F0F1;
			border-radius: 50%;
		}

		.create-template-icon-text {
			font-size: 15px;
			font-weight: 500;
		}
	}
</style>
