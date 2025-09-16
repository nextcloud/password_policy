<?php

/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Tests;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Password_Policy\PasswordValidator;
use OCA\Password_Policy\Validator\CommonPasswordsValidator;
use OCA\Password_Policy\Validator\HIBPValidator;
use OCA\Password_Policy\Validator\IValidator;
use OCA\Password_Policy\Validator\LengthValidator;
use OCA\Password_Policy\Validator\NumericCharacterValidator;
use OCA\Password_Policy\Validator\SpecialCharactersValidator;
use OCA\Password_Policy\Validator\UpperCaseLoweCaseValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class PasswordValidatorTest extends TestCase {

	private ContainerInterface&MockObject $container;
	private LoggerInterface&MockObject $logger;
	private PasswordValidator $validator;

	protected function setUp(): void {
		parent::setUp();

		$this->container = $this->createMock(ContainerInterface::class);
		$this->logger = $this->createMock(LoggerInterface::class);

		$this->validator = new PasswordValidator($this->container, $this->logger);
	}

	public function testValidate() {
		$validators = [
			CommonPasswordsValidator::class,
			LengthValidator::class,
			NumericCharacterValidator::class,
			UpperCaseLoweCaseValidator::class,
			SpecialCharactersValidator::class,
			HIBPValidator::class,
		];

		$this->container->method('get')
			->willReturnCallback(function ($class) use (&$validators) {
				if (($key = array_search($class, $validators)) !== false) {
					$validator = $this->createMock(IValidator::class);
					$validator->expects($this->once())
						->method('validate')
						->with('password');

					unset($validators[$key]);

					return $validator;
				}

				throw $this->createMock(ContainerExceptionInterface::class);
			});

		$this->logger->expects($this->never())->method($this->anything());

		$this->validator->validate('password');
		$this->assertEmpty($validators);
	}
}
