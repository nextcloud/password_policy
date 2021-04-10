/**
 * @copyright Copyright (c) 2021 Jonas Rittershofer <jotoeri@users.noreply.github.com>
 *
 * @author Jonas Rittershofer <jotoeri@users.noreply.github.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
import { translate } from '@nextcloud/l10n'
import Vue from 'vue'

import AdminSettings from './AdminSettings'

Vue.prototype.t = translate

export default new Vue({
	el: '#password_policy-settings',
	// eslint-disable-next-line vue/match-component-file-name
	name: 'AdminSettings',
	render: h => h(AdminSettings, {
		props: {
			renderSharing: true,
		},
	}),
})
