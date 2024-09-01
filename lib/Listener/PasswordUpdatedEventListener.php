<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Listener;

use OCA\Password_Policy\ComplianceService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\PasswordUpdatedEvent;

/**
 * @template-implements IEventListener<PasswordUpdatedEvent>
 */
class PasswordUpdatedEventListener implements IEventListener {
	/** @var ComplianceService */
	private $complianceUpdater;

	public function __construct(ComplianceService $complianceUpdater) {
		$this->complianceUpdater = $complianceUpdater;
	}

	public function handle(Event $event): void {
		if (!($event instanceof PasswordUpdatedEvent)) {
			return;
		}
		$this->complianceUpdater->update($event->getUser(), $event->getPassword());
	}
}
