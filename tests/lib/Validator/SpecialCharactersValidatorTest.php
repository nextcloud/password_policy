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
use OCA\Password_Policy\Validator\SpecialCharactersValidator;
use OCP\HintException;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;

class SpecialCharactersValidatorTest extends TestCase {

	private PasswordPolicyConfig&MockObject $config;
	private IL10N&MockObject $l;
	private IValidator $validator;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(PasswordPolicyConfig::class);
		$this->l = $this->createMock(IL10N::class);

		$this->validator = new SpecialCharactersValidator(
			$this->config,
			$this->l
		);
	}

	/**
	 * @dataProvider dataValidate
	 */
	public function testValidate(string $password, bool $enforced, bool $valid) {
		$this->config->method('getEnforceSpecialCharacters')
			->willReturn($enforced);

		if (!$valid) {
			$this->expectException(HintException::class);
		}

		$this->assertTrue(true);
		$this->validator->validate($password);
	}

	public static function dataValidate() {
		return [
			['password', false,  true],
			['p@ssword', false,  true],
			['password',  true, false],
			['p@ssword',  true,  true],
		];
	}
}
