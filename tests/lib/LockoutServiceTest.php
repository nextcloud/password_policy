<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Tests;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Password_Policy\LockoutService;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\Config\IUserConfig;
use OCP\IL10N;
use OCP\IUser;
use OCP\L10N\IFactory;
use OCP\Mail\IEMailTemplate;
use OCP\Mail\IMailer;
use OCP\Mail\IMessage;
use PHPUnit\Framework\MockObject\MockObject;

class LockoutServiceTest extends TestCase {

	protected LockoutService $instance;
	protected IUserConfig&MockObject $userConfig;
	protected IMailer&MockObject $mailer;
	protected IFactory&MockObject $languageFactory;
	protected PasswordPolicyConfig&MockObject $policyConfig;

	public function setUp(): void {
		parent::setUp();

		$this->userConfig = $this->createMock(IUserConfig::class);
		$this->mailer = $this->createMock(IMailer::class);
		$this->languageFactory = $this->createMock(IFactory::class);
		$this->policyConfig = $this->createMock(PasswordPolicyConfig::class);

		$this->instance = new LockoutService(
			$this->userConfig,
			$this->mailer,
			$this->languageFactory,
			$this->policyConfig,
		);
	}

	public function testLockout(): void {
		$user = $this->getUserMock();

		$before = time();
		$this->userConfig->expects($this->once())
			->method('setValueString')
			->with('alice', 'password_policy', 'lockout_time', $this->anything())
			->willReturnCallback(function ($uid, $app, $key, $value) use ($before) {
				$this->assertGreaterThanOrEqual($before, (int)$value);
				$this->assertLessThanOrEqual(time(), (int)$value);
				return true;
			});
		$this->userConfig->expects($this->never())
			->method('deleteUserConfig');

		$this->instance->lockout($user);
	}

	public function testUnlock(): void {
		$user = $this->getUserMock();

		$this->userConfig->expects($this->never())
			->method('setValueString');
		$this->userConfig->expects($this->once())
			->method('deleteUserConfig')
			->with('alice', 'password_policy', 'lockout_time');

		$this->instance->lockout($user, false);
	}

	/**
	 * @dataProvider lockoutTimeProvider
	 */
	public function testGetLockoutTime(string $storedValue, ?int $expected): void {
		$user = $this->getUserMock();

		$this->userConfig->expects($this->once())
			->method('getValueString')
			->with('alice', 'password_policy', 'lockout_time', '')
			->willReturn($storedValue);

		$this->assertSame($expected, $this->instance->getLockoutTime($user));
	}

	public static function lockoutTimeProvider(): array {
		return [
			['', null],
			['1700000000', 1700000000],
			['0', 0],
		];
	}

	public function testGetAppUsers(): void {
		$this->userConfig->expects($this->once())
			->method('getUserIds')
			->with('password_policy')
			->willReturn(['alice', 'bob']);

		$this->assertSame(['alice', 'bob'], $this->instance->getAppUsers());
	}

	/**
	 * @dataProvider notifyMailProvider
	 */
	public function testNotifyMail(int $duration, string $expectedBodyText): void {
		$user = $this->getUserMock();
		$user->method('getDisplayName')->willReturn('Alice');
		$user->method('getEMailAddress')->willReturn('alice@example.com');

		$this->policyConfig->method('getFailedLoginLockoutDuration')
			->willReturn($duration);

		$this->userConfig->method('getValueString')
			->with('alice', 'core', 'locale')
			->willReturn('fr_FR');

		/** @var IL10N&MockObject */
		$l10n = $this->createMock(IL10N::class);
		$l10n->method('t')
			->willReturnCallback(fn ($text, $params = []) => vsprintf($text, $params));
		$this->languageFactory->method('getUserLanguage')
			->with($user)
			->willReturn('fr');
		$this->languageFactory->expects($this->once())
			->method('get')
			->with('password_policy', 'fr', 'fr_FR')
			->willReturn($l10n);

		$template = $this->createMock(IEMailTemplate::class);
		$this->mailer->expects($this->once())
			->method('createEMailTemplate')
			->with('password_policy.Lockout', ['user' => 'Alice'])
			->willReturn($template);

		$bodyTexts = [];
		$template->expects($this->once())->method('setSubject');
		$template->expects($this->once())->method('addHeader');
		$template->expects($this->once())->method('addHeading');
		$template->expects($this->once())->method('addFooter');
		$template->method('addBodyText')
			->willReturnCallback(function ($text) use (&$bodyTexts) {
				$bodyTexts[] = $text;
			});

		$message = $this->createMock(IMessage::class);
		$message->expects($this->once())
			->method('setTo')
			->with(['alice@example.com' => 'Alice'])
			->willReturnSelf();
		$message->expects($this->once())
			->method('useTemplate')
			->with($template)
			->willReturnSelf();
		$this->mailer->expects($this->once())
			->method('createMessage')
			->willReturn($message);
		$this->mailer->expects($this->once())
			->method('send')
			->with($message);

		$this->instance->notifyMail($user);

		$this->assertSame($expectedBodyText, $bodyTexts[0]);
	}

	public static function notifyMailProvider(): array {
		return [
			[0, 'Your account has been locked.'],
			[15, 'Your account has been temporarily locked for 15 minutes.'],
		];
	}

	protected function getUserMock(): IUser&MockObject {
		$user = $this->createMock(IUser::class);
		$user->expects($this->any())
			->method('getUID')
			->willReturn('alice');

		return $user;
	}
}
