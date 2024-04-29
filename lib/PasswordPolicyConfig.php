<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */


namespace OCA\Password_Policy;

use OCP\IConfig;

/**
 * Class Config
 *
 * read/write config of the password policy
 *
 * @package OCA\Password_Policy
 */
class PasswordPolicyConfig {

	/** @var IConfig */
	private $config;

	/**
	 * Config constructor.
	 *
	 * @param IConfig $config
	 */
	public function __construct(IConfig $config) {
		$this->config = $config;
	}

	/**
	 * get the enforced minimum length of passwords
	 *
	 * @return int
	 */
	public function getMinLength(): int {
		return (int)$this->config->getAppValue('password_policy', 'minLength', '10');
	}

	/**
	 * Whether non-common passwords should be enforced
	 *
	 * @return bool
	 */
	public function getEnforceNonCommonPassword(): bool {
		$enforceNonCommonPasswords = $this->config->getAppValue(
			'password_policy',
			'enforceNonCommonPassword',
			'1'
		);
		return $enforceNonCommonPasswords === '1';
	}

	/**
	 * does the password need to contain upper and lower case characters
	 *
	 * @return bool
	 */
	public function getEnforceUpperLowerCase(): bool {
		$enforceUpperLowerCase = $this->config->getAppValue(
			'password_policy',
			'enforceUpperLowerCase',
			'0'
		);

		return $enforceUpperLowerCase === '1';
	}

	/**
	 * does the password need to contain numeric characters
	 *
	 * @return bool
	 */
	public function getEnforceNumericCharacters(): bool {
		$enforceNumericCharacters = $this->config->getAppValue(
			'password_policy',
			'enforceNumericCharacters',
			'0'
		);

		return $enforceNumericCharacters === '1';
	}

	/**
	 * does the password need to contain special characters
	 *
	 * @return bool
	 */
	public function getEnforceSpecialCharacters(): bool {
		$enforceSpecialCharacters = $this->config->getAppValue(
			'password_policy',
			'enforceSpecialCharacters',
			'0'
		);

		return $enforceSpecialCharacters === '1';
	}

	/**
	 * set minimal length of passwords
	 *
	 * @param int $minLength
	 */
	public function setMinLength(int $minLength) {
		$this->config->setAppValue('password_policy', 'minLength', $minLength);
	}

	/**
	 * enforce upper and lower case characters
	 *
	 * @param bool $enforceUpperLowerCase
	 */
	public function setEnforceUpperLowerCase(bool $enforceUpperLowerCase) {
		$value = $enforceUpperLowerCase === true ? '1' : '0';
		$this->config->setAppValue('password_policy', 'enforceUpperLowerCase', $value);
	}

	/**
	 * enforce numeric characters
	 *
	 * @param bool $enforceNumericCharacters
	 */
	public function setEnforceNumericCharacters(bool $enforceNumericCharacters) {
		$value = $enforceNumericCharacters === true ? '1' : '0';
		$this->config->setAppValue('password_policy', 'enforceNumericCharacters', $value);
	}

	/**
	 * enforce special characters
	 *
	 * @param bool $enforceSpecialCharacters
	 */
	public function setEnforceSpecialCharacters(bool $enforceSpecialCharacters) {
		$value = $enforceSpecialCharacters === true ? '1' : '0';
		$this->config->setAppValue('password_policy', 'enforceSpecialCharacters', $value);
	}

	/**
	 * Do we check against the HaveIBeenPwned passwords
	 *
	 * @return bool
	 */
	public function getEnforceHaveIBeenPwned(): bool {
		$hasInternetConnection = $this->config->getSystemValue('has_internet_connection', true);
		if (!$hasInternetConnection) {
			return false;
		}

		return $this->config->getAppValue(
			'password_policy',
			'enforceHaveIBeenPwned',
			'1'
		) === '1';
	}

	/**
	 * Enforce checking against haveibeenpwned.com
	 *
	 * @param bool $enforceHaveIBeenPwned
	 */
	public function setEnforceHaveIBeenPwned(bool $enforceHaveIBeenPwned) {
		$this->config->setAppValue('password_policy', 'enforceHaveIBeenPwned', $enforceHaveIBeenPwned ? '1' : '0');
	}

	public function getHistorySize(): int {
		return (int)$this->config->getAppValue(
			'password_policy',
			'historySize',
			0
		);
	}

	public function getExpiryInDays(): int {
		return (int)$this->config->getAppValue(
			'password_policy',
			'expiration',
			0
		);
	}

	/**
	 * @return int if 0 then there is no limit
	 */
	public function getMaximumLoginAttempts(): int {
		return (int)$this->config->getAppValue(
			'password_policy',
			'maximumLoginAttempts',
			0
		);
	}
}
