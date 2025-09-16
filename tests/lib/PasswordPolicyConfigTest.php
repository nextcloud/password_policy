<?php

/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Tests;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\IAppConfig;
use OCP\IConfig;
use OCP\Security\PasswordContext;
use PHPUnit\Framework\MockObject\MockObject;

class PasswordPolicyConfigTest extends TestCase {

	private IConfig&MockObject $config;
	private IAppConfig&MockObject $appConfig;
	private PasswordPolicyConfig $instance;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);
		$this->appConfig = $this->createMock(IAppConfig::class);
		$this->instance = new PasswordPolicyConfig($this->config, $this->appConfig);
	}

	/**
	 * @dataProvider dataGetMinLength
	 */
	public function testGetMinLength(?PasswordContext $context, bool $hasContext, int $expected) {
		$this->appConfig
			->method('getValueArray')
			->with('password_policy', 'passwordContexts', ['account'])
			->willReturn($hasContext ? ['account', 'sharing'] : ['account']);
		$this->appConfig
			->expects(self::once())
			->method('getValueInt')
			->willReturnMap([
				['password_policy', 'minLength', 10, 20],
				['password_policy', 'minLength_sharing', 10, 42],
			]);

		$this->assertSame($expected, $this->instance->getMinLength($context));
	}

	public static function dataGetMinLength(): array {
		return [
			[null, true, 20],
			[PasswordContext::ACCOUNT, true, 20],
			[PasswordContext::SHARING, true, 42],
			[PasswordContext::SHARING, false, 20],
		];
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testGetEnforceNonCommonPassword($appConfigValue, $expected) {
		$this->appConfig
			->expects(self::once())
			->method('getValueBool')
			->with('password_policy', 'enforceNonCommonPassword', true)
			->willReturn($appConfigValue);

		$this->assertSame($expected,
			$this->instance->getEnforceNonCommonPassword()
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testGetEnforceUpperLowerCase($appConfigValue, $expected) {
		$this->appConfig
			->expects(self::once())
			->method('getValueBool')
			->with('password_policy', 'enforceUpperLowerCase', false)
			->willReturn($appConfigValue);

		$this->assertSame($expected,
			$this->instance->getEnforceUpperLowerCase()
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testGetEnforceNumericCharacters($appConfigValue, $expected) {
		$this->appConfig
			->expects(self::once())
			->method('getValueBool')
			->with('password_policy', 'enforceNumericCharacters', false)
			->willReturn($appConfigValue);

		$this->assertSame($expected,
			$this->instance->getEnforceNumericCharacters()
		);
	}


	/**
	 * @dataProvider configTestData
	 */
	public function testGetEnforceSpecialCharacters($appConfigValue, $expected) {
		$this->appConfig
			->expects(self::once())
			->method('getValueBool')
			->with('password_policy', 'enforceSpecialCharacters', false)
			->willReturn($appConfigValue);

		$this->assertSame($expected,
			$this->instance->getEnforceSpecialCharacters()
		);
	}

	public function testSetMinLength() {
		$expected = 42;

		$this->appConfig
			->expects(self::once())
			->method('setValueInt')
			->with('password_policy', 'minLength', $expected);

		$this->instance->setMinLength($expected);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testSetEnforceUpperLowerCase($expected, $setValue) {
		$this->appConfig
			->expects(self::once())
			->method('setValueBool')
			->with('password_policy', 'enforceUpperLowerCase', $expected);

		$this->instance->setEnforceUpperLowerCase($setValue);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testSetEnforceNumericCharacters($expected, $setValue) {
		$this->appConfig
			->expects(self::once())
			->method('setValueBool')
			->with('password_policy', 'enforceNumericCharacters', $expected);

		$this->instance->setEnforceNumericCharacters($setValue);
	}


	/**
	 * @dataProvider configTestData
	 */
	public function testSetEnforceSpecialCharacters($expected, $setValue) {
		$this->appConfig
			->expects(self::once())
			->method('setValueBool')
			->with('password_policy', 'enforceSpecialCharacters', $expected);

		$this->instance->setEnforceSpecialCharacters($setValue);
	}

	public static function configTestData() {
		return [
			[true, true],
			[false, false]
		];
	}
}
