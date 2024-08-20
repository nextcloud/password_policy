<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Tests\Validator;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OC\HintException;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCA\Password_Policy\Validator\IValidator;
use OCA\Password_Policy\Validator\SpecialCharactersValidator;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;

class SpecialCharactersValidatorTest extends TestCase {

	/** @var PasswordPolicyConfig|MockObject */
	private $confg;

	/** @var IL10N|MockObject */
	private $l;

	/** @var IValidator */
	private $validator;

	protected function setUp(): void {
		parent::setUp();

		$this->confg = $this->createMock(PasswordPolicyConfig::class);
		$this->l = $this->createMock(IL10N::class);

		$this->validator = new SpecialCharactersValidator(
			$this->confg,
			$this->l
		);
	}

	/**
	 * @dataProvider dataValidate
	 */
	public function testValidate(string $password, bool $enforced, bool $valid) {
		$this->confg->method('getEnforceSpecialCharacters')
			->willReturn($enforced);

		if (!$valid) {
			$this->expectException(HintException::class);
		}

		$this->assertTrue(true);
		$this->validator->validate($password);
	}

	public function dataValidate() {
		return [
			['password', false,  true],
			['p@ssword', false,  true],
			['password',  true, false],
			['p@ssword',  true,  true],
		];
	}
}
