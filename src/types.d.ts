/*!
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

export interface IPasswordPolicy {
	minLength: number
	enforceNonCommonPassword: boolean
	enforceUpperLowerCase: boolean
	enforceNumericCharacters: boolean
	enforceSpecialCharacters: boolean
	enforceHaveIBeenPwned: boolean
}

export interface IPasswordPolicies {
	[index: string]: IPasswordPolicy
}

// User login related settings
export interface ILoginConfig {
	historySize: number
	maximumLoginAttempts: number
	expiration: number
}
