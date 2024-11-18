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

/**
 * Class Config
 *
 * read/write config of the password policy
 *
 * @package OCA\Password_Policy
 */
class PasswordPolicyConfig {

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
	public function getMinLength(): int {
		return max(0, $this->appConfig->getValueInt(Application::APP_ID, 'minLength', 10));
	}

	/**
	 * Whether non-common passwords should be enforced
	 */
	public function getEnforceNonCommonPassword(): bool {
		return $this->appConfig->getValueBool(
			Application::APP_ID,
			'enforceNonCommonPassword',
			true
		);
	}

	/**
	 * does the password need to contain upper and lower case characters
	 */
	public function getEnforceUpperLowerCase(): bool {
		return $this->appConfig->getValueBool(
			Application::APP_ID,
			'enforceUpperLowerCase',
		);
	}

	/**
	 * does the password need to contain numeric characters
	 */
	public function getEnforceNumericCharacters(): bool {
		return $this->appConfig->getValueBool(
			Application::APP_ID,
			'enforceNumericCharacters',
		);
	}

	/**
	 * does the password need to contain special characters
	 */
	public function getEnforceSpecialCharacters(): bool {
		return $this->appConfig->getValueBool(
			Application::APP_ID,
			'enforceSpecialCharacters',
		);
	}

	/**
	 * set minimal length of passwords
	 */
	public function setMinLength(int $minLength): void {
		$this->appConfig->setValueInt(Application::APP_ID, 'minLength', $minLength);
	}

	/**
	 * enforce upper and lower case characters
	 */
	public function setEnforceUpperLowerCase(bool $enforceUpperLowerCase): void {
		$this->appConfig->setValueBool(Application::APP_ID, 'enforceUpperLowerCase', $enforceUpperLowerCase);
	}

	/**
	 * enforce numeric characters
	 */
	public function setEnforceNumericCharacters(bool $enforceNumericCharacters): void {
		$this->appConfig->setValueBool(Application::APP_ID, 'enforceNumericCharacters', $enforceNumericCharacters);
	}

	/**
	 * enforce special characters
	 */
	public function setEnforceSpecialCharacters(bool $enforceSpecialCharacters): void {
		$this->appConfig->setValueBool(Application::APP_ID, 'enforceSpecialCharacters', $enforceSpecialCharacters);
	}

	/**
	 * Do we check against the HaveIBeenPwned passwords
	 */
	public function getEnforceHaveIBeenPwned(): bool {
		$hasInternetConnection = $this->config->getSystemValueBool('has_internet_connection', true);
		if (!$hasInternetConnection) {
			return false;
		}

		return $this->appConfig->getValueBool(
			Application::APP_ID,
			'enforceHaveIBeenPwned',
			true,
		);
	}

	/**
	 * Enforce checking against haveibeenpwned.com
	 *
	 * @param bool $enforceHaveIBeenPwned
	 */
	public function setEnforceHaveIBeenPwned(bool $enforceHaveIBeenPwned): void {
		$this->appConfig->setValueBool(Application::APP_ID, 'enforceHaveIBeenPwned', $enforceHaveIBeenPwned);
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
}
