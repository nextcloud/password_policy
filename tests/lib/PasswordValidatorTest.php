<?php
/**
 * @copyright Copyright (c) 2016 Bjoern Schiessle <bjoern@schiessle.org>
 *
 * @author Bjoern Schiessle <bjoern@schiessle.org>
 * @author Jonas Rittershofer <jotoeri@users.noreply.github.com>
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
use OCA\Password_Policy\PasswordValidator;
use OCA\Password_Policy\Validator\CommonPasswordsValidator;
use OCA\Password_Policy\Validator\HIBPValidator;
use OCA\Password_Policy\Validator\IValidator;
use OCA\Password_Policy\Validator\LengthValidator;
use OCA\Password_Policy\Validator\NumericCharacterValidator;
use OCA\Password_Policy\Validator\SpecialCharactersValidator;
use OCA\Password_Policy\Validator\UpperCaseLoweCaseValidator;
use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\QueryException;
use OCP\ILogger;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class PasswordValidatorTest extends TestCase {

	/** @var IAppContainer|MockObject */
	private $container;

	/** @var PasswordPolicyConfig|MockObject */
	private $config;

	/** @var ILogger|MockObject */
	private $logger;

	/** @var PasswordValidator */
	private $validator;


	protected function setUp(): void {
		parent::setUp();

		$this->container = $this->createMock(IAppContainer::class);
		$this->config = $this->createMock(PasswordPolicyConfig::class);
		$this->logger = $this->createMock(ILogger::class);

		$this->validator = new PasswordValidator($this->container, $this->config, $this->logger);
	}

	public function testValidateUser() {
		$this->config->expects($this->once())->method('getMinLength')->willReturn(10);
		$this->config->expects($this->once())->method('getEnforceNonCommonPassword')->willReturn(true);
		$this->config->expects($this->once())->method('getEnforceNumericCharacters')->willReturn(true);
		$this->config->expects($this->once())->method('getEnforceUpperLowerCase')->willReturn(true);
		$this->config->expects($this->once())->method('getEnforceSpecialCharacters')->willReturn(true);
		$this->config->expects($this->once())->method('getEnforceHaveIBeenPwned')->willReturn(true);

		$validators = [
			CommonPasswordsValidator::class,
			LengthValidator::class,
			NumericCharacterValidator::class,
			UpperCaseLoweCaseValidator::class,
			SpecialCharactersValidator::class,
			HIBPValidator::class,
		];

		$this->container->method('query')
			->willReturnCallback(function ($class) use (&$validators) {
				if (($key = array_search($class, $validators)) !== false) {
					$validator = $this->createMock(IValidator::class);

					if ($class === LengthValidator::class) {
						$validator->expects($this->once())
							->method('validate')
							->with('password', 10);
					} else {
						$validator->expects($this->once())
							->method('validate')
							->with('password', true);
					}

					unset($validators[$key]);

					return $validator;
				}

				throw new QueryException();
			});

		$this->logger->expects($this->never())->method($this->anything());

		$this->validator->validate('password');
		$this->assertEmpty($validators);
	}
}
