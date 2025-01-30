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

class CommonPasswordsValidator implements IValidator {

	public function __construct(
		private PasswordPolicyConfig $config,
		private IL10N $l,
	) {
	}

	public function validate(string $password, ?PasswordContext $context = null): void {
		$enforceNonCommonPassword = $this->config->getEnforceNonCommonPassword($context);
		if (!$enforceNonCommonPassword) {
			return;
		}

		$passwordFile = __DIR__ . '/../../lists/list-' . strlen($password) . '.php';
		if (file_exists($passwordFile)) {
			$commonPasswords = require $passwordFile;
			assert(is_array($commonPasswords));
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
