<template>
	<createTemplate/>
	<transition-group name="page-template-list">
		<templateItem
			v-for="(templateData, index) in pageTemplateList"
			:key="index"
			:templateData="templateData"
		></templateItem>
	</transition-group>
</template>

<script>
import { mapState } from 'vuex';
import templateItem from './templateItem.vue';
import createTemplate from './createTemplate.vue';

export default {
	name: 'itemList',
	components: {
		templateItem,
		createTemplate
	},
	computed: {
		pageTemplateList() {
			return this.$store.getters.getPageTemplateList;
		},
	},
	methods: {
	}
}
</script>

<style lang="scss">
	.page-template-list-enter-active,
	.page-template-list-leave-active {
		transition: all .3s cubic-bezier(.35,.77,.38,.96);
	}
	.page-template-list-enter-from {
		opacity: 0;
	}
	.page-template-list-leave-to {
		opacity: 0;
		transition-duration: 0s;
	}
	.page-template-list-move {
		transition: all .3s cubic-bezier(.35,.77,.38,.96);
	}
</style>

