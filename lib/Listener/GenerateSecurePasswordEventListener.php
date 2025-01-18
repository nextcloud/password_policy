<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Listener;

use OCA\Password_Policy\Generator;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Security\Events\GenerateSecurePasswordEvent;

/**
 * @template-implements IEventListener<GenerateSecurePasswordEvent>
 */
class GenerateSecurePasswordEventListener implements IEventListener {
	/** @var Generator */
	private $generator;

	public function __construct(Generator $generator) {
		$this->generator = $generator;
	}

	public function handle(Event $event): void {
		if (!($event instanceof GenerateSecurePasswordEvent)) {
			return;
		}

		$event->setPassword(
			$this->generator->generate($event->getContext()),
		);
	}
}
