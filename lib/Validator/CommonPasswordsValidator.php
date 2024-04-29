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

class CommonPasswordsValidator implements IValidator {

	/** @var PasswordPolicyConfig */
	private $config;
	/** @var IL10N */
	private $l;

	public function __construct(PasswordPolicyConfig $config, IL10N $l) {
		$this->config = $config;
		$this->l = $l;
	}

	public function validate(string $password): void {
		$enforceNonCommonPassword = $this->config->getEnforceNonCommonPassword();
		$passwordFile = __DIR__ . '/../../lists/list-'.strlen($password).'.php';
		if ($enforceNonCommonPassword && file_exists($passwordFile)) {
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
