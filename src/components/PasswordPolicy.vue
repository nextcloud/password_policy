<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<script setup lang="ts">
import type { IPasswordPolicy } from '../types'
import { t } from '@nextcloud/l10n'
import { computed } from 'vue'

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcInputField from '@nextcloud/vue/dist/Components/NcInputField.js'
import IconTrashbin from 'vue-material-design-icons/TrashCan.vue'

const props = defineProps<{
	modelValue: IPasswordPolicy
	heading: string
	canRemove: boolean
}>()

const emit = defineEmits<{
	(e: 'update:modelValue', v: IPasswordPolicy): void
	(e: 'remove'): void
}>()

const minLength = computed({
	get: () => props.modelValue.minLength,
	set: (minLength: number) => emit('update:modelValue', { ...props.modelValue, minLength: Number.isNaN(minLength) ? 0 : minLength }),
})
const enforceHaveIBeenPwned = computed({
	get: () => props.modelValue.enforceHaveIBeenPwned,
	set: (enforceHaveIBeenPwned: boolean) => emit('update:modelValue', { ...props.modelValue, enforceHaveIBeenPwned }),
})
const enforceNonCommonPassword = computed({
	get: () => props.modelValue.enforceNonCommonPassword,
	set: (enforceNonCommonPassword: boolean) => emit('update:modelValue', { ...props.modelValue, enforceNonCommonPassword }),
})
const enforceNumericCharacters = computed({
	get: () => props.modelValue.enforceNumericCharacters,
	set: (enforceNumericCharacters: boolean) => emit('update:modelValue', { ...props.modelValue, enforceNumericCharacters }),
})
const enforceSpecialCharacters = computed({
	get: () => props.modelValue.enforceSpecialCharacters,
	set: (enforceSpecialCharacters: boolean) => emit('update:modelValue', { ...props.modelValue, enforceSpecialCharacters }),
})
const enforceUpperLowerCase = computed({
	get: () => props.modelValue.enforceUpperLowerCase,
	set: (enforceUpperLowerCase: boolean) => emit('update:modelValue', { ...props.modelValue, enforceUpperLowerCase }),
})
</script>

<template>
	<form @submit.prevent="">
		<div :class="$style.headingWrapper">
			<h3 :class="$style.heading">
				{{ heading }}
			</h3>
			<NcButton v-if="canRemove"
				:aria-label="t('password_policy', 'Remove policy')"
				:title="t('password_policy', 'Remove policy')"
				type="tertiary"
				@click="$emit('remove')">
				<template #icon>
					<IconTrashbin :size="20" />
				</template>
			</NcButton>
		</div>
		<NcInputField v-model="minLength"
			:class="$style.inputField"
			:label="t('password_policy', 'Minimum password length')"
			type="number"
			min="0" />
		<NcCheckboxRadioSwitch :checked.sync="enforceNonCommonPassword"
			type="switch">
			{{ t('password_policy', 'Forbid common passwords') }}
		</NcCheckboxRadioSwitch>
		<NcCheckboxRadioSwitch :checked.sync="enforceUpperLowerCase"
			type="switch">
			{{ t('password_policy', 'Enforce upper and lower case characters') }}
		</NcCheckboxRadioSwitch>
		<NcCheckboxRadioSwitch :checked.sync="enforceNumericCharacters"
			type="switch">
			{{ t('password_policy', 'Enforce numeric characters') }}
		</NcCheckboxRadioSwitch>
		<NcCheckboxRadioSwitch :checked.sync="enforceSpecialCharacters"
			type="switch">
			{{ t('password_policy', 'Enforce special characters') }}
		</NcCheckboxRadioSwitch>
		<NcCheckboxRadioSwitch :checked.sync="enforceHaveIBeenPwned"
			type="switch">
			{{ t('password_policy', 'Check password against the list of breached passwords from haveibeenpwned.com') }}
		</NcCheckboxRadioSwitch>
		<p :class="$style.hint">
			{{ t('password_policy', 'This check creates a hash of the password and sends the first 5 characters of this hash to the haveibeenpwned.com API to retrieve a list of all hashes that start with those. Then it checks on the Nextcloud instance if the password hash is in the result set.') }}
		</p>
	</form>
</template>

<style module>
.heading {
	font-size: 18px;
	margin-top: 0;
}

.headingWrapper {
	display: flex;
	justify-content: space-between;
}

.hint {
	color: var(--color-text-maxcontrast);
	margin-inline-start: 12px;
}

.inputField {
	max-width: 350px;
}
</style>
