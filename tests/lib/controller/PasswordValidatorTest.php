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


use OC\HintException;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCA\Password_Policy\PasswordValidator;
use OCP\Http\Client\IClientService;
use OCP\IL10N;
use Test\TestCase;

class PasswordValidatorTest extends TestCase {

	/** @var  PasswordPolicyConfig|\PHPUnit_Framework_MockObject_MockObject */
	private $config;

	/** @var  IL10N|\PHPUnit_Framework_MockObject_MockObject */
	private $l10n;

	/** @var IClientService|\PHPUnit_Framework_MockObject_MockObject */
	private $clientService;

	public function setUp() {
		parent::setUp();

		$this->l10n = $this->createMock(IL10N::class);
		$this->config = $this->createMock(PasswordPolicyConfig::class);
		$this->clientService = $this->createMock(IClientService::class);
	}

	/**
	 * @param array $mockedMethods
	 * @return PasswordValidator | \PHPUnit_Framework_MockObject_MockObject
	 */
	private function getInstance($mockedMethods = []) {
		$passwordValidator = $this->getMockBuilder(PasswordValidator::class)
			->setConstructorArgs([$this->config, $this->l10n, $this->clientService])
			->setMethods($mockedMethods)->getMock();

		return $passwordValidator;
	}

	public function testCheckPasswordLength() {
		$this->config->expects($this->exactly(2))->method('getMinLength')->willReturn(4);
		$instance = $this->getInstance();

		$this->invokePrivate($instance, 'checkPasswordLength', ['password']);
		$this->invokePrivate($instance, 'checkPasswordLength', ['1234']);
	}

	/**
	 * @expectedException \OC\HintException
	 */
	public function testCheckPasswordLengthFail() {
		$this->config->expects($this->once())->method('getMinLength')->willReturn(4);
		$instance = $this->getInstance();

		$this->invokePrivate($instance, 'checkPasswordLength', ['123']);
	}

	/**
	 * @dataProvider dataTestCheckUpperLowerCase
	 *
	 * @param string $password
	 * @param bool $enforceUpperLowerCase
	 */
	public function testCheckUpperLowerCase($password, $enforceUpperLowerCase) {
		$this->config->expects($this->once())->method('getEnforceUpperLowerCase')
			->willReturn($enforceUpperLowerCase);

		$instance = $this->getInstance();

		$this->invokePrivate($instance, 'checkUpperLowerCase', [$password]);
	}

	public function dataTestCheckUpperLowerCase() {
		return [
			['passWord', true],
			['PASSwORD', true],
			['password', false],
			['PASSWORD', false],
		];
	}


	/**
	 * @dataProvider dataTestCheckUpperLowerCaseFail
	 * @expectedException \OC\HintException
	 */
	public function testCheckUpperLowerCaseFail($password) {
		$this->config->expects($this->once())->method('getEnforceUpperLowerCase')->willReturn(true);
		$instance = $this->getInstance();

		$this->invokePrivate($instance, 'checkUpperLowerCase', [$password]);
	}

	public function dataTestCheckUpperLowerCaseFail() {
		return [
			['password'], ['PASSWORD']
		];
	}


	/**
	 * @dataProvider dataTestCheckNumericCharacters
	 *
	 * @param string $password
	 * @param bool $enforceNumericCharacters
	 */
	public function testCheckNumericCharacters($password, $enforceNumericCharacters) {
		$this->config->expects($this->once())->method('getEnforceNumericCharacters')
			->willReturn($enforceNumericCharacters);

		$instance = $this->getInstance();

		$this->invokePrivate($instance, 'checkNumericCharacters', [$password]);
	}

	public function dataTestCheckNumericCharacters() {
		return [
			['password42', true],
			['password', false]
		];
	}


	/**
	 * @expectedException \OC\HintException
	 */
	public function testCheckNumericCharactersFail() {

		$password = 'pass%word';

		$this->config->expects($this->once())->method('getEnforceNumericCharacters')->willReturn(true);
		$instance = $this->getInstance();

		$this->invokePrivate($instance, 'checkNumericCharacters', [$password]);
	}

	/**
	 * @dataProvider dataTestCheckSpecialCharacters
	 *
	 * @param string $password
	 * @param bool $enforceSpecialCharacters
	 */
	public function testCheckSpecialCharacters($password, $enforceSpecialCharacters) {
		$this->config->expects($this->once())->method('getEnforceSpecialCharacters')
			->willReturn($enforceSpecialCharacters);

		$instance = $this->getInstance();

		$this->invokePrivate($instance, 'checkSpecialCharacters', [$password]);
	}

	public function dataTestCheckSpecialCharacters() {
		return [
			['pass%word', true],
			['password', false]
		];
	}


	/**
	 * @expectedException \OC\HintException
	 */
	public function testCheckSpecialCharactersFail() {

		$password = 'password42';

		$this->config->expects($this->once())->method('getEnforceSpecialCharacters')->willReturn(true);
		$instance = $this->getInstance();

		$this->invokePrivate($instance, 'checkSpecialCharacters', [$password]);
	}

	/**
	 * @expectedException \OC\HintException
	 */
	public function testCheckCommonPasswordsFail() {
		$password = 'banana';

		$this->config->expects($this->once())->method('getEnforceNonCommonPassword')->willReturn(true);
		$instance = $this->getInstance();

		$this->invokePrivate($instance, 'checkCommonPasswords', [$password]);
	}

	public function testCheckCommonPasswordsPass() {
		$password = 'banana1038462';

		$this->config->expects($this->once())->method('getEnforceNonCommonPassword')->willReturn(true);
		$instance = $this->getInstance();

		$this->invokePrivate($instance, 'checkCommonPasswords', [$password]);
	}

	public function testValidate() {

		$password = 'password';

		$instance = $this->getInstance(
			[
				'checkPasswordLength',
				'checkUpperLowerCase',
				'checkNumericCharacters',
				'checkSpecialCharacters',
				'checkCommonPasswords',
			]
		);

		$instance->expects($this->once())->method('checkPasswordLength')->with($password);
		$instance->expects($this->once())->method('checkUpperLowerCase')->with($password);
		$instance->expects($this->once())->method('checkNumericCharacters')->with($password);
		$instance->expects($this->once())->method('checkSpecialCharacters')->with($password);
		$instance->expects($this->once())->method('checkCommonPasswords')->with($password);

		$instance->validate($password);
	}


}
