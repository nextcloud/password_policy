<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy;

use OCP\HintException;
use OCP\Security\ISecureRandom;

class Generator {

	public const PASSWORD_GENERATION_MAX_ROUNDS = 10;

	public function __construct(
		private PasswordPolicyConfig $config,
		private PasswordValidator $validator,
		private ISecureRandom $random,
	) {
	}

	/**
	 * @throws HintException
	 */
	public function generate(): string {
		$minLength = max($this->config->getMinLength(), 8);
		$length = $minLength;

		$password = '';
		$chars = '';

		for ($i = 0; $i < self::PASSWORD_GENERATION_MAX_ROUNDS; $i++) {
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

			if ($password === '') {
				// something went wrong
				break;
			}

			try {
				$this->validator->validate($password);
				// Validation succeeded
				return $password;
			} catch (HintException $e) {
				/*
				 * Invalid so lets go for another round
				 * Reset the length so we don't run below zero
				 */
				$length = $minLength;
			}
		}

		throw new HintException('Could not generate a valid password');
	}
}
