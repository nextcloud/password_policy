<!--
  - @copyright Copyright (c) 2021 Jonas Rittershofer <jotoeri@users.noreply.github.com>
  -
  - @author Bjoern Schiessle <bjoern@schiessle.org>
  - @author Jonas Rittershofer <jotoeri@users.noreply.github.com>
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
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -
  -->

<template>
	<SettingsSection :title="t('password_policy', 'Password policy')">
		<div id="password-policy__saving-msg" class="msg success inlineblock" style="display: none;">
			{{ t('password_policy', 'Saved') }}
		</div>

		<ul class="password-policy__settings-list">
			<li>
				<label>
					<input id="password-policy__settings__min-length"
						v-model="config.minLength"
						type="number"
						@change="updateNumberSetting('minLength')">
					{{ t('password_policy', 'Minimum password length') }}
				</label>
			</li>
			<li>
				<label>
					<input id="password-policy-history-size"
						v-model="config.historySize"
						type="number"
						@change="updateNumberSetting('historySize')">
					{{ t('password_policy', 'User password history') }}
				</label>
			</li>
			<li>
				<label>
					<input id="password-policy-expiration"
						v-model="config.expiration"
						type="number"
						@change="updateNumberSetting('expiration')">
					{{ t('password_policy', 'Number of days until user password expires') }}
				</label>
			</li>
			<li>
				<label>
					<input id="password-policy_failed-login"
						v-model="config.maximumLoginAttempts"
						type="number"
						@change="updateNumberSetting('maximumLoginAttempts')">
					{{ t('password_policy', 'Number of login attempts before the user account is blocked (0 for no limit)') }}
				</label>
			</li>
		</ul>

		<ul class="password-policy__settings-list">
			<li>
				<CheckboxRadioSwitch :checked.sync="config.enforceNonCommonPassword"
					@update:checked="updateBoolSetting('enforceNonCommonPassword')">
					{{ t('password_policy', 'Forbid common passwords') }}
				</CheckboxRadioSwitch>
			</li>
			<li>
				<CheckboxRadioSwitch :checked.sync="config.enforceUpperLowerCase"
					@update:checked="updateBoolSetting('enforceUpperLowerCase')">
					{{ t('password_policy', 'Enforce upper and lower case characters') }}
				</CheckboxRadioSwitch>
			</li>
			<li>
				<CheckboxRadioSwitch :checked.sync="config.enforceNumericCharacters"
					@update:checked="updateBoolSetting('enforceNumericCharacters')">
					{{ t('password_policy', 'Enforce numeric characters') }}
				</CheckboxRadioSwitch>
			</li>
			<li>
				<CheckboxRadioSwitch :checked.sync="config.enforceSpecialCharacters"
					@update:checked="updateBoolSetting('enforceSpecialCharacters')">
					{{ t('password_policy', 'Enforce special characters') }}
				</CheckboxRadioSwitch>
			</li>
			<li>
				<CheckboxRadioSwitch :checked.sync="config.enforceHaveIBeenPwned"
					@update:checked="updateBoolSetting('enforceHaveIBeenPwned')">
					{{ t('password_policy', 'Check password against the list of breached passwords from haveibeenpwned.com') }}
				</CheckboxRadioSwitch>
				<p class="havibeenpwned-hint">
					{{ t('password_policy', 'This check creates a hash of the password and sends the first 5 characters of this hash to the haveibeenpwned.com API to retrieve a list of all hashes that start with those. Then it checks on the Nextcloud instance if the password hash is in the result set.') }}
				</p>
			</li>
		</ul>
	</SettingsSection>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'

export default {
	name: 'AdminSettings',
	components: {
		CheckboxRadioSwitch,
		SettingsSection,
	},

	data() {
		return {
			config: loadState('password_policy', 'config'),
		}
	},

	methods: {
		updateBoolSetting(setting) {
			OCP.AppConfig.setValue('password_policy', setting, this.config[setting] ? '1' : '0')
		},
		updateNumberSetting(setting) {
			OC.msg.startSaving('#password-policy__saving-msg')

			// If value not only (positive) numbers
			if (!/^\d+$/.test(this.config[setting])) {
				let message = t('password_policy', 'Unknown error')
				switch (setting) {
				case 'minLength':
					message = t('password_policy', 'Minimal length has to be a non negative number')
					break
				case 'historySize':
					message = t('password_policy', 'History size has to be a non negative number')
					break
				case 'expiration':
					message = t('password_policy', 'Expiration days have to be a non negative number')
					break
				case 'maximumLoginAttempts':
					message = t('password_policy', 'Maximum login attempts have to be a non negative number')
					break
				}
				OC.msg.finishedSaving('#password-policy__saving-msg',
					{
						status: 'failure',
						data: {
							message,
						},
					}
				)
				return
			}

			// Otherwise store Value
			OCP.AppConfig.setValue('password_policy', setting, this.config[setting])
			OC.msg.finishedSaving('#password-policy__saving-msg',
				{
					status: 'success',
					data: {
						message: t('password_policy', 'Saved'),
					},
				}
			)
		},
	},
}
</script>

<style lang="scss" scoped>
.password-policy {
	&__settings-list li input[type='number'] {
		width: 75px;
	}

	// Little spacing between two lists (used between number/checkbox inputs)
	&__settings-list + &__settings-list {
		margin-top: 8px;
	}
}

.havibeenpwned-hint {
	opacity: 0.7;
	padding-left: 28px;
}
</style>
