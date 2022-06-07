<?php
/**
 * Available widgets dashboard template
 */
?>
<div class="jet-cw-settings-page jet-cw-settings-page__avaliable-addons">
	<div class="avaliable-widgets">
		<div class="avaliable-widgets__option-info">
			<div class="avaliable-widgets__option-info-name"><?php _e( 'Available Widgets', 'jet-cw' ); ?></div>
			<div class="avaliable-widgets__option-info-desc"><?php _e( 'List of widgets that will be available when editing the page', 'jet-cw' ); ?></div>
		</div>
		<div class="avaliable-widgets__controls">
			<div
					class="avaliable-widgets__control"
					v-for="(option, index) in pageOptions.avaliable_widgets.options">
				<cx-vui-switcher
						:key="index"
						:name="`avaliable-widget-${option.value}`"
						:label="option.label"
						:wrapper-css="[ 'equalwidth' ]"
						return-true="true"
						return-false="false"
						v-model="pageOptions.avaliable_widgets.value[option.value]"
				>
				</cx-vui-switcher>
			</div>
		</div>
	</div>
</div>

