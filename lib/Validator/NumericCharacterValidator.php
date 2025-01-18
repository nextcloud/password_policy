<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Validator;

use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\HintException;
use OCP\IL10N;
use OCP\Security\PasswordContext;

class NumericCharacterValidator implements IValidator {

	public function __construct(
		private PasswordPolicyConfig $config,
		private IL10N $l,
	) {
	}

	public function validate(string $password, ?PasswordContext $context = null): void {
		$enforceNumericCharacters = $this->config->getEnforceNumericCharacters($context);
		if ($enforceNumericCharacters) {
			if (preg_match('/^(?=.*\d).+$/', $password) !== 1) {
				$message = 'Password needs to contain at least one numeric character.';
				$message_t = $this->l->t(
					'Password needs to contain at least one numeric character.'
				);
				throw new HintException($message, $message_t);
			}
		}
	}
}
