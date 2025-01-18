/*!
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import type { IPasswordPolicy } from './types.d.ts'
import { t } from '@nextcloud/l10n'

export const PolicyHeadings = {
	account: t('password_policy', 'Account password policies'),
	sharing: t('password_policy', 'Share password policies'),
}

export const DefaultPolicyValues: IPasswordPolicy = {
	enforceHaveIBeenPwned: false,
	enforceNonCommonPassword: true,
	enforceNumericCharacters: false,
	enforceSpecialCharacters: false,
	enforceUpperLowerCase: false,
	minLength: 10,
}
