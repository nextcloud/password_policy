/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import { translate } from '@nextcloud/l10n'
import Vue from 'vue'

import AdminSettings from './AdminSettings.vue'

Vue.prototype.t = translate

export default new Vue({
	el: '#password_policy-settings',
	// eslint-disable-next-line vue/match-component-file-name
	name: 'AdminSettings',
	render: h => h(AdminSettings),
})
