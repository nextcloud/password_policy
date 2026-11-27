<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Tests\Command;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Password_Policy\Command\Lockout;
use OCA\Password_Policy\LockoutService;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\IUser;
use OCP\IUserManager;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;

class LockoutTest extends TestCase {

	protected CommandTester $tester;
	protected LockoutService&MockObject $lockoutService;
	protected PasswordPolicyConfig&MockObject $policyConfig;
	protected IUserManager&MockObject $userManager;

	public function setUp(): void {
		parent::setUp();

		$this->lockoutService = $this->createMock(LockoutService::class);
		$this->policyConfig = $this->createMock(PasswordPolicyConfig::class);
		$this->userManager = $this->createMock(IUserManager::class);

		/** @var LoggerInterface&MockObject */
		$logger = $this->createMock(LoggerInterface::class);

		$command = new Lockout(
			$this->lockoutService,
			$this->policyConfig,
			$this->userManager,
			$logger,
		);
		$this->tester = new CommandTester($command);
	}

	public function testLockUser(): void {
		$user = $this->getUserMock('alice');
		$this->lockoutService->method('getLockoutTime')->willReturn(null);
		$this->lockoutService->expects($this->once())
			->method('lockout')
			->with($user);

		$this->assertSame(0, $this->tester->execute(['user' => 'alice', '--lock' => true]));
		$this->assertStringContainsString('User alice has been locked out.', $this->tester->getDisplay());
		$this->assertStringNotContainsString('already locked out', $this->tester->getDisplay());
	}

	public function testLockAlreadyLockedUser(): void {
		$user = $this->getUserMock('alice');
		$this->lockoutService->method('getLockoutTime')->willReturn(0);
		$this->lockoutService->expects($this->once())
			->method('lockout')
			->with($user);

		$this->assertSame(0, $this->tester->execute(['user' => 'alice', '--lock' => true]));
		$this->assertStringContainsString('User alice was already locked out at 1970-01-01 00:00:00. Extending lockout period.', $this->tester->getDisplay());
	}

	public function testUnlockUser(): void {
		$user = $this->getUserMock('alice');
		$this->lockoutService->method('getLockoutTime')->willReturn(time());
		$this->lockoutService->expects($this->once())
			->method('lockout')
			->with($user, false);

		$this->assertSame(0, $this->tester->execute(['user' => 'alice', '--unlock' => true]));
		$this->assertStringContainsString('User alice has been unlocked.', $this->tester->getDisplay());
	}

	public function testUnlockNotLockedUser(): void {
		$this->getUserMock('alice');
		$this->lockoutService->method('getLockoutTime')->willReturn(null);
		$this->lockoutService->expects($this->never())
			->method('lockout');

		$this->assertSame(0, $this->tester->execute(['user' => 'alice', '--unlock' => true]));
		$this->assertStringContainsString('User alice is not currently locked out.', $this->tester->getDisplay());
	}

	public function testLockUnknownUser(): void {
		$this->userManager->method('get')->willReturn(null);
		$this->lockoutService->expects($this->never())
			->method('lockout');

		$this->assertSame(1, $this->tester->execute(['user' => 'nobody', '--lock' => true]));
		$this->assertStringContainsString('User not found', $this->tester->getDisplay());
	}

	public function testLockWithoutUser(): void {
		$this->lockoutService->expects($this->never())
			->method('lockout');

		$this->assertSame(1, $this->tester->execute(['--lock' => true]));
		$this->assertStringContainsString('User ID is required', $this->tester->getDisplay());
	}

	/**
	 * @dataProvider listProvider
	 */
	public function testListLockedUsers(int $duration, string $expectedExpiry): void {
		$alice = $this->createMock(IUser::class);
		$alice->method('getUID')->willReturn('alice');
		$alice->method('getDisplayName')->willReturn('Alice');
		$bob = $this->createMock(IUser::class);
		$this->userManager->method('get')
			->willReturnMap([
				['alice', $alice],
				['bob', $bob],
				['ghost', null],
			]);
		$this->lockoutService->method('getAppUsers')
			->willReturn(['alice', 'bob', 'ghost']);
		$this->lockoutService->method('getLockoutTime')
			->willReturnMap([
				[$alice, 0],
				[$bob, null],
			]);
		$this->policyConfig->method('getFailedLoginLockoutDuration')
			->willReturn($duration);

		$this->assertSame(0, $this->tester->execute([]));
		$display = $this->tester->getDisplay();
		$this->assertStringContainsString('- alice (Alice) was locked out at: 1970-01-01 00:00:00 (expires at: ' . $expectedExpiry . ')', $display);
		$this->assertStringNotContainsString('bob', $display);
		$this->assertStringNotContainsString('ghost', $display);
	}

	public static function listProvider(): array {
		return [
			[15, '1970-01-01 00:15:00'],
			[0, 'never'],
		];
	}

	public function testListNoLockedUsers(): void {
		$this->lockoutService->method('getAppUsers')->willReturn([]);

		$this->assertSame(0, $this->tester->execute([]));
		$this->assertStringContainsString('No users are currently locked out.', $this->tester->getDisplay());
	}

	public function testListSingleNotLockedUser(): void {
		$this->getUserMock('alice');
		$this->lockoutService->expects($this->never())
			->method('getAppUsers');
		$this->lockoutService->method('getLockoutTime')->willReturn(null);

		$this->assertSame(0, $this->tester->execute(['user' => 'alice']));
		$this->assertStringContainsString('User alice is not currently locked out.', $this->tester->getDisplay());
	}

	protected function getUserMock(string $uid, string $displayName = ''): IUser&MockObject {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn($uid);
		$user->method('getDisplayName')->willReturn($displayName ?: $uid);
		$this->userManager->method('get')
			->with($uid)
			->willReturn($user);

		return $user;
	}
}
