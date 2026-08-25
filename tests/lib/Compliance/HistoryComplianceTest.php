<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Tests\Compliance;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Password_Policy\Compliance\HistoryCompliance;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\Config\IUserConfig;
use OCP\HintException;
use OCP\IL10N;
use OCP\IUser;
use OCP\Security\IHasher;
use PHPUnit\Framework\MockObject\MockObject;

class HistoryComplianceTest extends TestCase {

	protected HistoryCompliance $instance;

	protected PasswordPolicyConfig&MockObject $policyConfig;

	protected IUserConfig&MockObject $userConfig;

	protected IHasher&MockObject $hasher;

	#[\Override]
	public function setUp(): void {
		parent::setUp();

		$this->policyConfig = $this->createMock(PasswordPolicyConfig::class);
		$this->userConfig = $this->createMock(IUserConfig::class);
		$this->hasher = $this->createMock(IHasher::class);

		/** @var IL10N&MockObject */
		$l10n = $this->createMock(IL10N::class);

		$this->instance = new HistoryCompliance(
			$this->policyConfig,
			$this->hasher,
			$l10n,
			$this->userConfig,
		);
	}

	/**
	 * @dataProvider auditCaseProvider
	 */
	public function testAudit(int $historySize, array $history, string $newPasswordHash, bool $expectException): void {
		[$uid, $user] = $this->getUserMock();

		$this->policyConfig->expects($this->any())
			->method('getHistorySize')
			->willReturn($historySize);

		$this->userConfig->expects($this->any())
			->method('getValueArray')
			->with($uid, 'password_policy', 'passwordHistory')
			->willReturn($history);

		$this->hasher->expects($this->any())
			->method('verify')
			->willReturnCallback(fn (string $pwd, string $compareHash): bool => $newPasswordHash === $compareHash);

		if ($expectException) {
			$this->expectException(HintException::class);
		}

		$this->instance->audit($user, 'newPassword');
		$this->assertTrue(true);
	}

	/**
	 * @dataProvider updateCaseProvider
	 */
	public function testUpdate(int $historySize, array $history, string $newPasswordHash): void {
		[$uid, $user] = $this->getUserMock();

		$this->policyConfig->expects($this->any())
			->method('getHistorySize')
			->willReturn($historySize);

		$this->userConfig->expects($this->any())
			->method('getValueArray')
			->with($uid, 'password_policy', 'passwordHistory')
			->willReturn($history);
		$this->userConfig->expects($this->once())
			->method('setValueArray')
			->with($uid, 'password_policy', 'passwordHistory', $this->anything())
			->willReturnCallback(function (string $uid, string $app, string $key, array $value) use ($newPasswordHash): bool {
				$this->assertSame($newPasswordHash, $value[0]);
				return true;
			});

		$this->hasher->expects($this->once())
			->method('hash')
			->willReturn($newPasswordHash);

		$this->instance->update($user, 'newPassword');
	}

	public static function auditCaseProvider(): array {
		$history = ['pwHash1', 'pwHash2', 'pwHash3', 'pwHash4', 'pwHash5', 'pwHash6'];
		return [
			[
				6, [], 'pwHash1', false
			],
			[
				6, $history, 'pwHash7', false
			],
			[
				6, $history + ['pwHash7'], 'pwHash7', false
			],
			[
				6, $history, 'pwHash4', true
			],
		];
	}

	public static function updateCaseProvider(): array {
		$history = ['pwHash1', 'pwHash2', 'pwHash3', 'pwHash4', 'pwHash5', 'pwHash6'];
		return [
			[
				6, [], 'pwHash1'
			],
			[
				6, $history, 'pwHash7'
			],
			[
				6, $history + ['pwHash7'], 'pwHash7'
			],
		];
	}

	/**
	 * @return array{string, IUser&MockObject}
	 */
	protected function getUserMock(): array {
		$uid = 'alice';
		$user = $this->createMock(IUser::class);
		$user->expects($this->any())
			->method('getUID')
			->willReturn($uid);

		return [$uid, $user];
	}
}
