<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Controller;

use OC\HintException;
use OCA\Password_Policy\Generator;
use OCA\Password_Policy\PasswordValidator;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class APIController extends OCSController {

	/** @var PasswordValidator */
	private $validator;
	/** @var Generator */
	private $generator;

	public function __construct(string $appName, IRequest $request, PasswordValidator $validator, Generator $generator) {
		parent::__construct($appName, $request);
		$this->validator = $validator;
		$this->generator = $generator;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param string $password
	 * @return DataResponse
	 */
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
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function generate(): DataResponse {
		try {
			$password = $this->generator->generate();
		} catch (HintException $e) {
			return new DataResponse([], Http::STATUS_CONFLICT);
		}

		return new DataResponse([
			'password' => $password,
		]);
	}
}
