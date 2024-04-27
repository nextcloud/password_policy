<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Validator;

use OC\HintException;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\IL10N;

class UpperCaseLoweCaseValidator implements IValidator {

	/** @var PasswordPolicyConfig */
	private $config;
	/** @var IL10N */
	private $l;

	public function __construct(PasswordPolicyConfig $config, IL10N $l) {
		$this->config = $config;
		$this->l = $l;
	}

	public function validate(string $password): void {
		$enforceUpperLowerCase = $this->config->getEnforceUpperLowerCase();
		if ($enforceUpperLowerCase) {
			if (preg_match('/^(?=.*[a-z])(?=.*[A-Z]).+$/', $password) !== 1) {
				$message = 'Password needs to contain at least one lower and one upper case character.';
				$message_t = $this->l->t(
					'Password needs to contain at least one lower and one upper case character.'
				);
				throw new HintException($message, $message_t);
			}
		}
	}
}
