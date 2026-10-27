<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Password_Policy;

use OCA\Password_Policy\AppInfo\Application;
use OCP\Config\IUserConfig;
use OCP\IUser;
use OCP\L10N\IFactory;
use OCP\Mail\IMailer;

class LockoutService {

	public function __construct(
		private IUserConfig $userConfig,
		private IMailer $mailer,
		private IFactory $languageFactory,
		private PasswordPolicyConfig $policyConfig,
	) {
	}

	public function lockout(IUser $user, bool $lock = true): void {
		if ($lock) {
			$this->userConfig->setValueString($user->getUID(), Application::APP_ID, 'lockout_time', (string)time());
		} else {
			$this->userConfig->deleteUserConfig($user->getUID(), Application::APP_ID, 'lockout_time');
		}
	}

	public function getLockoutTime(IUser $user): ?int {
		$lockoutTime = $this->userConfig->getValueString($user->getUID(), Application::APP_ID, 'lockout_time', '');
		if ($lockoutTime === '') {
			return null;
		}
		return (int)$lockoutTime;
	}

	public function getAppUsers(): array {
		return $this->userConfig->getUserIds(Application::APP_ID);
	}

	/**
	 * Notify the user via email
	 * @param IUser $user the user to notify
	 * @return void
	 */
	public function notifyMail(IUser $user): void {
		$template = $this->mailer->createEMailTemplate('password_policy.Lockout', [
			'user' => $user->getDisplayName(),
		]);

		$language = $this->languageFactory->getUserLanguage($user);
		$l = $this->languageFactory->get(Application::APP_ID, $language, $this->userConfig->getValueString($user->getUID(), 'core', 'locale'));

		$duration = $this->policyConfig->getFailedLoginLockoutDuration();

		$template->setSubject($l->t('Too many consecutive failed login attempts'));
		$template->addHeader();
		$template->addHeading($l->t('Multiple failed login attempts detected on your account (\'%1s\').', [$user->getDisplayName()]));
		if ($duration === 0) {
			$template->addBodyText($l->t('Your account has been locked.'));
		} else {
			$template->addBodyText($l->t('Your account has been temporarily locked for %1d minutes.', [ $duration ]));
		}
		$template->addBodyText($l->t('If you did not attempt to log in, please check with your administrator how securing your account. You can also request your administrator to unlock your account.'));
		$template->addFooter();

		$message = $this->mailer->createMessage();
		$message->setTo([$user->getEMailAddress() => $user->getDisplayName()]);
		$message->useTemplate($template);
		$this->mailer->send($message);
	}

}
