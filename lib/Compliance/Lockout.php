<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Compliance;

use OCA\Password_Policy\AppInfo\Application;
use OCA\Password_Policy\LockoutService;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\HintException;
use OCP\IL10N;
use OCP\IUser;
use Psr\Log\LoggerInterface;

class Lockout implements IEntryControl {

	public function __construct(
		private PasswordPolicyConfig $policyConfig,
		private LockoutService $lockoutService,
		private LoggerInterface $logger,
		private IL10N $l,
	) {
	}

	#[\Override]
	public function entryControl(IUser $user, ?string $password): void {
		$lockout_time = $this->lockoutService->getLockoutTime($user);
		if ($lockout_time === null) {
			return;
		}
		$duration = $this->policyConfig->getFailedLoginLockoutDuration();
		$maxTime = $duration > 0 ? $lockout_time + ($duration * 60) : 0;
		if ($maxTime === 0 || $maxTime > time()) {
			$message = 'Your account has been locked due to too many failed login attempts. Please try again later or contact your administrator.';
			$message_t = $this->l->t('Your account has been locked due to too many failed login attempts. Please try again later or contact your administrator.');
			throw new HintException($message, $message_t);
		} else {
			// clears lockout and keep authentification processing
			$this->lockoutService->lockout($user, false);
			$this->logger->warning(
				'User lockout period expired', ['app' => Application::APP_ID, 'uid' => $user->getUID()]
			);
		}
	}
}
