<div
    class="jet-theme-core-conditions-manager wp-admin"
    :class="{ 'progress-state': getConditionsStatus }"
>
	<div class="jet-theme-core-conditions-manager__container">
		<div class="jet-theme-core-conditions-manager__blank">
			<div class="jet-theme-core-conditions-manager__blank-title"><?php echo __( 'Set the page template visibility conditions', 'jet-theme-core' ); ?></div>
			<div class="jet-theme-core-conditions-manager__blank-message">
				<span><?php echo __( 'Here you can set one or multiple conditions, according to which the given template will be either shown or hidden on specific pages.', 'jet-theme-core' ); ?></span>
			</div>
		</div>
		<div class="jet-theme-core-conditions-manager__list">
			<div class="jet-theme-core-conditions-manager__list-inner" v-if="!emptyConditions">
				<transition-group name="conditions-list-anim" tag="div">
					<jet-theme-core-template-conditions-item
						v-for="сondition in templateConditions"
						:key="сondition.id"
						:id="сondition.id"
						:rawCondition="сondition"
					></jet-theme-core-template-conditions-item>
				</transition-group>
			</div>
            <div class="jet-theme-core-conditions-manager__add-condition">
                <cx-vui-button
                    button-style="default"
                    class="cx-vui-button--style-link-accent"
                    size="mini"
                    @click="addCondition"
                >
                    <template v-slot:label>
                        <span class="svg-icon">
                            <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.3332 10.8334H11.3332V15.8334H9.6665V10.8334H4.6665V9.16675H9.6665V4.16675H11.3332V9.16675H16.3332V10.8334Z" fill="#007CBA"/>
                            </svg>
                        </span>
                        <span><?php echo __( 'Add condition', 'jet-theme-core' ); ?></span>
                    </template>
                </cx-vui-button>
            </div>
		</div>
	</div>
	<div class="jet-theme-core-conditions-manager__controls">
        <cx-vui-button
            button-style="default"
            class="cx-vui-button--style-accent-border"
            size="mini"
            @click="closeConditionsManagerPopupHandler"
        >
            <template v-slot:label>
                <span>Cancel</span>
            </template>
        </cx-vui-button>
		<cx-vui-button
            button-style="default"
            class="cx-vui-button--style-accent"
			:loading="saveConditionsStatus"
			size="mini"
			@click="saveConditions"
		>
            <template v-slot:label>
                <span><?php echo __( 'Save Conditions', 'jet-theme-core' ); ?></span>
            </template>
		</cx-vui-button>
	</div>
</div>
