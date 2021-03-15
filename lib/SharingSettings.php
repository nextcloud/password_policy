<?php

declare(strict_types=1);
/**
 * @copyright 2017, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\IInitialStateService;
use OCP\Util;

class SharingSettings implements ISettings {
	private $appName;

	/** @var PasswordPolicyConfig */
	private $config;

	/** @var IInitialStateService */
	private $initialStateService;

	public function __construct(string $appName,
								PasswordPolicyConfig $config,
								IInitialStateService $initialStateService) {
		$this->appName = $appName;
		$this->config = $config;
		$this->initialStateService = $initialStateService;
	}

	public function getForm(): TemplateResponse {
		Util::addScript($this->appName, 'password_policy-sharingSettings');

		$this->initialStateService->provideInitialState($this->appName, 'config', [
			'sharingMinLength' => $this->config->getSharingMinLength(),
			'sharingEnforceNonCommonPassword' => $this->config->getSharingEnforceNonCommonPassword(),
			'sharingEnforceUpperLowerCase' => $this->config->getSharingEnforceUpperLowerCase(),
			'sharingEnforceNumericCharacters' => $this->config->getSharingEnforceNumericCharacters(),
			'sharingEnforceSpecialCharacters' => $this->config->getSharingEnforceSpecialCharacters(),
		]);

		return new TemplateResponse($this->appName, 'settings');
	}

	public function getSection(): string {
		return 'sharing';
	}

	public function getPriority(): int {
		return 20;
	}
}
