<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2018, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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

use OC\HintException;
use OCP\Security\ISecureRandom;

class Generator {

	/** @var PasswordPolicyConfig */
	private $config;

	/** @var PasswordValidator */
	private $validator;

	/** @var ISecureRandom */
	private $random;

	public function __construct(PasswordPolicyConfig $config,
								PasswordValidator $validator,
								ISecureRandom $random) {
		$this->config = $config;
		$this->validator = $validator;
		$this->random = $random;
	}

	/**
	 * @return string
	 * @throws HintException
	 */
	public function generate(string $type = PasswordValidator::POLICY_USER): string {
		$config = [];

		if ($type === PasswordValidator::POLICY_SHARE) {
			$config = [
				'minLength' => $this->config->getSharingMinLength(),
				'enforceUpperLowerCase' => $this->config->getSharingEnforceUpperLowerCase(),
				'enforceNumericCharacters' => $this->config->getSharingEnforceNumericCharacters(),
				'enforceSpecialCharacters' => $this->config->getSharingEnforceSpecialCharacters(),
			];
		} else {
			// Default is User-Policy
			$config = [
				'minLength' => $this->config->getMinLength(),
				'enforceUpperLowerCase' => $this->config->getEnforceUpperLowerCase(),
				'enforceNumericCharacters' => $this->config->getEnforceNumericCharacters(),
				'enforceSpecialCharacters' => $this->config->getEnforceSpecialCharacters(),
			];
		}

		$length = $config['minLength'];
		$password = '';
		$chars = '';

		$found = false;
		for ($i = 0; $i < 10; $i++) {
			if ($config['enforceUpperLowerCase']) {
				$password .= $this->random->generate(1, ISecureRandom::CHAR_UPPER);
				$password .= $this->random->generate(1, ISecureRandom::CHAR_LOWER);
				$length -= 2;
				$chars .= ISecureRandom::CHAR_UPPER . ISecureRandom::CHAR_LOWER;
			}

			if ($config['enforceNumericCharacters']) {
				$password .= $this->random->generate(1, ISecureRandom::CHAR_DIGITS);
				$length -= 1;
				$chars .= ISecureRandom::CHAR_DIGITS;
			}

			if ($config['enforceSpecialCharacters']) {
				$password .= $this->random->generate(1, ISecureRandom::CHAR_SYMBOLS);
				$length -= 1;
				$chars .= ISecureRandom::CHAR_SYMBOLS;
			}

			if ($chars === '') {
				$chars = ISecureRandom::CHAR_HUMAN_READABLE;
			}

			// Append random chars to achieve length
			$password .= $this->random->generate($length, $chars);

			// Shuffle string so the order is random
			$password = str_shuffle($password);

			try {
				$this->validator->validate($password, $type);
				$found = true;
				break;
			} catch (HintException $e) {
				/*
				 * Invalid so lets go for another round
				 * Reset the length so we don't run below zero
				 */
				$length = $config['minLength'];
			}
		}

		if ($found === false) {
			throw new HintException('Could not generate a valid password');
		}

		return $password;
	}
}
