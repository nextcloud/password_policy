<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Controller;

use OCA\Password_Policy\Generator;
use OCA\Password_Policy\PasswordValidator;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\HintException;
use OCP\IRequest;

class APIController extends OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		private PasswordValidator $validator,
		private Generator $generator,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Validate a password against the enabled password policy rules
	 *
	 * @param string $password The password to validate
	 * @return DataResponse<Http::STATUS_OK, array{passed: bool, reason?: string}, array{}>
	 *
	 * 200: Always
	 */
	#[NoAdminRequired]
	public function validate(string $password): DataResponse {
		try {
			$this->validator->validate($password);
		} catch (HintException $e) {
			return new DataResponse([
				'passed' => false,
				'reason' => $e->getHint(),
			]);
		}

		return new DataResponse([
			'passed' => true,
		]);
	}

	/**
	 * Generate a random password that validates against the enabled password policy rules
	 *
	 * @return DataResponse<Http::STATUS_OK, array{password: string}, array{}>|DataResponse<Http::STATUS_CONFLICT, list<empty>, array{}>
	 *
	 * 200: Password generated
	 * 409: Generated password accidentally failed to validate against the rules, retry.
	 */
	#[NoAdminRequired]
	public function generate(): DataResponse {
		try {
			$password = $this->generator->generate();
		} catch (HintException) {
			return new DataResponse([], Http::STATUS_CONFLICT);
		}

		return new DataResponse([
			'password' => $password,
		]);
	}
}
