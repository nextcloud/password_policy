/*!
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import type { IPasswordPolicies } from './types.d.ts'

interface ICapabilities {
	password_policy: {
		policies: IPasswordPolicies
	}
}

declare module '@nextcloud/capabilities' {
	function getCapabilities(): ICapabilities;
}
