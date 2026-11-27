<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Command;

use OC\Core\Command\Base;
use OCA\Password_Policy\AppInfo\Application;
use OCA\Password_Policy\LockoutService;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Lockout extends Base {

	public function __construct(
		private LockoutService $lockoutService,
		private PasswordPolicyConfig $passwordPolicyConfig,
		private IUserManager $userManager,
		private LoggerInterface $logger,
	) {
		parent::__construct();
	}

	protected function configure() {
		$this->setName('password_policy:lockout')
			->setDescription('Manage lockout state for users (list or reset lockout state)')
			->addArgument('user', InputArgument::OPTIONAL, 'The user for whom to manage lockout state')
			->addOption('unlock', null, InputOption::VALUE_NONE, 'Remove lockout state for the specified user')
			->addOption('lock', null, InputOption::VALUE_NONE, 'Set lockout state for the specified user');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		try {
			$userId = $input->getArgument('user');
			if ($input->getOption('lock') === true) {
				$this->lockoutUser($userId, true, $output);
			} elseif ($input->getOption('unlock') === true) {
				$this->lockoutUser($userId, false, $output);
			} else {
				$this->listLockoutUsers($output, $userId);
			}
		} catch (\Exception $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return 1;
		}
		return 0;
	}

	protected function lockoutUser(?string $userId, bool $lock, OutputInterface $output) : void {
		if ($userId === null) {
			throw new \InvalidArgumentException('User ID is required for lock/unlock operations');
		}
		$user = $this->userManager->get($userId);
		if (!$user) {
			throw new \InvalidArgumentException('User not found');
		}
		$lockoutTime = $this->lockoutService->getLockoutTime($user);
		if ($lock) {
			if ($lockoutTime !== null) {
				$output->writeln('<info>User ' . $userId . ' was already locked out at ' . $this->formatTime($lockoutTime) . '. Extending lockout period.</info>');
			}
			$this->lockoutService->lockout($user);
			$output->writeln('<info>User ' . $userId . ' has been locked out.</info>');
			$this->logger->info('Locking user by occ command', ['app' => Application::APP_ID, 'uid' => $userId]);
		} else {
			if ($lockoutTime === null) {
				$output->writeln('<info>User ' . $userId . ' is not currently locked out.</info>');
				return;
			}
			$this->lockoutService->lockout($user, false);
			$output->writeln('<info>User ' . $userId . ' has been unlocked.</info>');
			$this->logger->info('Unlocking user by occ command', ['app' => Application::APP_ID, 'uid' => $userId]);
		}
	}

	protected function listLockoutUsers(OutputInterface $output, ?string $filterUserId): void {
		$duration = $this->passwordPolicyConfig->getFailedLoginLockoutDuration();
		$count = 0;
		$userIds = $filterUserId ? [ $filterUserId ] : $this->lockoutService->getAppUsers();
		foreach ($userIds as $userId) {
			$user = $this->userManager->get($userId);
			if (!$user) {
				continue;
			}
			$lockoutTime = $this->lockoutService->getLockoutTime($user);
			if ($lockoutTime === null) {
				continue;
			}
			$count++;
			$output->writeln('- ' . $user->getUID() . ' (' . $user->getDisplayName() . ') was locked out at: ' . $this->formatTime($lockoutTime) . ' (expires at: ' . ($duration > 0 ? $this->formatTime($lockoutTime + ($duration * 60)) : 'never') . ')');
		}
		if ($count === 0) {
			if ($filterUserId === null) {
				$output->writeln('<info>No users are currently locked out.</info>');
			} else {
				$output->writeln('<info>User ' . $filterUserId . ' is not currently locked out.</info>');
			}
			return;
		}
	}

	private function formatTime(int $time): string {
		return (new \DateTime('@' . $time))->format('Y-m-d H:i:s');
	}
}
