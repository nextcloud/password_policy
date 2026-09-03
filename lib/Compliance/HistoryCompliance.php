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
use OCP\Security\IHasher;

final readonly class HistoryCompliance implements IAuditor, IUpdatable {

	public function __construct(
		private PasswordPolicyConfig $policyConfig,
		private IHasher $hasher,
		private IL10N $l,
		private IUserConfig $userConfig,
	) {
	}

	/**
	 * @throws HintException
	 */
	#[\Override]
	public function audit(IUser $user, string $password): void {
		if ($this->policyConfig->getHistorySize() === 0) {
			return;
		}

		$history = $this->getHistory($user);

		foreach ($history as $hash) {
			if ($this->hasher->verify($password, $hash)) {
				$message = 'Password must not have been used recently before.';
				$message_t = $this->l->t(
					'Password must not have been used recently before.'
				);
				throw new HintException($message, $message_t);
			}
		}
	}

	/**
	 * @throws PreConditionNotMetException
	 */
	#[\Override]
	public function update(IUser $user, string $password): void {
		$historySize = $this->policyConfig->getHistorySize();
		if ($historySize === 0) {
			$this->userConfig->deleteUserConfig($user->getUID(), 'password_policy', 'passwordHistory');
			return;
		}

		$history = $this->getHistory($user);
		array_unshift($history, $this->hasher->hash($password));
		$history = \array_slice($history, 0, $historySize);

		$this->userConfig->setValueArray(
			$user->getUID(),
			'password_policy',
			'passwordHistory',
			$history
		);
	}

	/**
	 * @return list<string> List of previously used passwords (hashed)
	 */
	private function getHistory(IUser $user): array {
		$history = $this->userConfig->getValueArray(
			$user->getUID(),
			'password_policy',
			'passwordHistory',
		);

		$history = \array_slice($history, 0, $this->policyConfig->getHistorySize());

		/** @var list<string> $history */
		$history = \array_values($history);
		return $history;
	}
}
