<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2019, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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

namespace OCA\Password_Policy\Tests\Validator;

use OC\HintException;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCA\Password_Policy\Validator\CommonPasswordsValidator;
use OCA\Password_Policy\Validator\IValidator;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;
use ChristophWurst\Nextcloud\Testing\TestCase;

class CommonPasswordsValidatorTest extends TestCase {

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

		$this->validator = new CommonPasswordsValidator(
			$this->confg,
			$this->l
		);
	}

	/**
	 * @dataProvider dataValidate
	 */
	public function testValidate(string $password, bool $enforced, bool $valid) {
		$this->confg->method('getEnforceNonCommonPassword')
			->willReturn($enforced);

		if (!$valid) {
			$this->expectException(HintException::class);
		}

		$this->assertTrue(true);
		$this->validator->validate($password);
	}

	public function dataValidate() {
		return [
			['banana', false,  true],
			['bananabananabananabanana', false,  true],
			['banana',  true, false],
			['bananabananabananabanana',  true,  true],
		];
		for ($i = 1; $i <= 39; $i++) {
			$attempts[] = [str_repeat('$', $i), true, true];
		}
		return $attempts;
	}
}
