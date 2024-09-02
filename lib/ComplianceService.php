<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy;

use OC\User\LoginException;
use OCA\Password_Policy\Compliance\Expiration;
use OCA\Password_Policy\Compliance\HistoryCompliance;
use OCA\Password_Policy\Compliance\IAuditor;
use OCA\Password_Policy\Compliance\IEntryControl;
use OCA\Password_Policy\Compliance\IUpdatable;
use OCP\HintException;
use OCP\IConfig;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ComplianceService {

	protected const COMPLIANCERS = [
		HistoryCompliance::class,
		Expiration::class,
	];

	public function __construct(
		private ContainerInterface $container,
		private LoggerInterface $logger,
		private IUserSession $userSession,
		private IConfig $config,
		private ISession $session,
		private IUserManager $userManager,
	) {
	}

	public function update(IUser $user, string $password): void {
		foreach ($this->getInstance(IUpdatable::class) as $instance) {
			$instance->update($user, $password);
		}
	}

	public function audit(IUser $user, string $password): void {
		foreach ($this->getInstance(IAuditor::class) as $instance) {
			$instance->audit($user, $password);
		}
	}

	/**
	 * @throws LoginException
	 */
	public function entryControl(string $loginName, ?string $password): void {
		$uid = $loginName;
		\OCP\Util::emitHook('\OCA\Files_Sharing\API\Server2Server', 'preLoginNameUsedAsUserName', ['uid' => &$uid]);

		/** @var IEntryControl $instance */
		foreach ($this->getInstance(IEntryControl::class) as $instance) {
			try {
				$user = $this->userManager->get((string)$uid);

				if ($user === null) {
					break;
				}

				$instance->entryControl($user, $password);
			} catch (HintException $e) {
				throw new LoginException($e->getHint());
			}
		}
	}

	/**
	 * @template T
	 * @psalm-param class-string<T> $interface
	 * @return Iterable<T>
	 */
	protected function getInstance($interface) {
		foreach (self::COMPLIANCERS as $compliance) {
			try {
				$instance = $this->container->get($compliance);
				if (!$instance instanceof $interface) {
					continue;
				}
			} catch (ContainerExceptionInterface $e) {
				//ignore and continue
				$this->logger->info('Could not query compliance', ['compliance' => $compliance, 'exception' => $e]);
				continue;
			}

			yield $instance;
		}
	}
}
