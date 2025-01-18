import { createAppConfig } from '@nextcloud/vite-config'
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
export default createAppConfig({
	settings: 'src/settings.js',
}, {
	emptyOutputDirectory: {
		additionalDirectories: ['css'],
	},
	thirdPartyLicense: false,
	extractLicenseInformation: true,
	// Do not inline to speed up browser parsing JS code
	inlineCSS: false,
})
