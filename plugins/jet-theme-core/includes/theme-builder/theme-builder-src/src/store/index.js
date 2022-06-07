import { createStore } from 'vuex'

export default createStore({
	state: {
		rawPageTemplateList: [],
		rawConditionsData: [],
		rawTemplateList: window.JetThemeBuilderConfig.templatesList,
		popupVisible: {
			createTemplate: false,
			conditionsPopup: false,
			templateLibrary: false,
			importPageTemplate: false,
		},
		updatePageTemplatesProgressState: false,
		filterPageTemplateTitle: '',
		filterPageTemplateType: 'all',
		pageTemplateId: false,
		pageTemplateConditions: [],
		templateId: false,
		templateStructureType: false,
		layoutStructureType: false,
	},
	getters: {
		getPageTemplateList: state => {
			let rawPageTemplateList = state.rawPageTemplateList;

			if ( '' !== state.filterPageTemplateTitle ) {
				rawPageTemplateList = rawPageTemplateList.filter( ( pageTemplateData ) => {
					return pageTemplateData.templateName.includes( state.filterPageTemplateTitle );
				} );
			}

			if ( 'all' !== state.filterPageTemplateType ) {
				rawPageTemplateList = rawPageTemplateList.filter( ( pageTemplateData ) => {
					return pageTemplateData.type == state.filterPageTemplateType;
				} );
			}

			return rawPageTemplateList;
		},
		getTemplateList: state => {
			return state.rawTemplateList;
		},
		getConditionsList: state => {
			let conditionsList = {};

			for ( const group in state.rawConditionsData ) {
				let subGpoups = state.rawConditionsData[ group ]['sub-groups'];

				for ( const subGroup in subGpoups ) {
					let subGpoupData = subGpoups[ subGroup ];

					conditionsList[ subGroup ] = subGpoupData;
				}
			}

			return conditionsList;
		},
	},
	mutations: {
		updateRawPageTemplateList: ( state, payload ) => {

			if ( ! payload.list ) {
				return;
			}

			state.rawPageTemplateList = payload.list;
		},
		updateRawTemplateList: ( state, payload ) => {

			if ( ! payload.list ) {
				return;
			}

			state.rawTemplateList = payload.list;
		},
		updateRawConditionsData: ( state, payload ) => {

			if ( ! payload.list ) {
				return;
			}

			state.rawConditionsData = payload.list;
		},
		updatePopupVisibleState: ( state, payload ) => {
			let popup = payload.popup,
			    visible = payload.visible;

			if ( ! state.popupVisible.hasOwnProperty( popup ) ) {
				return false;
			}

			state.popupVisible[ popup ] = visible;

		},

		updatePageTemplateId: ( state, payload ) => {
			state.pageTemplateId = payload.id;
		},

		updateEditablePageTemplateConditions: ( state, payload ) => {
			state.pageTemplateConditions = payload.conditions;
		},

		updateTemplateId: ( state, payload ) => {
			state.templateId = payload.id;
		},

		updateLayoutStructureType: ( state, payload ) => {
			state.layoutStructureType = payload.type;
		},

		updateTemplateStructureType: ( state, payload ) => {
			state.templateStructureType = payload.type;
		},
	},
	actions: {
		updatePageTemplateConditions: ( context, payload ) => {
			let pageTemplateList = context.state.rawPageTemplateList,
			    conditions       = payload.conditions || [];

			let index = pageTemplateList.findIndex( ( templateData, index ) => {
				return templateData.id === context.state.pageTemplateId;
			} );

			if ( -1 === index ) {
				return false;
			}

			conditions = conditions.sort( ( conditionA, conditionB ) => {
				return conditionA.priority - conditionB.priority;
			} );

			context.state.rawPageTemplateList[ index ]['conditions'] = conditions;

			if ( 0 !== conditions.length ) {
				let subGroup = conditions[0].subGroup,
				    allConditionsList = context.getters.getConditionsList;

				if ( allConditionsList.hasOwnProperty( subGroup ) ) {
					context.state.rawPageTemplateList[ index ]['type'] = allConditionsList[ subGroup ].bodyStructure;
				}

				context.state.rawPageTemplateList[ index ]['conditions'] = conditions;
			} else {
				context.state.rawPageTemplateList[ index ]['type'] = 'unassigned';
			}
		},
		updatePageTemplateStructureId: ( context, payload ) => {
			let pageTemplateList = context.state.rawPageTemplateList,
			    templateId       = payload.id || false;

			let index = pageTemplateList.findIndex( ( templateData, index ) => {
				return templateData.id === context.state.pageTemplateId;
			} );

			if ( -1 === index ) {
				return false;
			}

			if ( templateId ) {
				context.state.rawPageTemplateList[ index ]['layout'][ context.state.layoutStructureType ]['id'] = templateId;
			}
		},
		updatePageTemplateLayout: ( context, payload ) => {
			let pageTemplateList = context.state.rawPageTemplateList,
			    pageTemplateId   = payload.pageTemplateId,
			    structure        = payload.structure,
			    structureData    = payload.structureData;

			context.commit( 'updatePageTemplateId', {
				id: pageTemplateId
			} );

			context.commit( 'updateLayoutStructureType', {
				type: structure
			} );

			let index = pageTemplateList.findIndex( ( templateData, index ) => {
				return templateData.id === pageTemplateId;
			} );

			if ( -1 === index ) {
				return false;
			}

			context.state.rawPageTemplateList[ index ]['layout'][ structure ] = structureData;
		},

		updatePageTemplateName: ( context, payload ) => {
			let pageTemplateList = context.state.rawPageTemplateList,
			    pageTemplateName = payload.name,
			    pageTemplateId   = payload.pageTemplateId,
			    index            = pageTemplateList.findIndex( ( templateData, index ) => {
				    return templateData.id === pageTemplateId;
			    } );

			context.commit( 'updatePageTemplateId', {
				id: pageTemplateId,
			} );

			if ( -1 === index ) {
				return false;
			}

			context.state.rawPageTemplateList[ index ]['templateName'] = pageTemplateName;
		},

		updateTemplateData: ( context, payload ) => {
			let templatesList = context.state.rawTemplateList,
			    id   = payload.id || false,
			    name = payload.name || false;

			context.commit( 'updateTemplateId', {
				id: id,
			} );

			let index = templatesList.findIndex( ( templateData, index ) => {
				return templateData.id === id;
			} );

			if ( -1 === index ) {
				return false;
			}

			if ( name ) {
				context.state.rawTemplateList[ index ][ 'title' ] = name;
			}
		},

		openCreateTemplatePopup: ( context, payload = {} ) => {
			let pageTemplateId =  payload.pageTemplateId || false,
			    layoutStructureType = payload.layoutStructureType || false,
			    templateStructureType = payload.templateStructureType || false;

			if ( pageTemplateId ) {
				context.commit( 'updatePageTemplateId', {
					id: payload.pageTemplateId
				} );
			}

			if ( layoutStructureType ) {
				context.commit( 'updateLayoutStructureType', {
					type: payload.layoutStructureType
				} );
			}

			if ( templateStructureType ) {
				context.commit( 'updateTemplateStructureType', {
					type: payload.templateStructureType
				} );
			}

			context.commit( 'updatePopupVisibleState', {
				popup: 'createTemplate',
				visible: true,
			} );
		},
		closeCreateTemplatePopup: ( context ) => {
			context.commit( 'updatePopupVisibleState', {
				popup: 'createTemplate',
				visible: false,
			} );
		},
		openTemplateLibraryPopup: ( context, payload ) => {
			context.commit( 'updatePageTemplateId', {
				id: payload.pageTemplateId
			} );

			context.commit( 'updateLayoutStructureType', {
				type: payload.layoutStructureType
			} );

			context.commit( 'updateTemplateStructureType', {
				type: payload.templateStructureType
			} );

			context.commit( 'updatePopupVisibleState', {
				popup: 'templateLibrary',
				visible: true,
			} );
		},
		closeTemplateLibraryPopup: ( context ) => {
			context.commit( 'updatePopupVisibleState', {
				popup: 'templateLibrary',
				visible: false,
			} );
		},
		openConditionsPopup: ( context, payload ) => {

			context.commit( 'updatePageTemplateId', {
				id: payload.pageTemplateId
			} );

			context.commit( 'updatePopupVisibleState', {
				popup: 'conditionsPopup',
				visible: true,
			} );
		},
		closeConditionsPopup: ( context ) => {
			context.commit( 'updatePopupVisibleState', {
				popup: 'conditionsPopup',
				visible: false,
			} );
		},
		openImportPageTemplatePopup: ( context, payload ) => {
			context.commit( 'updatePopupVisibleState', {
				popup: 'importPageTemplate',
				visible: true,
			} );
		},
		closeImportPageTemplatePopup: ( context, payload ) => {
			context.commit( 'updatePopupVisibleState', {
				popup: 'importPageTemplate',
				visible: false,
			} );
		},
	},
});
