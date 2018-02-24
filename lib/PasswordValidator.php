<?php
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


namespace OCA\Password_Policy;


use OC\HintException;
use OCP\Http\Client\IClientService;
use OCP\IL10N;

class PasswordValidator {

	/** @var PasswordPolicyConfig  */
	private $config;

	/** @var IL10N */
	private $l;

	/** @var IClientService */
	private $clientService;

	/**
	 * PasswordValidator constructor.
	 *
	 * @param PasswordPolicyConfig $config
	 * @param IL10N $l
	 * @param IClientService $clientService
	 */
	public function __construct(PasswordPolicyConfig $config,
								IL10N $l,
								IClientService $clientService) {
		$this->config = $config;
		$this->l = $l;
		$this->clientService = $clientService;
	}

	/**
	 * check if the given password matches the conditions defined by the admin
	 *
	 * @param string $password
	 * @throws HintException
	 */
	public function validate($password) {
		$this->checkCommonPasswords($password);
		$this->checkPasswordLength($password);
		$this->checkNumericCharacters($password);
		$this->checkUpperLowerCase($password);
		$this->checkSpecialCharacters($password);
		$this->checkHaveIBeenPwned($password);
	}

	/**
	 * check if password matches the minimum length defined by the admin
	 *
	 * @param string $password
	 * @throws HintException
	 */
	protected function checkPasswordLength($password) {
		$minLength = $this->config->getMinLength();
		if(strlen($password) < $minLength) {
			$message = 'Password needs to be at least ' . $minLength . ' characters long';
			$message_t = $this->l->t(
				'Password needs to be at least %s characters long', [$minLength]
			);
			throw new HintException($message, $message_t);
		}
	}

	/**
	 * check if password contain at least one upper and one lower case character
	 *
	 * @param string $password
	 * @throws HintException
	 */
	protected function checkUpperLowerCase($password) {
		$enforceUpperLowerCase = $this->config->getEnforceUpperLowerCase();
		if($enforceUpperLowerCase) {
			if (preg_match('/^(?=.*[a-z])(?=.*[A-Z]).+$/', $password) !== 1) {
				$message = 'Password needs to contain at least one lower and one upper case character.';
				$message_t = $this->l->t(
					'Password needs to contain at least one lower and one upper case character.'
				);
				throw new HintException($message, $message_t);
			}
		}
	}

	/**
	 * check if password contain at least one numeric character
	 *
	 * @param string $password
	 * @throws HintException
	 */
	protected function checkNumericCharacters($password) {
		$enforceNumericCharacters = $this->config->getEnforceNumericCharacters();
		if($enforceNumericCharacters) {
			if (preg_match('/^(?=.*\d).+$/', $password) !== 1) {
				$message = 'Password needs to contain at least one numeric character';
				$message_t = $this->l->t(
					'Password needs to contain at least one numeric character.'
				);
				throw new HintException($message, $message_t);
			}
		}
	}

	/**
	 * check if password contain at least one special character
	 *
	 * @param string $password
	 * @throws HintException
	 */
	protected function checkSpecialCharacters($password) {
		$enforceSpecialCharacters = $this->config->getEnforceSpecialCharacters();
		if($enforceSpecialCharacters && ctype_alnum($password)) {
			$message = 'Password needs to contain at least one special character.';
			$message_t = $this->l->t(
				'Password needs to contain at least one special character.'
			);
			throw new HintException($message, $message_t);
		}
	}


	/**
	 * Checks if password is within the 100,000 most used passwords.
	 *
	 * @param string $password
	 * @throws HintException
	 */
	protected function checkCommonPasswords($password) {
		$enforceNonCommonPassword = $this->config->getEnforceNonCommonPassword();
		if($enforceNonCommonPassword) {
			$passwordFile = __DIR__ . '/../lists/list-'.strlen($password).'.php';
			if(file_exists($passwordFile)) {
				$commonPasswords = require_once $passwordFile;
				if (isset($commonPasswords[strtolower($password)])) {
					$message = 'Password is among the 1,000,000 most common ones. Please make it unique.';
					$message_t = $this->l->t(
						'Password is among the 1,000,000 most common ones. Please make it unique.'
					);
					throw new HintException($message, $message_t);
				}
			}
		}
	}

	/**
	 * Check if a password is in the list of breached passwords from
	 * haveibeenpwned.com
	 *
	 * @param string $password
	 * @throws HintException
	 */
	protected function checkHaveIBeenPwned($password) {
		if ($this->config->getEnforceHaveIBeenPwned()) {
			$hash = sha1($password);
			$range = substr($hash, 0, 5);
			$needle = strtoupper(substr($hash, 5));

			$client = $this->clientService->newClient();

			try {
				$response = $client->get(
					'https://api.pwnedpasswords.com/range/' . $range,
					[
						'timeout' => 5
					]
				);
			} catch (\Exception $e) {
				return;
			}

			$result = $response->getBody();

			if (strpos($result, $needle) !== false) {
				$message = 'Password is present in compromised password list. Please choose a different password.';
				$message_t = $this->l->t(
					'Password is present in compromised password list. Please choose a different password.'
				);
				throw new HintException($message, $message_t);
			}
		}
	}

}
