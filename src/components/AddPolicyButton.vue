<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<script setup lang="ts">
import type { IPasswordPolicies } from '../types.d.ts'
import { t } from '@nextcloud/l10n'
import { computed } from 'vue'
import { PolicyHeadings } from '../constants.js'

import NcActions from '@nextcloud/vue/dist/Components/NcActions.js'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'
import IconPlus from 'vue-material-design-icons/Plus.vue'
import IconShieldCheck from 'vue-material-design-icons/ShieldCheck.vue'

const props = defineProps<{
	policies: IPasswordPolicies
}>()

defineEmits<{
	(e: 'add-policy', v: string): void
}>()

const allPasswordContexts = ['account', 'sharing']
const unusedPasswordContexts = computed(() => allPasswordContexts.filter((p) => !(p in props.policies)))
</script>

<template>
	<div :class="$style.container">
		<NcActions :menu-name="t('password_policy', 'Add policy set')" force-menu>
			<template #icon>
				<IconPlus :size="20" />
			</template>
			<NcActionButton v-for="passwordContext of unusedPasswordContexts"
				:key="passwordContext"
				close-after-click
				@click="$emit('add-policy', passwordContext)">
				<template #icon>
					<IconShieldCheck :size="20" />
				</template>
				{{ PolicyHeadings[passwordContext] }}
			</NcActionButton>
		</NcActions>
	</div>
</template>

<style module>
.container {
	display: flex;
	align-items: center;
	justify-content: center;
}
</style>
