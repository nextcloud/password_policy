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

class FailedLoginCompliance {

	/** @var IConfig */
	private $config;

	/** @var IUserManager */
	private $userManager;

	/** @var PasswordPolicyConfig */
	private $passwordPolicyConfig;

	public function __construct(
		IConfig $config,
		IUserManager $userManager,
		PasswordPolicyConfig $passwordPolicyConfig) {
		$this->config = $config;
		$this->userManager = $userManager;
		$this->passwordPolicyConfig = $passwordPolicyConfig;
	}

	public function onFailedLogin(string $uid) {
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
			return;
		}

		$this->setAttempts($uid, $attempts);
	}

	public function onSucessfullLogin(IUser $user) {
		$this->setAttempts($user->getUID(), 0);
	}

	private function getAttempts(string $uid): int {
		return (int)$this->config->getUserValue($uid, 'password_policy', 'failedLoginAttempts', 0);
	}

	private function setAttempts(string $uid, int $attempts): void {
		$this->config->setUserValue($uid, 'password_policy', 'failedLoginAttempts', $attempts);
	}
}
