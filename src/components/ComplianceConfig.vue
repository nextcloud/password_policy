<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<script setup lang="ts">
import type { ILoginConfig } from '../types.d.ts'

import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import { ref, watch } from 'vue'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcInputField from '@nextcloud/vue/components/NcInputField'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import { logger } from '../logger.ts'

const config = ref(loadState<ILoginConfig>('password_policy', 'loginConfig'))
const oldConfig = ref(loadState<ILoginConfig>('password_policy', 'loginConfig'))

watch(config, saveComplianceConfig, { deep: true })
/**
 * Save the changed configuration on the backend
 */
function saveComplianceConfig(): void {
	for (const [key, value] of Object.entries(config.value)) {
		// sanitize the value
		let sanitizedValue: number | boolean = value

		if (typeof value === 'number') {
			sanitizedValue = Math.max(value || 0, 0)
			sanitizedValue = Number.isNaN(sanitizedValue) ? 0 : sanitizedValue
		}

		logger.debug('Saving compliance config', { sanitizedValue, value, oldValue: oldConfig.value[key] })

		if (oldConfig.value[key] !== sanitizedValue) {
			// save the value on the backend
			window.OCP.AppConfig.setValue('password_policy', key, JSON.stringify(sanitizedValue))
			// update in the config
			oldConfig.value[key] = sanitizedValue
		}
		// check if the current config has an invalid value and replace with the sanitized if it has
		if (config.value[key] !== sanitizedValue) {
			config.value[key] = sanitizedValue
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
			<NcInputField
				v-model="config.historySize"
				:label="t('password_policy', 'Password history size')"
				:helperText="t('password_policy', 'Number of passwords to keep (securely hashed) to prevent users from reusing previously used passwords.')"
				min="0"
				type="number" />
		</div>

		<div :class="$style.inputField">
			<NcInputField
				v-model="config.maximumLoginAttempts"
				:label="t('password_policy', 'Maximum login attempts')"
				min="0"
				type="number"
				:helperText="t('password_policy', 'Number of login attempts before the user login will be automatically locked. (0 for no limit)')" />
			<NcNoteCard
				v-show="config.maximumLoginAttempts > 0"
				:heading="t('password_policy', 'Maximum login attempts')"
				:text="t('password_policy', 'Please note, this option is meant to protect attacked accounts. Permanent locked accounts have to be unlocked manually by administration. Timed locked accounts will be automatically re-enabled after the specified time. Attackers that try to guess passwords of accounts will have their IP address blocked by the bruteforce protection independent from this setting.')"
				type="info" />
			<fieldset
				v-if="config.maximumLoginAttempts > 0"
				:class="$style.subSection">
				<NcCheckboxRadioSwitch
					v-model="config.notifyUserOnLockout"
					type="switch">
					{{ t('password_policy', 'Send an email notification to the user when their account is locked') }}
				</NcCheckboxRadioSwitch>
				<NcInputField
					v-model="config.failedLoginLockoutDuration"
					:label="t('password_policy', 'Lockout user time (in minutes)')"
					min="0"
					type="number"
					:helperText="t('password_policy', 'Number of minutes to lock user account after failed login attempts. (0 for permanent lock)')" />
			</fieldset>
		</div>

		<div :class="$style.inputField">
			<NcInputField
				v-model="config.expiration"
				:label="t('password_policy', 'Number of days until user password expires')"
				min="0"
				type="number" />
			<NcNoteCard
				v-show="config.expiration > 0"
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

.subSection {
	display: flex;
	flex-direction: column;
	gap: calc(2 * var(--default-grid-baseline));
	margin: 0;
	margin-inline-start: calc(4 * var(--default-grid-baseline));
	padding: 0;
	border: none;
}

</style>
