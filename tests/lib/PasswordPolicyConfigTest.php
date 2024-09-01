<?php
/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Tests;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\IConfig;

class PasswordPolicyConfigTest extends TestCase {

	/** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
	private $config;

	/** @var PasswordPolicyConfig */
	private $instance;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);
		$this->instance = new PasswordPolicyConfig($this->config);
	}

	public function testGetMinLength() {
		$appConfigValue = '42';
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
