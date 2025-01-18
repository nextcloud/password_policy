<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Tests\Validator;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCA\Password_Policy\Validator\HIBPValidator;
use OCA\Password_Policy\Validator\IValidator;
use OCP\HintException;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\IL10N;
use OCP\Security\PasswordContext;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class HIBPValidatorTest extends TestCase {

	private PasswordPolicyConfig&MockObject $config;
	private IL10N&MockObject $l;
	private IClientService&MockObject $clientService;
	private LoggerInterface&MockObject $logger;
	private IValidator $validator;

	protected static array $resources = [];

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(PasswordPolicyConfig::class);
		$this->l = $this->createMock(IL10N::class);
		$this->clientService = $this->createMock(IClientService::class);
		$this->logger = $this->createMock(LoggerInterface::class);

		$this->validator = new HIBPValidator(
			$this->config,
			$this->l,
			$this->clientService,
			$this->logger,
		);
	}

	protected function tearDown(): void {
		foreach (self::$resources as $resource) {
			fclose($resource);
		}
		self::$resources = [];

		parent::tearDown();
	}

	/**
	 * Ensure that different contexts can yield different configuration values
	 * @dataProvider dataValidateWithContext
	 */
	public function testValidateWithContext(?PasswordContext $context, bool $expected): void {
		$this->config
			->method('getEnforceHaveIBeenPwned')
			->willReturnMap([
				[null, true],
				[PasswordContext::ACCOUNT, true],
				[PasswordContext::SHARING, false],
			]);

		$client = $this->createMock(IClient::class);
		$this->clientService->method('newClient')->willReturn($client);

		$response = $this->createMock(IResponse::class);
		$client->method('get')
			->with('https://api.pwnedpasswords.com/range/250e7', self::anything())
			->willReturn($response);

		$response->method('getBody')
			->willReturn("7EB3AD72E4AC6182E6E831E33395F97C419:1\n7F12A5AB6972A0895D290C4792F0A326EA8:270322");

		if (!$expected) {
			$this->expectException(HintException::class);
		} else {
			$this->assertTrue(true);
		}

		$this->validator->validate('banana', $context);
	}

	public static function dataValidateWithContext(): array {
		return [
			[null, false],
			[PasswordContext::ACCOUNT, false],
			[PasswordContext::SHARING, true],
		];
	}

	/**
	 * @dataProvider dataValidate
	 */
	public function testValidate($responseBody, bool $expected): void {
		// Mark as used
		if (is_resource($responseBody)) {
			self::$resources[] = $responseBody;
		}

		$this->config
			->method('getEnforceHaveIBeenPwned')
			->willReturnMap([
				[null, true],
				[PasswordContext::ACCOUNT, true],
				[PasswordContext::SHARING, false],
			]);

		$client = $this->createMock(IClient::class);
		$this->clientService->method('newClient')->willReturn($client);

		$response = $this->createMock(IResponse::class);
		$client->method('get')
			->with('https://api.pwnedpasswords.com/range/250e7', self::anything())
			->willReturn($response);

		$response->method('getBody')
			->willReturn($responseBody);

		if (!$expected) {
			$this->expectException(HintException::class);
		} else {
			$this->assertTrue(true);
		}

		$this->validator->validate('banana');
	}

	public static function dataValidate(): array {
		function mkResource(string $text) {
			$resource = fopen('php://temp', 'r+');
			fwrite($resource, $text);
			rewind($resource);
			return $resource;
		}

		$resourceBad = mkResource("7EB3AD72E4AC6182E6E831E33395F97C419:1\n7F12A5AB6972A0895D290C4792F0A326EA8:270322");
		$resourceGood = mkResource('7EB3AD72E4AC6182E6E831E33395F97C419:1');

		return [
			'pwned' => ["7EB3AD72E4AC6182E6E831E33395F97C419:1\n7F12A5AB6972A0895D290C4792F0A326EA8:270322", false],
			'known but no breach?' => ["7EB3AD72E4AC6182E6E831E33395F97C419:1\n7F12A5AB6972A0895D290C4792F0A326EA8:0", true],
			'not pwned' => ['7EB3AD72E4AC6182E6E831E33395F97C419:1', true],
			'resource like pwned' => [$resourceBad, false],
			'resource like' => [$resourceGood, true],
		];
	}
}
