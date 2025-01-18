/*!
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import { createAppConfig } from '@nextcloud/vite-config'

export default createAppConfig({
	settings: 'src/settings.ts',
}, {
	// Setup REUSE information extraction
	extractLicenseInformation: {
		// Also create .license files for source maps
		includeSourceMaps: true,
	},
	thirdPartyLicense: false,
	// Also clear the CSS directory
	emptyOutputDirectory: {
		additionalDirectories: ['css'],
	},
	// Make sure we have one cache-able CSS entry point per JS entry
	createEmptyCSSEntryPoints: true,
	// Enable CSS code splitting to create correct CSS files per JS entry
	config: {
		build: {
			cssCodeSplit: true,
		},
	},
})
