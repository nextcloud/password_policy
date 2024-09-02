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
use OCP\HintException;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Security\IHasher;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class HistoryComplianceTest extends TestCase {

	protected HistoryCompliance $instance;
	protected PasswordPolicyConfig&MockObject $policyConfig;
	protected IConfig&MockObject $config;
	protected IUserSession&MockObject $session;
	protected IHasher&MockObject $hasher;

	public function setUp(): void {
		parent::setUp();

		$this->policyConfig = $this->createMock(PasswordPolicyConfig::class);
		$this->config = $this->createMock(IConfig::class);
		$this->session = $this->createMock(IUserSession::class);
		$this->hasher = $this->createMock(IHasher::class);

		/** @var IL10N&MockObject */
		$l10n = $this->createMock(IL10N::class);
		/** @var LoggerInterface&MockObject */
		$logger = $this->createMock(LoggerInterface::class);

		$this->instance = new HistoryCompliance(
			$this->policyConfig,
			$this->config,
			$this->session,
			$this->hasher,
			$l10n,
			$logger,
		);
	}


	/**
	 * @dataProvider auditCaseProvider
	 */
	public function testAudit(int $historySize, array $history, string $newPasswordHash, bool $expectException) {
		[$uid, $user] = $this->getUserMock();

		$this->policyConfig->expects($this->any())
			->method('getHistorySize')
			->willReturn($historySize);

		$history = \json_encode($history);
		$this->config->expects($this->any())
			->method('getUserValue')
			->with($uid, 'password_policy', 'passwordHistory', '[]')
			->willReturn($history);

		$this->hasher->expects($this->any())
			->method('verify')
			->willReturnCallback(function ($pwd, $compareHash) use ($newPasswordHash) {
				return $newPasswordHash === $compareHash;
			});

		if ($expectException) {
			$this->expectException(HintException::class);
		}

		$this->instance->audit($user, 'newPassword');
		$this->assertTrue(true);
	}

	/**
	 * @dataProvider updateCaseProvider
	 */
	public function testUpdate(int $historySize, array $history, string $newPasswordHash) {
		[$uid, $user] = $this->getUserMock();

		$this->policyConfig->expects($this->any())
			->method('getHistorySize')
			->willReturn($historySize);

		$history = \json_encode($history);
		$this->config->expects($this->any())
			->method('getUserValue')
			->with($uid, 'password_policy', 'passwordHistory', '[]')
			->willReturn($history);
		$this->config->expects($this->once())
			->method('setUserValue')
			->with($uid, 'password_policy', 'passwordHistory', $this->anything())
			->willReturnCallback(function ($uid, $app, $key, $value) use ($newPasswordHash) {
				$history = \json_decode($value, true);
				$this->assertSame($newPasswordHash, $history[0]);
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

	protected function getUserMock(): array {
		$uid = 'alice';
		$user = $this->createMock(IUser::class);
		$user->expects($this->any())
			->method('getUID')
			->willReturn($uid);

		return [$uid, $user];
	}
}
