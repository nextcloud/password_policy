<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Listener;

use OCA\Password_Policy\PasswordValidator;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Security\Events\ValidatePasswordPolicyEvent;

/**
 * @template-implements IEventListener<ValidatePasswordPolicyEvent>
 */
class ValidatePasswordPolicyEventListener implements IEventListener {

	public function __construct(
		private PasswordValidator $passwordValidator,
	) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof ValidatePasswordPolicyEvent)) {
			return;
		}

		$this->passwordValidator->validate(
			$event->getPassword(),
			$event->getContext(),
		);
	}
}
