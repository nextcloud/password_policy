<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy\Settings;

use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IL10N;
use OCP\Settings\IDelegatedSettings;
use OCP\Util;

class Settings implements IDelegatedSettings {

	public function __construct(
		private string $appName,
		private PasswordPolicyConfig $config,
		private IInitialState $initialStateService,
		private IL10N $l10n,
	) {
	}

	#[\Override]
	public function getForm(): TemplateResponse {
		Util::addStyle($this->appName, 'password_policy-settings');
		Util::addScript($this->appName, 'password_policy-settings');

		$this->initialStateService->provideInitialState('loginConfig', [
			'historySize' => $this->config->getHistorySize(),
			'expiration' => $this->config->getExpiryInDays(),
			'maximumLoginAttempts' => $this->config->getMaximumLoginAttempts(),
		]);

		return new TemplateResponse($this->appName, 'settings');
	}

	#[\Override]
	public function getSection(): string {
		return 'security';
	}

	#[\Override]
	public function getPriority(): int {
		return 50;
	}

	#[\Override]
	public function getName(): string {
		return $this->l10n->t('Password Policy');
	}

	#[\Override]
	public function getAuthorizedAppConfig(): array {
		return [];
	}
}
