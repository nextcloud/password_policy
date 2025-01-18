<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<script setup lang="ts">
import type { ILoginConfig } from '../types.d.ts'
import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import { ref, watch } from 'vue'
import NcInputField from '@nextcloud/vue/dist/Components/NcInputField.js'
import NcNoteCard from '@nextcloud/vue/dist/Components/NcNoteCard.js'

const config = ref(loadState<ILoginConfig>('password_policy', 'loginConfig'))
const oldConfig = ref(loadState<ILoginConfig>('password_policy', 'loginConfig'))

watch(config, saveComplianceConfig, { deep: true })
/**
 * Save the changed configuration on the backend
 * @param oldConfig The old configuration
 * @param newConfig The new configuration
 */
function saveComplianceConfig(): void {
	for (const [key, value] of Object.entries(config.value)) {
		// sanitize the value
		let numericValue = Math.max(value || 0, 0)
		numericValue = Number.isNaN(numericValue) ? 0 : numericValue

		console.debug(numericValue, value, oldConfig.value[key])

		if (oldConfig.value[key] !== numericValue) {
			// save the value on the backend
			window.OCP.AppConfig.setValue('password_policy', key, JSON.stringify(numericValue))
			// update in the config
			oldConfig.value[key] = numericValue
		}
		// check if the current config has an invalid value and replace with the sanitized if it has
		if (config.value[key] !== numericValue) {
			config.value[key] = numericValue
		}
	}
}
</script>

<template>
	<form :class="$style.form" @submit.prevent="">
		<h3 :class="$style.heading">
			{{ t('password_policy', 'Login policies') }}
		</h3>

		<div :class="$style.inputField">
			<NcInputField v-model="config.historySize"
				:label="t('password_policy', 'Password history size')"
				:helper-text="t('password_policy', 'Number of passwords to keep (securely hashed) to prevent users from reusing previously used passwords.')"
				min="0"
				type="number" />
		</div>

		<div :class="$style.inputField">
			<NcInputField v-model="config.maximumLoginAttempts"
				:label="t('password_policy', 'Maximum login attempts')"
				min="0"
				type="number"
				:helper-text="t('password_policy', 'Number of login attempts before the user account will be disabled until manual action is taken. (0 for no limit)')" />
			<NcNoteCard v-show="config.maximumLoginAttempts > 0"
				:heading="t('password_policy', 'Maximum login attempts')"
				:text="t('password_policy', 'Please note, this option is meant to protect attacked accounts. Disabled accounts have to be re-enabled manually by administration. Attackers that try to guess passwords of accounts will have their IP address blocked by the bruteforce protection independent from this setting.')"
				type="info" />
		</div>

		<div :class="$style.inputField">
			<NcInputField v-model="config.expiration"
				:label="t('password_policy', 'Number of days until user password expires')"
				min="0"
				type="number" />
			<NcNoteCard v-show="config.expiration > 0"
				:heading="t('password_policy', 'Password expiration')"
				:text="t('password_policy', 'Warning: enabling password expiration is nowadays considered a security risk by several security agencies.')"
				type="warning" />
		</div>
	</form>
</template>

<style module>
.form {
	display: flex;
	flex-direction: column;
	gap: calc(2 * var(--default-grid-baseline));
}

.heading {
	font-size: 18px;
	margin-top: 0;
}

.inputField {
	max-width: 600px;
}
</style>
