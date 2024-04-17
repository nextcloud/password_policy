<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2018, Roeland Jago Douma <roeland@famdouma.nl>
 * @copyright Copyright (c) 2023, Sebastian Faul <sebastian@faul.info>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 * @author Sebastian Faul <sebastian@faul.info>
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
     * @throws HintException
	 */
	public function generate(): string {
		if ($this->config->getGeneratePassphrases()) {
			return $this->generatePhrase();
		}
		return $this->generateRandom();
	}

	private function generatePhrase(): string {
		$minLength = max($this->config->getMinLength(), 8);
		$wordCount = max($this->config->getWordCount(), 3);

		$seperator = '-';
		$effLargeWordlist = require_once 'effLargeWordlist.php';
		$wlSize = count($effLargeWordlist);

		$words = array();
		for ($i=0; strlen(join($seperator, $words)) + 1 < $minLength || sizeof($words) < $wordCount;$i++){
			$index = \random_int(0, $wlSize - 1);
			$words[$i] = ucfirst($effLargeWordlist[$index]);
		}
		$numPos = \random_int(0, sizeof($words)-1);
		$num = $this->random->generate(1, ISecureRandom::CHAR_DIGITS);
		$words[$numPos] .= $num;

		$password = join($seperator, $words);
		
		$this->validator->validate($password);
		
		return $password;
	}

	private function generateRandom(): string {
		$minLength = max($this->config->getMinLength(), 8);
		$length = $minLength;

		$password = '';
		$chars = '';

		$found = false;
		for ($i = 0; $i < 10; $i++) {
			if ($this->config->getEnforceUpperLowerCase()) {
				$password .= $this->random->generate(1, ISecureRandom::CHAR_UPPER);
				$password .= $this->random->generate(1, ISecureRandom::CHAR_LOWER);
				$length -= 2;
				$chars .= ISecureRandom::CHAR_UPPER . ISecureRandom::CHAR_LOWER;
			}

			if ($this->config->getEnforceNumericCharacters()) {
				$password .= $this->random->generate(1, ISecureRandom::CHAR_DIGITS);
				$length -= 1;
				$chars .= ISecureRandom::CHAR_DIGITS;
			}

			if ($this->config->getEnforceSpecialCharacters()) {
				$password .= $this->random->generate(1, ISecureRandom::CHAR_SYMBOLS);
				$length -= 1;
				$chars .= ISecureRandom::CHAR_SYMBOLS;
			}

			if ($chars === '') {
				$chars = ISecureRandom::CHAR_HUMAN_READABLE;
			}

			$password .= $chars = $this->random->generate($length, $chars);

			// Shuffle string so the order is random
			$password = str_shuffle($password);

			try {
				$this->validator->validate($password);

				if ($password === null || $password === '') {
					// something went wrong
					break;
				}

				$found = true;
				break;
			} catch (HintException $e) {
				/*
				 * Invalid so lets go for another round
				 * Reset the length so we don't run below zero
				 */
				$length = $minLength;
			}
		}

		if ($found === false) {
			throw new HintException('Could not generate a valid password');
		}

		return $password;
	}
}
