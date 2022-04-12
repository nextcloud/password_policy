<?php
/**
 * @copyright Copyright (c) 2016 Bjoern Schiessle <bjoern@schiessle.org>
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

namespace OCA\Password_Policy\Tests;

use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\IConfig;
use ChristophWurst\Nextcloud\Testing\TestCase;

class PasswordPolicyConfigTest extends TestCase {

	/** @var  IConfig|\PHPUnit_Framework_MockObject_MockObject */
	private $config;

	/** @var  PasswordPolicyConfig */
	private $instance;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);
		$this->instance = new PasswordPolicyConfig($this->config);
	}

	public function testGetMinLength() {
		$appConfigValue = "42";
		$expected = 42;

		$this->config->expects($this->once())->method('getAppValue')
			->with('password_policy', 'minLength', '10')
			->willReturn($appConfigValue);

		$this->assertSame($expected,
			$this->instance->getMinLength()
			);
	}
	/**
	 * @dataProvider configTestData
	 */
	public function testGetEnforceNonCommonPassword($appConfigValue, $expected) {
		$this->config->expects($this->once())->method('getAppValue')
			->with('password_policy', 'enforceNonCommonPassword', '1')
			->willReturn($appConfigValue);

		$this->assertSame($expected,
			$this->instance->getEnforceNonCommonPassword()
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testGetEnforceUpperLowerCase($appConfigValue, $expected) {
		$this->config->expects($this->once())->method('getAppValue')
			->with('password_policy', 'enforceUpperLowerCase', '0')
			->willReturn($appConfigValue);

		$this->assertSame($expected,
			$this->instance->getEnforceUpperLowerCase()
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testGetEnforceNumericCharacters($appConfigValue, $expected) {
		$this->config->expects($this->once())->method('getAppValue')
			->with('password_policy', 'enforceNumericCharacters', '0')
			->willReturn($appConfigValue);

		$this->assertSame($expected,
			$this->instance->getEnforceNumericCharacters()
		);
	}


	/**
	 * @dataProvider configTestData
	 */
	public function testGetEnforceSpecialCharacters($appConfigValue, $expected) {
		$this->config->expects($this->once())->method('getAppValue')
			->with('password_policy', 'enforceSpecialCharacters', '0')
			->willReturn($appConfigValue);

		$this->assertSame($expected,
			$this->instance->getEnforceSpecialCharacters()
		);
	}

	public function testSetMinLength() {
		$expected = 42;

		$this->config->expects($this->once())->method('setAppValue')
			->with('password_policy', 'minLength', $expected);

		$this->instance->setMinLength($expected);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testSetEnforceUpperLowerCase($expected, $setValue) {
		$this->config->expects($this->once())->method('setAppValue')
			->with('password_policy', 'enforceUpperLowerCase', $expected);

		$this->instance->setEnforceUpperLowerCase($setValue);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testSetEnforceNumericCharacters($expected, $setValue) {
		$this->config->expects($this->once())->method('setAppValue')
			->with('password_policy', 'enforceNumericCharacters', $expected);

		$this->instance->setEnforceNumericCharacters($setValue);
	}


	/**
	 * @dataProvider configTestData
	 */
	public function testSetEnforceSpecialCharacters($expected, $setValue) {
		$this->config->expects($this->once())->method('setAppValue')
			->with('password_policy', 'enforceSpecialCharacters', $expected);

		$this->instance->setEnforceSpecialCharacters($setValue);
	}

	public function configTestData() {
		return [
			['1', true],
			['0', false]
		];
	}
}
