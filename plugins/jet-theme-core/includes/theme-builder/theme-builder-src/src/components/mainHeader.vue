<template>
	<div class="jet-theme-builder__header ">
		<div class="jet-theme-builder__header-info">
			<h1>Theme Builder</h1>
			<p>Create new page templates, set their display conditions, and apply custom theme templates to the header, body, and footer sections.</p>
		</div>
		<div class="jet-theme-builder__header-controls panel">
			<div class="jet-theme-builder__header-filters">
				<div class="jet-theme-builder__header-filter search-by-name-filter">
					<div class="cx-vui-component__label">Search by name</div>
					<cx-vui-input
						name="page-template-search-filter"
						placeholder="Enter name"
						:prevent-wrap="true"
						size="fullwidth"
						type="text"
						:value="searchText"
						@on-change="searchTextOnChangeHandler( $event.target.value )"
					>
					</cx-vui-input>
				</div>
				<div class="jet-theme-builder__header-filter filter-by-type-filter">
					<div class="cx-vui-component__label">Filter by type</div>
					<cx-vui-select
						name="page-template-filter-by-type"
						placeholder="Select type"
						:prevent-wrap="true"
						size="fullwidth"
						:options-list="getTemplateTypeOptions"
						:value="templateTypeFilterValue"
						@on-input="filterTypeOnInputHandler( $event )"
					>
					</cx-vui-select>
				</div>
			</div>
			<div class="jet-theme-builder__header-actions">
				<cx-vui-button
					button-style="accent-border"
					size="mini"
					@on-click="openImportPopupHandler"
				>
					<template v-slot:label>
						<span class="svg-icon">
							<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M15.6667 8H12.3333V3H7.33333V8H4L9.83333 13.8333L15.6667 8ZM4 15.5V17.1667H15.6667V15.5H4Z" fill="#007CBA"/>
							</svg>
						</span>
						<span>Import page template</span>
					</template>
				</cx-vui-button>
			</div>
		</div>

	</div>
</template>

<script>

export default {
	name: 'Header',
	props: {

	},
	data() {
		return {
			debounceInterval: null,
			searchText: '',
			templateTypeFilterValue: 'all',
		}
	},
	watch: {
		/*searchText( value ) {
			clearInterval( this.debounceInterval );
			this.debounceInterval = setTimeout( this.searchPageTemplatesHandler, 500 );
		},
		orderBy( value ) {
			this.searchPageTemplatesHandler();
		},*/
	},
	computed: {
		getTemplateTypeOptions() {
			return [
				{
					label: 'All',
					value: 'all',
				},
				{
					label: 'Page',
					value: 'jet_page',
				},
				{
					label: 'Archive',
					value: 'jet_archive',
				},
				{
					label: 'Single',
					value: 'jet_single',
				},
				{
					label: 'Unassigned',
					value: 'unassigned',
				},
			]
		}
	},
	methods: {
		openImportPopupHandler() {
			this.$store.dispatch( 'openImportPageTemplatePopup' );
		},
		searchTextOnChangeHandler( text ) {
			this.searchText = text;
			this.$store.state.filterPageTemplateTitle = text;
		},
		filterTypeOnInputHandler( type ) {
			this.templateTypeFilterValue = type;
			this.$store.state.filterPageTemplateType = type;
		},
		searchPageTemplatesHandler() {
			this.$store.state.updatePageTemplatesProgressState = true;

			wp.apiFetch( {
				method: 'post',
				path: window.JetThemeBuilderConfig.getPageTemplateListPath,
				data: {
					templateName: this.searchText,
					orderBy: this.orderBy,
				},
			} ).then( ( response ) => {
				this.$store.state.updatePageTemplatesProgressState = false;

				if ( response.success ) {
					this.$store.commit( 'updateRawPageTemplateList', {
						list: response.data,
					} );
				}
			} );
		}
	}
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
	.jet-theme-builder__header {
		display: flex;
		flex-direction: column;
		align-items: stretch;
	}

	.jet-theme-builder__header-info {
		margin-bottom: 16px;

		h1 {
			font-size: 24px;
			font-weight: 500;
		}
	}

	.jet-theme-builder__header-controls {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		gap: 16px;

		@media (max-width: 1439px) {
			flex-direction: column;
			align-items: stretch;
		}
	}

	.jet-theme-builder__header-actions {
		display: flex;
		justify-content: flex-end;
		align-items: center;
		gap: 16px;
		flex: 1 1 auto;

		@media (max-width: 1439px) {
			justify-content: flex-start;
		}
	}

	.jet-theme-builder__header-filters {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		gap: 16px;
		flex: 1 1 auto;
	}

	.jet-theme-builder__header-filter {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		gap: 12px;

		.cx-vui-component__label {
			font-weight: 400;
		}

		.cx-vui-component-raw {
			flex: 1 1 auto;
		}

		&.search-by-name-filter {
			 min-width: 350px;
		}

		&.filter-by-type-filter {
			min-width: 300px;
		}
	}
</style>
