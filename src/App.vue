<!--
  - @copyright Copyright (c) 2020 Roeland Jago Douma <roeland@famdouma.nl>
  -
  - @author Roeland Jago Douma <roeland@famdouma.nl>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -
  -->
<template>
	<div id="password-policy" class="section">
		<h2>{{ t('password_policy', 'Password policy') }}</h2>

		<p>
			<label>
				<span>{{ t('password_policy', 'Minimal length') }}</span>
				<input id="password-policy-min-length"
					   type="number" />
			</label>
		</p>
		<p id="enforceNonCommonPassword">
			<input type="checkbox"
				   name="password-policy-enforce-non-common-password"
				   id="password-policy-enforce-non-common-password"
				   class="checkbox"
				   value="1" />
			<label for="password-policy-enforce-non-common-password">
				{{ t('password_policy', 'Forbid common passwords') }}
			</label><br/>
		</p>
		<p id="enforceLowerUpperCase">
			<input type="checkbox"
				   name="password-policy-enforce-upper-lower-case"
				   id="password-policy-enforce-upper-lower-case"
				   class="checkbox"
				   value="1" />
			<label for="password-policy-enforce-upper-lower-case">
				{{ t('password_policy', 'Enforce upper and lower case characters') }}
			</label><br/>
		</p>
		<p id="enforceNumericCharacters">
			<input type="checkbox"
				   name="password-policy-enforce-numeric-characters"
				   id="password-policy-enforce-numeric-characters"
				   class="checkbox"
				   value="1" />
			<label for="password-policy-enforce-numeric-characters">
				{{ t('password_policy', 'Enforce numeric characters') }}
			</label><br/>
		</p>
		<p id="enforceSpecialCharacters">
			<input type="checkbox"
				   name="password-policy-enforce-special-characters"
				   id="password-policy-enforce-special-characters"
				   class="checkbox"
				   value="1" />
			<label for="password-policy-enforce-special-characters">
				{{ t('password_policy', 'Enforce special characters') }}
			</label><br/>
		</p>
		<p id="enforceHaveIBeenPwned">
			<input type="checkbox"
				   name="password-policy-enforce-have-i-been-pwned"
				   id="password-policy-enforce-have-i-been-pwned"
				   class="checkbox"
				   value="1" />
			<label for="password-policy-enforce-have-i-been-pwned">
				{{ t('password_policy', 'Check password against the list of breached passwords from haveibeenpwned.com') }}
			</label><br/>
		</p>
		<p class="password-policy-settings-hint">
			{{ t('password_policy', 'This check creates a hash of the password and sends the first 5 characters of this hash to the haveibeenpwned.com API to retrieve a list of all hashes that start with those. Then it checks on the Nextcloud instance if the password hash is in the result set.') }}
		</p>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'App',
	props: {
		'minLength': {
			type: Number,
			required: true,
		}
	}
}
</script>
