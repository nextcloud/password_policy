<?php

declare(strict_types=1);
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


namespace OCA\Password_Policy;

use OC\HintException;
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

class PasswordValidator {
	public const POLICY_USER = 'policyUser';
	public const POLICY_SHARE = 'policyShare';

	/** @var IAppContainer */
	private $container;

	/** @var PasswordPolicyConfig */
	private $config;

	/** @var ILogger */
	private $logger;

	public function __construct(IAppContainer $container,
								PasswordPolicyConfig $config,
								ILogger $logger) {
		$this->container = $container;
		$this->config = $config;
		$this->logger = $logger;
	}

	/**
	 * check if the given password matches the conditions defined by the admin
	 *
	 * @throws HintException
	 */
	public function validate(string $password, string $type = self::POLICY_USER): void {
		// Use Validators and parameters dependent on type
		if ($type === self::POLICY_SHARE) {
			$validators = [
				LengthValidator::class => $this->config->getSharingMinLength(),
				CommonPasswordsValidator::class => $this->config->getSharingEnforceNonCommonPassword(),
				NumericCharacterValidator::class => $this->config->getSharingEnforceNumericCharacters(),
				UpperCaseLoweCaseValidator::class => $this->config->getSharingEnforceUpperLowerCase(),
				SpecialCharactersValidator::class => $this->config->getSharingEnforceSpecialCharacters(),
			];
		} else {
			// Default to User Validators
			$validators = [
				LengthValidator::class => $this->config->getMinLength(),
				CommonPasswordsValidator::class => $this->config->getEnforceNonCommonPassword(),
				NumericCharacterValidator::class => $this->config->getEnforceNumericCharacters(),
				UpperCaseLoweCaseValidator::class => $this->config->getEnforceUpperLowerCase(),
				SpecialCharactersValidator::class => $this->config->getEnforceSpecialCharacters(),
				HIBPValidator::class => $this->config->getEnforceHaveIBeenPwned(),
			];
		}

		$errors = [];
		$hints = [];
		foreach ($validators as $validator => $validationParam) {
			try {
				/** @var IValidator $instance */
				$instance = $this->container->query($validator);
			} catch (QueryException $e) {
				//ignore and continue
				$this->logger->logException($e, ['level' => ILogger::INFO]);
				continue;
			}

			try {
				$instance->validate($password, $validationParam);
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
