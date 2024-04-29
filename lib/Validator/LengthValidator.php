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

class LengthValidator implements IValidator {

	/** @var PasswordPolicyConfig */
	private $config;
	/** @var IL10N */
	private $l;

	public function __construct(PasswordPolicyConfig $config, IL10N $l) {
		$this->config = $config;
		$this->l = $l;
	}

	public function validate(string $password): void {
		$minLength = $this->config->getMinLength();
		if (strlen($password) < $minLength) {
			$message = 'Password needs to be at least ' . $minLength . ' characters long.';
			$message_t = $this->l->t(
				'Password needs to be at least %s characters long.', [$minLength]
			);
			throw new HintException($message, $message_t);
		}
	}
}
