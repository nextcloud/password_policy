<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\AppInfo;

use OCA\Password_Policy\Capabilities;
use OCA\Password_Policy\Listener\BeforePasswordUpdatedEventListener;
use OCA\Password_Policy\Listener\BeforeUserLoggedInEventListener;
use OCA\Password_Policy\Listener\FailedLoginListener;
use OCA\Password_Policy\Listener\GenerateSecurePasswordEventListener;
use OCA\Password_Policy\Listener\PasswordUpdatedEventListener;
use OCA\Password_Policy\Listener\SuccesfullLoginListener;
use OCA\Password_Policy\Listener\ValidatePasswordPolicyEventListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Authentication\Events\LoginFailedEvent;
use OCP\Security\Events\GenerateSecurePasswordEvent;
use OCP\Security\Events\ValidatePasswordPolicyEvent;
use OCP\User\Events\BeforePasswordUpdatedEvent;
use OCP\User\Events\BeforeUserLoggedInEvent;
use OCP\User\Events\PasswordUpdatedEvent;
use OCP\User\Events\UserLoggedInEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'password_policy';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerCapability(Capabilities::class);

		$context->registerEventListener(ValidatePasswordPolicyEvent::class, ValidatePasswordPolicyEventListener::class);
		$context->registerEventListener(GenerateSecurePasswordEvent::class, GenerateSecurePasswordEventListener::class);
		$context->registerEventListener(BeforePasswordUpdatedEvent::class, BeforePasswordUpdatedEventListener::class);
		$context->registerEventListener(PasswordUpdatedEvent::class, PasswordUpdatedEventListener::class);
		$context->registerEventListener(BeforeUserLoggedInEvent::class, BeforeUserLoggedInEventListener::class);
		$context->registerEventListener(LoginFailedEvent::class, FailedLoginListener::class);
		$context->registerEventListener(UserLoggedInEvent::class, SuccesfullLoginListener::class);
	}

	public function boot(IBootContext $context): void {
	}
}
