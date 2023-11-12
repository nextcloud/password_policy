<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Compliance;

use OCP\HintException;
use OCP\IUser;

interface IEntryControl {
	/**
	 * @throws HintException
	 */
	public function entryControl(IUser $user, ?string $password): void;
}
