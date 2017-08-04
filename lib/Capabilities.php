<?php
/**
 * @copyright Copyright (c) 2017 Bjoern Schiessle <bjoern@schiessle.org>
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


namespace OCA\Password_Policy;


use OCP\Capabilities\ICapability;

class Capabilities implements ICapability {

	/** @var PasswordPolicyConfig */
	private $config;

	public function __construct(PasswordPolicyConfig $config) {
		$this->config = $config;
	}

	/**
	 * Function an app uses to return the capabilities
	 *
	 * @return array Array containing the apps capabilities
	 * @since 12.0.0
	 */
	public function getCapabilities() {
		return [
			'password_policy' =>
				[
					'minLength' => $this->config->getMinLength(),
					'enforceNonCommonPassword' => $this->config->getEnforceNonCommonPassword(),
					'enforceNumericCharacters' => $this->config->getEnforceNumericCharacters(),
					'enforceSpecialCharacters' => $this->config->getEnforceSpecialCharacters(),
					'enforceUpperLowerCase' => $this->config->getEnforceUpperLowerCase(),
                    'expirationDays' => $this->config->getExpirationDays(),
                    'expirationMailDaysBefore' => $this->config->getExpirationMailDaysBefore(),
                    'nextcloudHost' => $this->config->getNextcloudHost(),
                    'excludeGroups' => $this->config->getExcludeGroups(),
				]
		];
	}
}
