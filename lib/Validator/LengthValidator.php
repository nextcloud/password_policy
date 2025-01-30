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

class LengthValidator implements IValidator {

	public function __construct(
		private PasswordPolicyConfig $config,
		private IL10N $l,
	) {
	}

	public function validate(string $password, ?PasswordContext $context = null): void {
		$minLength = $this->config->getMinLength($context);
		if (strlen($password) < $minLength) {
			$message = 'Password needs to be at least ' . $minLength . ' characters long.';
			$message_t = $this->l->t(
				'Password needs to be at least %s characters long.', [$minLength]
			);
			throw new HintException($message, $message_t);
		}
	}
}
