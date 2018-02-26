/**
 * @copyright Copyright (c) 2016 Bjoern Schiessle <bjoern@schiessle.org>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

var passwordPolicy = {

	saveMinLength: function(minLength) {
		OC.msg.startSaving('#password-policy-settings-msg');

		if (/^\d+$/.test(minLength)) {
			OCP.AppConfig.setValue('password_policy', 'minLength', minLength);
			OC.msg.finishedSaving('#password-policy-settings-msg',
				{
					'status': 'success',
					'data': {
						'message': OC.L10N.translate('password_policy', 'Saved')
					}
				}
			);
		} else {
			OC.msg.finishedSaving('#password-policy-settings-msg',
				{
					'status': 'failure',
					'data': {
						'message': OC.L10N.translate('password_policy', 'Minimal length has to be a non negative number')
					}
				}
			);
		}
	}

};

$(document).ready(function(){
	$('#password-policy-enforce-non-common-password').click(function() {
		var value = '0';
		if (this.checked) {
			value = '1';
		}
		OCP.AppConfig.setValue('password_policy', 'enforceNonCommonPassword', value);
	});
	$('#password-policy-enforce-upper-lower-case').click(function() {
		var value = '0';
		if (this.checked) {
			value = '1';
		}
		OCP.AppConfig.setValue('password_policy', 'enforceUpperLowerCase', value);
	});
	$('#password-policy-enforce-numeric-characters').click(function() {
		var value = '0';
		if (this.checked) {
			value = '1';
		}
		OCP.AppConfig.setValue('password_policy', 'enforceNumericCharacters', value);
	});
	$('#password-policy-enforce-special-characters').click(function() {
		var value = '0';
		if (this.checked) {
			value = '1';
		}
		OCP.AppConfig.setValue('password_policy', 'enforceSpecialCharacters', value);
	});
	$('#password-policy-enforce-have-i-been-pwned').click(function() {
		var value = '0';
		if (this.checked) {
			value = '1';
		}
		OCP.AppConfig.setValue('password_policy', 'enforceHaveIBeenPwned', value);
	});

	$('#password-policy-min-length').keyup(function (e) {
		if (e.keyCode === 13) {
			passwordPolicy.saveMinLength($(this).val());
		}
	}).focusout(function () {
		passwordPolicy.saveMinLength($(this).val());
	});

});
