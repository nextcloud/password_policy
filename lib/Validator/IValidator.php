<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Validator;

use OCP\HintException;
use OCP\Security\PasswordContext;

interface IValidator {

	/**
	 * @throws HintException
	 */
	public function validate(string $password, ?PasswordContext $context = null): void;
}
