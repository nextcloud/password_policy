/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: CC0-1.0
 */

import { recommendedVue2 } from '@nextcloud/eslint-config'
import { defineConfig } from 'eslint/config'

export default defineConfig([
	...recommendedVue2,
])
