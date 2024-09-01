<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Listener;

use OCA\Password_Policy\FailedLoginCompliance;
use OCP\Authentication\Events\LoginFailedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/**
 * @template-implements IEventListener<LoginFailedEvent>
 */
class FailedLoginListener implements IEventListener {
	/** @var FailedLoginCompliance */
	private $compliance;

	public function __construct(FailedLoginCompliance $compliance) {
		$this->compliance = $compliance;
	}

	public function handle(Event $event): void {
		if (!($event instanceof LoginFailedEvent)) {
			return;
		}

		$this->compliance->onFailedLogin($event->getUid());
	}
}
