<!--
  - SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<script setup lang="ts">
import type { IPasswordPolicy } from './types.d.ts'

import { getCapabilities } from '@nextcloud/capabilities'
import { t } from '@nextcloud/l10n'
import Vue, { computed, ref } from 'vue'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'
import AddPolicyButton from './components/AddPolicyButton.vue'
import ComplianceConfig from './components/ComplianceConfig.vue'
import PasswordPolicy from './components/PasswordPolicy.vue'
import { DefaultPolicyValues, PolicyHeadings } from './constants.ts'
import { logger } from './logger.ts'

const policies = ref(getCapabilities().password_policy.policies)
const configuredPolicies = computed(() => Object.keys(policies.value))

/**
 * Update a password policy
 *
 * @param context The password context the policy is for
 * @param policy The updated policy
 */
function onUpdatePolicy(context: string, policy: IPasswordPolicy): void {
	logger.debug(`Update password policy ${context}`, { policy })

	for (const [key, value] of Object.entries(policy)) {
		if (value !== policies.value[context]?.[key]) {
			const update = typeof value === 'boolean' ? (value ? '1' : '0') : String(value)
			window.OCP.AppConfig.setValue('password_policy', context === 'account' ? key : `${key}_${context}`, update)
		}
	}

	Vue.set(policies.value, context, policy)
}

/**
 * Create a new policy for specified password context
 *
 * @param context The password context
 */
function onAddPolicy(context: string): void {
	logger.debug(`Add password policy ${context}`)
	if (context in policies.value) {
		logger.warn(`Password context "${context}" already registered`)
		return
	}

	const passwordContexts = [...Object.keys(policies.value), context]
	window.OCP.AppConfig.setValue('password_policy', 'passwordContexts', JSON.stringify(passwordContexts))
	Vue.set(policies.value, context, { ...DefaultPolicyValues })
}

/**
 * Remove a policy configuration
 *
 * @param context The password context to remove the policy for
 */
function onRemovePolicy(context: string): void {
	logger.debug(`Remove password policy ${context}`)
	const passwordContexts = Object.keys(policies.value).filter((key) => key !== context)
	window.OCP.AppConfig.setValue('password_policy', 'passwordContexts', JSON.stringify(passwordContexts))
	Vue.delete(policies.value, context)
}
</script>

<template>
	<NcSettingsSection :name="t('password_policy', 'Password policy')">
		<ComplianceConfig />

		<div :class="$style.policyWrapper">
			<PasswordPolicy
				v-for="policyName in configuredPolicies"
				:key="policyName"
				:can-remove="policyName !== 'account'"
				:heading="configuredPolicies.length === 1 ? t('password_policy', 'General password policies') : PolicyHeadings[policyName]"
				:model-value="policies[policyName]"
				@update:modelValue="onUpdatePolicy(policyName, $event)"
				@remove="onRemovePolicy(policyName)" />

			<AddPolicyButton
				v-if="configuredPolicies.length < Object.keys(PolicyHeadings).length"
				:policies="policies"
				@add-policy="onAddPolicy" />
		</div>
	</NcSettingsSection>
</template>

<style module>
.policyWrapper {
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
}

.policyWrapper > * {
	min-width: 446px;
	max-width: 446px;
	padding: 8px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-container);
}
</style>
