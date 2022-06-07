<template>
	<div :class="classes">
		<div class="jet-editable-input__icon" v-if="iconHtml" v-html="iconHtml"></div>
		<div class="jet-editable-input__input"
		     ref="input"
		     contenteditable="true"
		     @input="updateHTML"
		     @focus="focusHandler"
		     @blur="blurHandler"
		     v-html="value"
		     tabindex="0"
		     :data-placeholder="placeholder"
		></div>
	</div>
</template>

<script>

export default {
	name: 'editableInput',
	props: {
		value: String,
		isValid: {
			type: Boolean,
			default: true,
		},
		placeholder: {
			type: String,
			default: 'Input',
		},
		icon: {
			type: [ String, Boolean ],
			default: false,
		},
	},
	mounted: function () {
		//this.$el.innerHTML = this.value;
	},
	data: function() {
		return ( {
			isFocus: false,
			isEmpty: true,
			currentValue: this.value,
		} );
	},
	computed: {
		classes: function() {
			let classes = [
				'jet-editable-input',
				!this.isValid ? 'jet-editable-input--not-valid' : false,
				this.isFocus ? 'jet-editable-input--focus' : false,
				this.isPlaceholder ? 'jet-editable-input--placeholder' : false,
				this.iconHtml ? 'jet-editable-input--has-icon' : '',
			];

			return classes;
		},

		isPlaceholder: function() {

			if ( this.isFocus || ! this.isEmpty ) {
				return false;
			}

			return true;
		},

		iconHtml: function () {

			if ( ! this.icon ) {
				return '';
			}

			return this.icon;
		}
	},
	methods: {
		updateHTML: function( e ) {
			let currentValue = this.$refs.input.innerHTML.trim();

			this.currentValue = currentValue;
			this.$emit( 'on-input', this.currentValue );

			if ( 0 === currentValue.length ) {
				this.isEmpty = true;
			} else {
				this.isEmpty = false;
			}
		},

		focusHandler: function( e ) {
			this.$emit( 'on-focus', e.target );
			this.$emit( 'on-focus:value', this.currentValue );
			this.isFocus = true;
		},

		blurHandler: function( e ) {
			this.$emit( 'on-blur', e.target );
			this.$emit( 'on-blur:value', this.currentValue );
			this.isFocus = false;
		}
	}
}
</script>

<style lang="scss">

.jet-editable-input {
	position: relative;
	width: 100%;

	.jet-editable-input__icon {
		display: flex;
		justify-content: center;
		align-items: center;
		width: 16px;
		position: absolute;
		top: 7px;
		left: 4px;

		svg {
			width: 100%;
			height: auto;
		}
	}

	.jet-editable-input__input {
		padding: 6px 8px;
		border-radius: 4px;
	}

	&--has-icon {
		.jet-editable-input__input {
			padding-left: 24px;
		}
	}

	&--placeholder {
		.jet-editable-input__input {
			&:before {
				pointer-events: none;
				content: attr(data-placeholder);
			}
		}
	}

	&--not-valid {
		.jet-editable-input__input {
			&:after {
				display: block;
			}
		}
	}
}

</style>




