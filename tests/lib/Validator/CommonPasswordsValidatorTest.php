<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Tests\Validator;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCA\Password_Policy\Validator\CommonPasswordsValidator;
use OCA\Password_Policy\Validator\IValidator;
use OCP\HintException;
use OCP\IL10N;
use OCP\Security\PasswordContext;
use PHPUnit\Framework\MockObject\MockObject;

class CommonPasswordsValidatorTest extends TestCase {

	private PasswordPolicyConfig&MockObject $config;
	private IL10N&MockObject $l;
	private IValidator $validator;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(PasswordPolicyConfig::class);
		$this->l = $this->createMock(IL10N::class);

		$this->validator = new CommonPasswordsValidator(
			$this->config,
			$this->l
		);
	}

	/**
	 * Ensure that different contexts can yield different configuration values
	 * @dataProvider dataValidateWithContext
	 */
	public function testValidateWithContext(?PasswordContext $context, bool $expected): void {
		$this->config
			->method('getEnforceNonCommonPassword')
			->willReturnMap([
				[null, true],
				[PasswordContext::ACCOUNT, true],
				[PasswordContext::SHARING, false],
			]);

		if (!$expected) {
			$this->expectException(HintException::class);
		} else {
			$this->assertTrue(true);
		}

		$this->validator->validate('banana', $context);
	}

	public static function dataValidateWithContext(): array {
		return [
			[null, false],
			[PasswordContext::ACCOUNT, false],
			[PasswordContext::SHARING, true],
		];
	}

	/**
	 * @dataProvider dataValidate
	 */
	public function testValidate(string $password, bool $enforced, bool $valid): void {
		$this->config->expects(self::once())
			->method('getEnforceNonCommonPassword')
			->willReturn($enforced);

		if (!$valid) {
			$this->expectException(HintException::class);
		}

		$this->assertTrue(true);
		$this->validator->validate($password);
	}

	public static function dataValidate(): array {
		$attempts = [
			'not enforced but common' => ['banana', false, true],
			'not enforced unique' => ['bananabananabananabanana', false, true],
			'enforced and common' => ['banana', true, false],
			'enforced unique' => ['bananabananabananabanana', true, true],
		];
		for ($i = 1; $i <= 39; $i++) {
			$attempts[] = [str_repeat('$', $i), true, $i !== 6];
		}
		return $attempts;
	}
}
