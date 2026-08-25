<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Compliance;

use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\Config\IUserConfig;
use OCP\HintException;
use OCP\IL10N;
use OCP\IUser;
use OCP\PreConditionNotMetException;

final readonly class Expiration implements IUpdatable, IEntryControl {

	public function __construct(
		private IUserConfig $userConfig,
		private PasswordPolicyConfig $policyConfig,
		private IL10N $l,
	) {
	}

	/**
	 * @throws PreConditionNotMetException
	 */
	#[\Override]
	public function update(IUser $user, string $password): void {
		if (!$this->isLocalUser($user)) {
			return;
		}

		if ($this->policyConfig->getExpiryInDays() === 0) {
			$this->userConfig->deleteUserConfig(
				$user->getUID(),
				'password_policy',
				'pwd_last_updated'
			);
			return;
		}

		$this->userConfig->setValueInt(
			$user->getUID(),
			'password_policy',
			'pwd_last_updated',
			time()
		);
	}

	#[\Override]
	public function entryControl(IUser $user, ?string $password): void {
		if ($this->policyConfig->getExpiryInDays() !== 0
			&& $this->isLocalUser($user)
			&& $this->isPasswordExpired($user)
		) {
			$message = 'Password is expired, please use forgot password method to reset';
			$message_t = $this->l->t('Password is expired, please use forgot password method to reset');
			throw new HintException($message, $message_t);
		}
	}

	private function isPasswordExpired(IUser $user): bool {
		$updatedAt = $this->userConfig->getValueInt(
			$user->getUID(),
			'password_policy',
			'pwd_last_updated',
		);

		if ($updatedAt === 0) {
			$this->update($user, '');
			return false;
		}

		$expiryInDays = $this->policyConfig->getExpiryInDays();
		$expiresIn = $updatedAt + $expiryInDays * 24 * 60 * 60;

		return $expiresIn <= time();
	}

	private function isLocalUser(IUser $user): bool {
		$localBackends = ['Database', 'Guests'];
		return in_array($user->getBackendClassName(), $localBackends);
	}
}
