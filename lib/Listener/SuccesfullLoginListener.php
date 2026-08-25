<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Listener;

use OCA\Password_Policy\FailedLoginCompliance;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\UserLoggedInEvent;

/**
 * @template-implements IEventListener<UserLoggedInEvent>
 */
final class SuccesfullLoginListener implements IEventListener {
	public function __construct(
		private readonly FailedLoginCompliance $compliance,
	) {
	}

	#[\Override]
	public function handle(Event $event): void {
		if (!($event instanceof UserLoggedInEvent)) {
			return;
		}

		$this->compliance->onSuccessfulLogin($event->getUser());
	}
}
