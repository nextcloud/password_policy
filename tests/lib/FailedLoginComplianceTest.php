<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Tests;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Password_Policy\FailedLoginCompliance;
use OCA\Password_Policy\LockoutService;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\Config\IUserConfig;
use OCP\IUser;
use OCP\IUserManager;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class FailedLoginComplianceTest extends TestCase {

	protected FailedLoginCompliance $instance;
	protected IUserConfig&MockObject $config;
	protected IUserManager&MockObject $userManager;
	protected PasswordPolicyConfig&MockObject $policyConfig;
	protected LockoutService&MockObject $lockoutService;

	public function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IUserConfig::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->policyConfig = $this->createMock(PasswordPolicyConfig::class);
		$this->lockoutService = $this->createMock(LockoutService::class);

		/** @var LoggerInterface&MockObject */
		$logger = $this->createMock(LoggerInterface::class);

		$this->instance = new FailedLoginCompliance(
			$this->config,
			$this->userManager,
			$logger,
			$this->policyConfig,
			$this->lockoutService,
		);
	}

	public function testOnFailedLoginUnknownUser(): void {
		$this->userManager->method('get')
			->with('alice')
			->willReturn(null);
		$this->config->expects($this->never())
			->method('setValueString');
		$this->lockoutService->expects($this->never())
			->method('lockout');

		$this->instance->onFailedLogin('alice');
	}

	public function testOnFailedLoginDisabledUser(): void {
		$this->getUserMock(false);
		$this->config->expects($this->never())
			->method('setValueString');
		$this->lockoutService->expects($this->never())
			->method('lockout');

		$this->instance->onFailedLogin('alice');
	}

	public function testOnFailedLoginNoLimit(): void {
		$this->getUserMock();
		$this->policyConfig->method('getMaximumLoginAttempts')
			->willReturn(0);
		$this->config->expects($this->never())
			->method('setValueString');
		$this->lockoutService->expects($this->never())
			->method('lockout');

		$this->instance->onFailedLogin('alice');
	}

	public function testOnFailedLoginIncrementsAttempts(): void {
		$this->getUserMock();
		$this->policyConfig->method('getMaximumLoginAttempts')
			->willReturn(3);
		$this->config->method('getValueString')
			->with('alice', 'password_policy', 'failedLoginAttempts', '0')
			->willReturn('1');
		$this->config->expects($this->once())
			->method('setValueString')
			->with('alice', 'password_policy', 'failedLoginAttempts', '2');
		$this->lockoutService->expects($this->never())
			->method('lockout');
		$this->lockoutService->expects($this->never())
			->method('notifyMail');

		$this->instance->onFailedLogin('alice');
	}

	/**
	 * @dataProvider notifyProvider
	 */
	public function testOnFailedLoginLocksOutUser(bool $notify): void {
		$user = $this->getUserMock();
		$user->expects($this->never())
			->method('setEnabled');
		$this->policyConfig->method('getMaximumLoginAttempts')
			->willReturn(3);
		$this->policyConfig->method('getNotifyUserOnLockout')
			->willReturn($notify);
		$this->config->method('getValueString')
			->with('alice', 'password_policy', 'failedLoginAttempts', '0')
			->willReturn('2');
		$this->config->expects($this->once())
			->method('setValueString')
			->with('alice', 'password_policy', 'failedLoginAttempts', '0');
		$this->lockoutService->expects($this->once())
			->method('lockout')
			->with($user);
		$this->lockoutService->expects($notify ? $this->once() : $this->never())
			->method('notifyMail')
			->with($user);

		$this->instance->onFailedLogin('alice');
	}

	public static function notifyProvider(): array {
		return [
			[true],
			[false],
		];
	}

	public function testOnSuccessfulLoginResetsAttempts(): void {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('alice');
		$this->config->expects($this->once())
			->method('setValueString')
			->with('alice', 'password_policy', 'failedLoginAttempts', '0');

		$this->instance->onSuccessfulLogin($user);
	}

	protected function getUserMock(bool $enabled = true): IUser&MockObject {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('alice');
		$user->method('isEnabled')->willReturn($enabled);
		$this->userManager->method('get')
			->with('alice')
			->willReturn($user);

		return $user;
	}
}
