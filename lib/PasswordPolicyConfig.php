<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy;

use OCA\Password_Policy\AppInfo\Application;
use OCP\IAppConfig;
use OCP\IConfig;
use OCP\Security\PasswordContext;

/**
 * Class Config
 *
 * read/write config of the password policy
 *
 * @package OCA\Password_Policy
 */
class PasswordPolicyConfig {

	/**
	 * PasswordContext that are supported by the app.
	 * This does not mean all of those have been setup by the admin!
	 * @since 3.0.0
	 */
	protected const SUPPORTED_CONTEXTS = [PasswordContext::ACCOUNT, PasswordContext::SHARING];

	/**
	 * Config constructor.
	 */
	public function __construct(
		private IConfig $config,
		private IAppConfig $appConfig,
	) {
	}

	/**
	 * get the enforced minimum length of passwords
	 * @return non-negative-int
	 */
	public function getMinLength(?PasswordContext $context = null): int {
		$key = $this->getScopedAppConfig('minLength', $context);
		return max(0, $this->appConfig->getValueInt(Application::APP_ID, $key, 10));
	}

	/**
	 * Whether non-common passwords should be enforced
	 */
	public function getEnforceNonCommonPassword(?PasswordContext $context = null): bool {
		$key = $this->getScopedAppConfig('enforceNonCommonPassword', $context);
		return $this->appConfig->getValueBool(
			Application::APP_ID,
			$key,
			true
		);
	}

	/**
	 * does the password need to contain upper and lower case characters
	 */
	public function getEnforceUpperLowerCase(?PasswordContext $context = null): bool {
		return $this->appConfig->getValueBool(
			Application::APP_ID,
			$this->getScopedAppConfig('enforceUpperLowerCase', $context),
		);
	}

	/**
	 * does the password need to contain numeric characters
	 */
	public function getEnforceNumericCharacters(?PasswordContext $context = null): bool {
		return $this->appConfig->getValueBool(
			Application::APP_ID,
			$this->getScopedAppConfig('enforceNumericCharacters', $context),
		);
	}

	/**
	 * does the password need to contain special characters
	 */
	public function getEnforceSpecialCharacters(?PasswordContext $context = null): bool {
		return $this->appConfig->getValueBool(
			Application::APP_ID,
			$this->getScopedAppConfig('enforceSpecialCharacters', $context),
		);
	}

	/**
	 * set minimal length of passwords
	 */
	public function setMinLength(int $minLength, ?PasswordContext $context = null): void {
		$this->appConfig->setValueInt(
			Application::APP_ID,
			$this->getScopedAppConfig('minLength', $context),
			$minLength,
		);
	}

	/**
	 * enforce upper and lower case characters
	 */
	public function setEnforceUpperLowerCase(bool $enforceUpperLowerCase, ?PasswordContext $context = null): void {
		$this->appConfig->setValueBool(
			Application::APP_ID,
			$this->getScopedAppConfig('enforceUpperLowerCase', $context),
			$enforceUpperLowerCase,
		);
	}

	/**
	 * enforce numeric characters
	 */
	public function setEnforceNumericCharacters(bool $enforceNumericCharacters, ?PasswordContext $context = null): void {
		$this->appConfig->setValueBool(
			Application::APP_ID,
			$this->getScopedAppConfig('enforceNumericCharacters', $context),
			$enforceNumericCharacters,
		);
	}

	/**
	 * enforce special characters
	 */
	public function setEnforceSpecialCharacters(bool $enforceSpecialCharacters, ?PasswordContext $context = null): void {
		$this->appConfig->setValueBool(
			Application::APP_ID,
			$this->getScopedAppConfig('enforceSpecialCharacters', $context),
			$enforceSpecialCharacters,
		);
	}

	/**
	 * Do we check against the HaveIBeenPwned passwords
	 */
	public function getEnforceHaveIBeenPwned(?PasswordContext $context = null): bool {
		$hasInternetConnection = $this->config->getSystemValueBool('has_internet_connection', true);
		if (!$hasInternetConnection) {
			return false;
		}

		return $this->appConfig->getValueBool(
			Application::APP_ID,
			$this->getScopedAppConfig('enforceHaveIBeenPwned', $context),
			true,
		);
	}

	/**
	 * Enforce checking against haveibeenpwned.com
	 *
	 * @param bool $enforceHaveIBeenPwned
	 */
	public function setEnforceHaveIBeenPwned(bool $enforceHaveIBeenPwned, ?PasswordContext $context = null): void {
		$this->appConfig->setValueBool(
			Application::APP_ID,
			$this->getScopedAppConfig('enforceHaveIBeenPwned', $context),
			$enforceHaveIBeenPwned,
		);
	}

	public function getHistorySize(): int {
		return $this->appConfig->getValueInt(
			Application::APP_ID,
			'historySize',
		);
	}

	public function getExpiryInDays(): int {
		return $this->appConfig->getValueInt(
			Application::APP_ID,
			'expiration',
		);
	}

	/**
	 * @return int if 0 then there is no limit
	 */
	public function getMaximumLoginAttempts(): int {
		return $this->appConfig->getValueInt(
			Application::APP_ID,
			'maximumLoginAttempts',
		);
	}

	/**
	 * Format a \OCP\Security\PasswordContext to a human readable string
	 * @since 3.0.0
	 */
	public static function passwordContextToString(PasswordContext $context): string {
		return match ($context) {
			PasswordContext::ACCOUNT => 'account',
			PasswordContext::SHARING => 'sharing',
			default => throw new \InvalidArgumentException('Unsupported password context'),
		};
	}

	/**
	 * Get all password contexts for which a policy was setup
	 * @return PasswordContext[]
	 * @since 3.0.0
	 */
	public function getAvailableConfigs(): array {
		return array_filter(self::SUPPORTED_CONTEXTS, $this->hasConfigurationContext(...));
	}

	/**
	 * Check if a configuration for this password context is available
	 * @param PasswordContext $context
	 * @return bool
	 * @since 3.0.0
	 */
	private function hasConfigurationContext(?PasswordContext $context = null): bool {
		$available = $this->appConfig->getValueArray(Application::APP_ID, 'passwordContexts', ['account']);
		return match ($context) {
			PasswordContext::ACCOUNT => true,
			PasswordContext::SHARING => in_array('sharing', $available),
			default => false,
		};
	}

	private function getScopedAppConfig(string $key, ?PasswordContext $context): string {
		if ($context === null || $this->hasConfigurationContext($context) === false) {
			$context = PasswordContext::ACCOUNT;
		}
 
		if ($context === PasswordContext::ACCOUNT) {
			return $key;
		}

		return $key . '_' . self::passwordContextToString($context);
	}
}
