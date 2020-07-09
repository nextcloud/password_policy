<?php
declare(strict_types=1);
/**
 * @copyright 2017, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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

namespace OCA\Password_Policy\AppInfo;

use OCA\Password_Policy\Capabilities;
use OCA\Password_Policy\Listener\BeforePasswordUpdatedEventListener;
use OCA\Password_Policy\Listener\BeforeUserLoggedInEventListener;
use OCA\Password_Policy\Listener\FailedLoginListener;
use OCA\Password_Policy\Listener\GenerateSecurePasswordEventListener;
use OCA\Password_Policy\Listener\PasswordUpdatedEventListener;
use OCA\Password_Policy\Listener\SuccesfullLoginListener;
use OCA\Password_Policy\Listener\ValidatePasswordPolicyEventListener;
use OCA\Password_Policy\PasswordValidator;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Authentication\Events\LoginFailedEvent;
use OCP\ILogger;
use OCP\Security\Events\GenerateSecurePasswordEvent;
use OCP\Security\Events\ValidatePasswordPolicyEvent;
use OCP\User\Events\BeforePasswordUpdatedEvent;
use OCP\User\Events\BeforeUserLoggedInEvent;
use OCP\User\Events\PasswordUpdatedEvent;
use OCP\User\Events\UserLoggedInEvent;
use Symfony\Component\EventDispatcher\GenericEvent;

class Application extends App implements IBootstrap {
	public function __construct() {
		parent::__construct('password_policy');
	}

	public function register(IRegistrationContext $context): void {
		$context->registerCapability(Capabilities::class);

		$context->registerEventListener(ValidatePasswordPolicyEvent::class, ValidatePasswordPolicyEventListener::class);
		$context->registerEventListener(GenerateSecurePasswordEvent::class, GenerateSecurePasswordEventListener::class);
		$context->registerEventListener(BeforePasswordUpdatedEvent::class, BeforePasswordUpdatedEventListener::class);
		$context->registerEventListener(PasswordUpdatedEvent::class, PasswordUpdatedEventListener::class);
		$context->registerEventListener(BeforeUserLoggedInEvent::class, BeforeUserLoggedInEventListener::class);
		$context->registerEventListener(LoginFailedEvent::class, FailedLoginListener::class);
		$context->registerEventListener(UserLoggedInEvent::class, SuccesfullLoginListener::class);
	}

	public function boot(IBootContext $context): void {
		$server = $context->getServerContainer();

		// TODO: remove this legacy event listener once https://github.com/nextcloud/user_sql/pull/146 is in
		$symfonyDispatcher = $server->getEventDispatcher();
		$symfonyDispatcher->addListener(
			'OCP\PasswordPolicy::validate',
			function (GenericEvent $event) use ($server) {
				/** @var ILogger $logger */
				$logger = $server->query(ILogger::class);
				$logger->debug('OCP\PasswordPolicy::validate is deprecated. Listen to ' . ValidatePasswordPolicyEvent::class . ' instead');

				/** @var PasswordValidator $validator */
				$validator = $server->query(PasswordValidator::class);
				$validator->validate($event->getSubject());
			}
		);
	}
}
