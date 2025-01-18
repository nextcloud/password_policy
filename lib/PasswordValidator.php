<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */


namespace OCA\Password_Policy;

use OCA\Password_Policy\Validator\CommonPasswordsValidator;
use OCA\Password_Policy\Validator\HIBPValidator;
use OCA\Password_Policy\Validator\IValidator;
use OCA\Password_Policy\Validator\LengthValidator;
use OCA\Password_Policy\Validator\NumericCharacterValidator;
use OCA\Password_Policy\Validator\SpecialCharactersValidator;
use OCA\Password_Policy\Validator\UpperCaseLoweCaseValidator;
use OCP\HintException;
use OCP\Security\PasswordContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class PasswordValidator {

	public function __construct(
		private ContainerInterface $container,
		private LoggerInterface $logger,
	) {
	}

	/**
	 * check if the given password matches the conditions defined by the admin
	 *
	 * @throws HintException
	 */
	public function validate(string $password, ?PasswordContext $context = null): void {
		$validators = [
			CommonPasswordsValidator::class,
			LengthValidator::class,
			NumericCharacterValidator::class,
			UpperCaseLoweCaseValidator::class,
			SpecialCharactersValidator::class,
			HIBPValidator::class,
		];

		$errors = [];
		$hints = [];
		foreach ($validators as $validator) {
			try {
				/** @var IValidator $instance */
				$instance = $this->container->get($validator);
			} catch (ContainerExceptionInterface $e) {
				//ignore and continue
				$this->logger->info('Could not get validator from container', ['validator' => $validator, 'exception' => $e]);
				continue;
			}

			try {
				$instance->validate($password, $context);
			} catch (HintException $e) {
				$errors[] = $e->getMessage();
				$hints[] = $e->getHint();
			}
		}

		if (!empty($errors)) {
			throw new HintException(
				implode(' ', $errors),
				implode(' ', $hints)
			);
		}
	}
}
