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

	saveIntegerValue: function(value, valueName) {
		OC.msg.startSaving('#password-policy-settings-msg');

		if (/^\d+$/.test(value)) {
			OCP.AppConfig.setValue('password_policy', valueName, value);
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
						'message': OC.L10N.translate('password_policy', 'Minimal value has to be a non negative number')
					}
				}
			);
		}
	},

	saveTextValue: function(value, valueName) {
		OC.msg.startSaving('#password-policy-settings-msg');

		OCP.AppConfig.setValue('password_policy', valueName, value);
		OC.msg.finishedSaving('#password-policy-settings-msg',
			{
				'status': 'success',
				'data': {
					'message': OC.L10N.translate('password_policy', 'Saved')
				}
			}
		);
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

	$('#password-policy-min-length').keyup(function (e) {
		if (e.keyCode === 13) {
			passwordPolicy.saveIntegerValue($(this).val(), 'minLength');
		}
	}).focusout(function () {
		passwordPolicy.saveIntegerValue($(this).val(), 'minLength');
	});

	$('#password-policy-expiration-days').keyup(function (e) {
		if (e.keyCode === 13) {
			passwordPolicy.saveIntegerValue($(this).val(), 'expirationDays');
		}
	}).focusout(function () {
		passwordPolicy.saveIntegerValue($(this).val(), 'expirationDays');
	});

	$('#password-policy-expiration-mail-days-before').keyup(function (e) {
		if (e.keyCode === 13) {
			passwordPolicy.saveIntegerValue($(this).val(), 'expirationMailDaysBefore');
		}
	}).focusout(function () {
		passwordPolicy.saveIntegerValue($(this).val(), 'expirationMailDaysBefore');
	});

	$('#password-policy-nextcloud-host').keyup(function (e) {
		if (e.keyCode === 13) {
			passwordPolicy.saveTextValue($(this).val(), 'nextcloudHost');
		}
	}).focusout(function () {
		passwordPolicy.saveTextValue($(this).val(), 'nextcloudHost');
	});

	//excluded groups
	var $groups = $('#password-policy').find('.exclude-groups');

	OC.Settings.setupGroupsSelect($groups);

	$groups.change(function(event) {
		var groups = event.val || ['admin'];
		groups = JSON.stringify(groups);
		OCP.AppConfig.setValue('password_policy', 'excludeGroups', groups);
	});

});
