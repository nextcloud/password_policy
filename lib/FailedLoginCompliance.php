<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy;

use OCP\IConfig;
use OCP\IUser;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;

class FailedLoginCompliance {

	public function __construct(
		private IConfig $config,
		private IUserManager $userManager,
		private LoggerInterface $logger,
		private PasswordPolicyConfig $passwordPolicyConfig,
	) {
	}

	public function onFailedLogin(string $uid): void {
		$user = $this->userManager->get($uid);

		if (!($user instanceof IUser)) {
			return;
		}

		if ($user->isEnabled() === false) {
			// Just ignore this user then
			return;
		}

		$allowedAttempts = $this->passwordPolicyConfig->getMaximumLoginAttempts();

		if ($allowedAttempts === 0) {
			// 0 is the max
			return;
		}

		$attempts = $this->getAttempts($uid);
		$attempts++;

		if ($attempts >= $allowedAttempts) {
			$this->setAttempts($uid, 0);
			$user->setEnabled(false);
			$this->logger->warning(
				'Too many consecutive failed login attempts, disabling user',
				['app' => 'password_policy', 'uid' => $uid]
			);
			return;
		}

		$this->setAttempts($uid, $attempts);
	}

	public function onSuccessfulLogin(IUser $user): void {
		$this->setAttempts($user->getUID(), 0);
	}

	private function getAttempts(string $uid): int {
		return (int)$this->config->getUserValue($uid, 'password_policy', 'failedLoginAttempts', '0');
	}

	private function setAttempts(string $uid, int $attempts): void {
		$this->config->setUserValue($uid, 'password_policy', 'failedLoginAttempts', (string)$attempts);
	}
}
