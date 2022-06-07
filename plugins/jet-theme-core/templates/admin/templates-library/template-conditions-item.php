<div
	class="jet-theme-core-conditions-manager__item"
	:class="{ 'progress-state': requestLoading }"
>
	<div class="jet-theme-core-conditions-manager__item-control select-type">
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
			v-model="сondition.include"
		></cx-vui-select>
	</div>
	<div class="jet-theme-core-conditions-manager__item-control select-type" v-if="groupVisible">
		<cx-vui-select
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			:options-list="groupOptions"
			v-model="сondition.group"
		>
		</cx-vui-select>
	</div>
	<div class="jet-theme-core-conditions-manager__item-control select-type" v-if="subGroupVisible">
		<cx-vui-select
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			:options-list="subGroupOptions"
			v-model="сondition.subGroup"
		>
		</cx-vui-select>
	</div>
	<div class="jet-theme-core-conditions-manager__item-control select-type" v-if="subGroupValueVisible">
		<cx-vui-input
			v-if="'input' === subGroupValueControl.type"
			:placeholder="subGroupValueControl.placeholder"
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			v-model="сondition.subGroupValue"
		></cx-vui-input>
		<cx-vui-select
			v-if="'select' === subGroupValueControl.type"
			:placeholder="subGroupValueControl.placeholder"
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			:options-list="subGroupValueOptions"
			v-model="сondition.subGroupValue"
		>
		</cx-vui-select>
		<cx-vui-f-select
			v-if="'f-select' === subGroupValueControl.type"
			:placeholder="subGroupValueControl.placeholder"
			:wrapper-css="[ 'equalwidth' ]"
			:prevent-wrap="true"
			:options-list="subGroupValueOptions"
			:multiple="true"
			v-model="сondition.subGroupValue"
		></cx-vui-f-select>
	</div>
	<div class="jet-theme-core-conditions-manager__item-delete" @click="deleteCondition">
        <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6.49976 19C6.49976 20.1 7.39976 21 8.49976 21H16.4998C17.5998 21 18.4998 20.1 18.4998 19V7H6.49976V19ZM19.4998 4H15.9998L14.9998 3H9.99976L8.99976 4H5.49976V6H19.4998V4Z" fill="#D6336C"/>
        </svg>
    </div>
</div>
