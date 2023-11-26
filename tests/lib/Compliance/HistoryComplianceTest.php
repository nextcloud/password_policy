<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Password_Policy\Tests\Compliance;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Password_Policy\Compliance\HistoryCompliance;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\HintException;
use OCP\IConfig;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Security\IHasher;
use PHPUnit\Framework\MockObject\MockObject;

class HistoryComplianceTest extends TestCase {

	/** @var HistoryCompliance */
	protected $instance;
	/** @var PasswordPolicyConfig|MockObject */
	protected $policyConfig;
	/** @var IConfig|MockObject */
	protected $config;
	/** @var IUserSession|MockObject */
	protected $session;
	/** @var IHasher|MockObject */
	protected $hasher;

	public function setUp(): void {
		parent::setUp();

		$this->policyConfig = $this->createMock(PasswordPolicyConfig::class);
		$this->config = $this->createMock(IConfig::class);
		$this->session = $this->createMock(IUserSession::class);
		$this->hasher = $this->createMock(IHasher::class);

		$this->instance = new HistoryCompliance(
			$this->policyConfig,
			$this->config,
			$this->session,
			$this->hasher,
			$this->createMock(IL10N::class),
			$this->createMock(ILogger::class)
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

	public function auditCaseProvider(): array {
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

	public function updateCaseProvider(): array {
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
