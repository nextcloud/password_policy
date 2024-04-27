<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Validator;

use OC\HintException;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\Http\Client\IClientService;
use OCP\IL10N;
use OCP\ILogger;

class HIBPValidator implements IValidator {

	/** @var PasswordPolicyConfig */
	private $config;
	/** @var IL10N */
	private $l;
	/** @var IClientService */
	private $clientService;
	/** @var ILogger */
	private $logger;

	public function __construct(PasswordPolicyConfig $config,
		IL10N $l,
		IClientService $clientService,
		ILogger $logger) {
		$this->config = $config;
		$this->l = $l;
		$this->clientService = $clientService;
		$this->logger = $logger;
	}

	public function validate(string $password): void {
		if ($this->config->getEnforceHaveIBeenPwned()) {
			$hash = sha1($password);
			$range = substr($hash, 0, 5);
			$needle = strtoupper(substr($hash, 5));

			$client = $this->clientService->newClient();

			try {
				$response = $client->get(
					'https://api.pwnedpasswords.com/range/' . $range,
					[
						'timeout' => 5,
						'headers' => [
							'Add-Padding' => 'true'
						]
					]
				);
			} catch (\Exception $e) {
				$this->logger->logException($e, ['level' => ILogger::INFO]);
				return;
			}

			$result = $response->getBody();
			$result = preg_replace('/^([0-9A-Z]+:0)$/m', '', $result);

			if (strpos($result, $needle) !== false) {
				$message = 'Password is present in compromised password list. Please choose a different password.';
				$message_t = $this->l->t(
					'Password is present in compromised password list. Please choose a different password.'
				);
				throw new HintException($message, $message_t);
			}
		}
	}
}
