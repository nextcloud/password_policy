<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Tests\Validator;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCA\Password_Policy\Validator\IValidator;
use OCA\Password_Policy\Validator\NumericCharacterValidator;
use OCP\HintException;
use OCP\IL10N;
use OCP\Security\PasswordContext;
use PHPUnit\Framework\MockObject\MockObject;

class NumericCharacterValidatorTest extends TestCase {

	private PasswordPolicyConfig&MockObject $config;
	private IL10N&MockObject $l;
	private IValidator $validator;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(PasswordPolicyConfig::class);
		$this->l = $this->createMock(IL10N::class);

		$this->validator = new NumericCharacterValidator(
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
			->method('getEnforceNumericCharacters')
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

		$this->validator->validate('password', $context);
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
	public function testValidate(string $password, bool $enforced, bool $valid) {
		$this->config->method('getEnforceNumericCharacters')
			->willReturn($enforced);

		if (!$valid) {
			$this->expectException(HintException::class);
		}

		$this->assertTrue(true);
		$this->validator->validate($password);
	}

	public static function dataValidate() {
		return [
			['password',  false,  true],
			['password1', false,  true],
			['password',   true, false],
			['password1',  true,  true],
		];
	}
}
