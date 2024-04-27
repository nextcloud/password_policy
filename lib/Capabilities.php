<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
					'minLength' => $this->config->getMinLength(),
					'enforceNonCommonPassword' => $this->config->getEnforceNonCommonPassword(),
					'enforceNumericCharacters' => $this->config->getEnforceNumericCharacters(),
					'enforceSpecialCharacters' => $this->config->getEnforceSpecialCharacters(),
					'enforceUpperLowerCase' => $this->config->getEnforceUpperLowerCase(),
					'api' => [
						'generate' => $this->urlGenerator->linkToOCSRouteAbsolute('password_policy.API.generate'),
						'validate' => $this->urlGenerator->linkToOCSRouteAbsolute('password_policy.API.validate'),
					]
				]
		];
	}
}
