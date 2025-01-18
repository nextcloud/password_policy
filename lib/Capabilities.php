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
	public function __construct(
		private PasswordPolicyConfig $config,
		private IURLGenerator $urlGenerator,
	) {
	}

	/**
	 * @return array{
	 *   password_policy: array{
	 *     api: array{
	 *       generate: string,
	 *       validate: string,
	 *     },
	 *     policies: array<string, array{
	 *       minLength: non-negative-int,
	 *       enforceHaveIBeenPwned: bool,
	 *       enforceNonCommonPassword: bool,
	 *       enforceNumericCharacters: bool,
	 *       enforceSpecialCharacters: bool,
	 *       enforceUpperLowerCase: bool,
	 *     }>,
	 *     minLength: non-negative-int,
	 *     enforceNonCommonPassword: bool,
	 *     enforceNumericCharacters: bool,
	 *     enforceSpecialCharacters: bool,
	 *     enforceUpperLowerCase: bool,
	 *   }
	 * } Array containing the app's capabilities
	 * @since 12.0.0
	 * @since 31.0.0 new policies per context
	 */
	public function getCapabilities(): array {
		/* Get an array [['context' => [policies]], ...] */
		$policies = [];
		foreach ($this->config->getAvailableConfigs() as $context) {
			$contextName = $this->config->passwordContextToString($context);
			$policies[$contextName] = [
				'minLength' => $this->config->getMinLength($context),
				'enforceHaveIBeenPwned' => $this->config->getEnforceHaveIBeenPwned($context),
				'enforceNonCommonPassword' => $this->config->getEnforceNonCommonPassword($context),
				'enforceNumericCharacters' => $this->config->getEnforceNumericCharacters($context),
				'enforceSpecialCharacters' => $this->config->getEnforceSpecialCharacters($context),
				'enforceUpperLowerCase' => $this->config->getEnforceUpperLowerCase($context),
			];
		}

		return [
			'password_policy' =>
				[
					'api' => [
						'generate' => $this->urlGenerator->linkToOCSRouteAbsolute('password_policy.API.generate'),
						'validate' => $this->urlGenerator->linkToOCSRouteAbsolute('password_policy.API.validate'),
					],

					'policies' => $policies,

					/** @deprecated 3.0.0 */
					'minLength' => $this->config->getMinLength(),
					/** @deprecated 3.0.0 */
					'enforceNonCommonPassword' => $this->config->getEnforceNonCommonPassword(),
					/** @deprecated 3.0.0 */
					'enforceNumericCharacters' => $this->config->getEnforceNumericCharacters(),
					/** @deprecated 3.0.0 */
					'enforceSpecialCharacters' => $this->config->getEnforceSpecialCharacters(),
					/** @deprecated 3.0.0 */
					'enforceUpperLowerCase' => $this->config->getEnforceUpperLowerCase(),
				]
		];
	}
}
