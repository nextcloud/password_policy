<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Validator;

use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\HintException;
use OCP\Http\Client\IClientService;
use OCP\IL10N;
use OCP\Security\PasswordContext;
use Psr\Log\LoggerInterface;

class HIBPValidator implements IValidator {

	public function __construct(
		private PasswordPolicyConfig $config,
		private IL10N $l,
		private IClientService $clientService,
		private LoggerInterface $logger,
	) {
	}

	public function validate(string $password, ?PasswordContext $context = null): void {
		if ($this->config->getEnforceHaveIBeenPwned($context)) {
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
				$this->logger->info('Could not connect to HaveIBeenPwned API', ['exception' => $e]);
				return;
			}

			$result = $response->getBody();
			if (is_resource($result)) {
				$result = stream_get_contents($result);
			} elseif ($result === null) {
				$this->logger->info('Could not read content from HaveIBeenPwned API, body was null');
				return;
			}
			$result = preg_replace('/^([0-9A-Z]+:0)$/m', '', $result);

			if (str_contains($result, $needle)) {
				$message = 'Password is present in compromised password list. Please choose a different password.';
				$message_t = $this->l->t(
					'Password is present in compromised password list. Please choose a different password.'
				);
				throw new HintException($message, $message_t);
			}
		}
	}
}
