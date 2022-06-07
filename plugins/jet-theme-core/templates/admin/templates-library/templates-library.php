<div id="jet-theme-core-template-library" class="jet-theme-core-template-library">
	<transition name="cx-popup">
		<cx-vui-popup
			class="jet-template-library__popup create-template-popup"
			v-model="newTemplatePopupVisible"
			:footer="false"
			body-width="false"
			@on-cancel="closeTemplatePopupHandler"
		>
            <div
                class="jet-template-library-form create-template-form"
                :class="{ 'progress-state': templateCreatingStatus }"
                slot="content"
            >
                <div class="jet-template-library-form__header">
                    <div class="jet-template-library-form__header-title">Create a template</div>
                    <p class="jet-template-library-form__header-sub-title">Here you can create a new theme template for the site locations.</p>
                </div>
                <div class="jet-template-library-form__body">
                    <cx-vui-select
                        name="templateType"
                        label="<?php _e( 'Template Type', 'jet-theme-core' ); ?>"
                        placeholder="<?php _e( 'Select template type', 'jet-menu' ); ?>"
                        :wrapper-css="[ 'vertical-fullwidth' ]"
                        size="fullwidth"
                        :options-list="getTemplateTypeOptions"
                        v-model="newTemplateData.type"
                    >
                    </cx-vui-select>
                    <cx-vui-select
                        name="templateContentType"
                        label="<?php _e( 'Template Content Type', 'jet-theme-core' ); ?>"
                        placeholder="<?php _e( 'Select template content type', 'jet-menu' ); ?>"
                        :wrapper-css="[ 'vertical-fullwidth' ]"
                        size="fullwidth"
                        :options-list="getTemplateContentTypeOptions"
                        v-model="newTemplateData.content"
                    >
                    </cx-vui-select>
                    <cx-vui-input
                        name="templateName"
                        label="<?php _e( 'Template Name', 'jet-theme-core' ); ?>"
                        placeholder="<?php _e( 'Enter template name(optional)', 'jet-menu' ); ?>"
                        :wrapper-css="[ 'vertical-fullwidth' ]"
                        size="fullwidth"
                        type="text"
                        v-model="newTemplateData.name"
                    >
                    </cx-vui-input>
                </div>
                <div class="jet-template-library-form__footer">
                    <cx-vui-button
                        button-style="default"
                        class="cx-vui-button--style-accent-border"
                        size="mini"
                        @on-click="closeTemplatePopupHandler"
                    >
                        <template v-slot:label>
                            <span>Cancel</span>
                        </template>
                    </cx-vui-button>
                    <cx-vui-button
                        button-style="default"
                        class="cx-vui-button--style-accent"
                        size="mini"
                        @click="createTemplateHandler"
                        :loading="templateCreatingStatus"
                    >
                        <span slot="label"><?php _e( 'Create Template', 'jet-theme-core' ); ?></span>
                    </cx-vui-button>
                </div>
            </div>
		</cx-vui-popup>
	</transition>
    <transition name="cx-popup">
        <cx-vui-popup
            class="jet-template-library__popup condition-manager-popup"
            v-model="conditionsManagerPopupVisible"
            :footer="false"
            body-width="false"
            @on-cancel="closeConditionsManagerPopupHandler"
        >
            <div class="cx-vui-popup__content-inner" slot="content">
                <jet-theme-core-template-conditions-manager :template-id="templateId"></jet-theme-core-template-conditions-manager>
            </div>
        </cx-vui-popup>
    </transition>
    <transition name="cx-popup">
        <cx-vui-popup
            class="jet-template-library__popup import-template-popup"
            :value="importTemplatePopupVisible"
            @on-cancel="importTemplatePopupCloseHandler"
            :header="false"
            :footer="false"
            body-width="false"
        >
            <template v-slot:content>
                <div
                    class="jet-template-library-form jet-theme-builder-form--import-page-template-form"
                >
                    <div class="jet-template-library-form__header">
                        <div class="jet-template-library-form__header-title">Import Template</div>
                        <p class="jet-template-library-form__header-sub-title">Here you can select a template file in the .json format and import it.</p>
                    </div>
                    <div class="jet-template-library-form__body">
                        <form enctype="multipart/form-data" novalidate>
                            <div class="dropbox">
                                <input
                                    type="file"
                                    ref="file"
                                    :disabled="importProgressState"
                                    @change="prepareToImport( $event.target.files )"
                                    accept=".json,application/json"
                                >
                            </div>
                        </form>
                    </div>
                    <div class="jet-template-library-form__footer">
                        <cx-vui-button
                            button-style="default"
                            class="cx-vui-button--style-accent-border"
                            size="mini"
                            @on-click="importTemplatePopupCloseHandler"
                        >
                            <template v-slot:label>
                                <span>Cancel</span>
                            </template>
                        </cx-vui-button>
                        <cx-vui-button
                            button-style="default"
                            class="cx-vui-button--style-accent"
                            size="mini"
                            @on-click="importPageTemplateHandler"
                            :loading="importProgressState"
                            :disabled="!readyToImport"
                        >
                            <template v-slot:label>
                                <span>Import</span>
                            </template>
                        </cx-vui-button>
                    </div>
                </div>
            </template>
        </cx-vui-popup>
    </transition>
</div>
