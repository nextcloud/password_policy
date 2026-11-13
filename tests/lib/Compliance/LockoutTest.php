<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Tests\Compliance;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Password_Policy\Compliance\Lockout;
use OCA\Password_Policy\LockoutService;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\HintException;
use OCP\IL10N;
use OCP\IUser;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class LockoutTest extends TestCase {

	protected Lockout $instance;
	protected PasswordPolicyConfig&MockObject $policyConfig;
	protected LockoutService&MockObject $lockoutService;
	protected LoggerInterface&MockObject $logger;

	public function setUp(): void {
		parent::setUp();

		$this->policyConfig = $this->createMock(PasswordPolicyConfig::class);
		$this->lockoutService = $this->createMock(LockoutService::class);
		$this->logger = $this->createMock(LoggerInterface::class);

		/** @var IL10N&MockObject */
		$l10n = $this->createMock(IL10N::class);
		$l10n->method('t')->willReturnArgument(0);

		$this->instance = new Lockout(
			$this->policyConfig,
			$this->lockoutService,
			$this->logger,
			$l10n,
		);
	}

	public function testEntryControlNotLockedOut(): void {
		$user = $this->getUserMock();

		$this->lockoutService->expects($this->once())
			->method('getLockoutTime')
			->with($user)
			->willReturn(null);
		$this->lockoutService->expects($this->never())
			->method('lockout');

		$this->instance->entryControl($user, 'password');
		$this->assertTrue(true);
	}

	/**
	 * @dataProvider stillLockedOutProvider
	 */
	public function testEntryControlStillLockedOut(int $lockoutAge, int $duration): void {
		$user = $this->getUserMock();

		$this->lockoutService->method('getLockoutTime')
			->with($user)
			->willReturn(time() - $lockoutAge);
		$this->policyConfig->method('getFailedLoginLockoutDuration')
			->willReturn($duration);
		$this->lockoutService->expects($this->never())
			->method('lockout');

		$this->expectException(HintException::class);
		$this->instance->entryControl($user, 'password');
	}

	public static function stillLockedOutProvider(): array {
		return [
			// recently locked, 15 minutes lockout
			[60, 15],
			// duration 0 means locked forever
			[86400 * 365, 0],
		];
	}

	public function testEntryControlLockoutExpired(): void {
		$user = $this->getUserMock();

		$this->lockoutService->method('getLockoutTime')
			->with($user)
			->willReturn(time() - 16 * 60);
		$this->policyConfig->method('getFailedLoginLockoutDuration')
			->willReturn(15);
		$this->lockoutService->expects($this->once())
			->method('lockout')
			->with($user, false);
		$this->logger->expects($this->once())
			->method('warning');

		$this->instance->entryControl($user, 'password');
	}

	protected function getUserMock(): IUser&MockObject {
		$user = $this->createMock(IUser::class);
		$user->expects($this->any())
			->method('getUID')
			->willReturn('alice');

		return $user;
	}
}
