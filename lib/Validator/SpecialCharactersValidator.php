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

class SpecialCharactersValidator implements IValidator {

	public function __construct(
		private PasswordPolicyConfig $config,
		private IL10N $l,
	) {
	}

	public function validate(string $password, ?PasswordContext $context = null): void {
		$enforceSpecialCharacters = $this->config->getEnforceSpecialCharacters($context);
		if ($enforceSpecialCharacters && ctype_alnum($password)) {
			$message = 'Password needs to contain at least one special character.';
			$message_t = $this->l->t(
				'Password needs to contain at least one special character.'
			);
			throw new HintException($message, $message_t);
		}
	}
}
