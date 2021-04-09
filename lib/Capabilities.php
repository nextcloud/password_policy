<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2017 Bjoern Schiessle <bjoern@schiessle.org>
 *
 * @author Bjoern Schiessle <bjoern@schiessle.org>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace OCA\Password_Policy;

use OCP\Capabilities\ICapability;
use OCP\IURLGenerator;

class Capabilities implements ICapability {

	/** @var PasswordPolicyConfig */
	private $config;
	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(PasswordPolicyConfig $config, IURLGenerator $urlGenerator) {
		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * Function an app uses to return the capabilities
	 *
	 * @return array Array containing the apps capabilities
	 * @since 12.0.0
	 */
	public function getCapabilities(): array {
		return [
			'password_policy' =>
				[
					// User Policy
					'minLength' => $this->config->getMinLength(),
					'historySize' => $this->config->getHistorySize(),
					'expiration' => $this->config->getExpiryInDays(),
					'maximumLoginAttempts' => $this->config->getMaximumLoginAttempts(),
					'enforceNonCommonPassword' => $this->config->getEnforceNonCommonPassword(),
					'enforceNumericCharacters' => $this->config->getEnforceNumericCharacters(),
					'enforceSpecialCharacters' => $this->config->getEnforceSpecialCharacters(),
					'enforceUpperLowerCase' => $this->config->getEnforceUpperLowerCase(),

					// Sharing Policy
					'sharingMinLength' => $this->config->getSharingMinLength(),
					'sharingEnforceNonCommonPassword' => $this->config->getSharingEnforceNonCommonPassword(),
					'sharingEnforceNumericCharacters' => $this->config->getSharingEnforceNumericCharacters(),
					'sharingEnforceSpecialCharacters' => $this->config->getSharingEnforceSpecialCharacters(),
					'sharingEnforceUpperLowerCase' => $this->config->getSharingEnforceUpperLowerCase(),

					'api' => [
						'generate' => $this->urlGenerator->linkToOCSRouteAbsolute('password_policy.API.generate', ['apiVersion' => 'v2']),
						'validate' => $this->urlGenerator->linkToOCSRouteAbsolute('password_policy.API.validate', ['apiVersion' => 'v2']),
						'generateShare' => $this->urlGenerator->linkToOCSRouteAbsolute('password_policy.API.generateShare'),
						'validateShare' => $this->urlGenerator->linkToOCSRouteAbsolute('password_policy.API.validateShare'),
					]
				]
		];
	}
}
