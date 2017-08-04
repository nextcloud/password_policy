<?php
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
use OCA\Password_Policy\PasswordValidator;
use OCA\Password_Policy\Hook\UserHooks;
use OCP\AppFramework\App;
use Symfony\Component\EventDispatcher\GenericEvent;
use OCP\Util;

class Application extends App {

    public function __construct() {
		parent::__construct('password_policy');
		$container = $this->getContainer();

		$server = $container->getServer();
		$eventDispatcher = $server->getEventDispatcher();

		/** register capabilities */
		$container->registerCapability(Capabilities::class);

		$eventDispatcher->addListener('OCP\PasswordPolicy::validate',
			function(GenericEvent $event) use ($container) {
				/** @var PasswordValidator $validator */
				$validator = $container->query(PasswordValidator::class);
				$validator->validate($event->getSubject());
			}
		);
	}

	public function register() {
        $this->registerHooks();
        $this->registerBackgroundJobs();
    }

    public function registerHooks() {
        Util::connectHook('OC_User', 'post_setPassword', UserHooks::class, 'afterPasswordSet');
        Util::connectHook('OC_User', 'post_deleteUser', UserHooks::class, 'afterUserDeleted');
    }

    public function registerBackgroundJobs() {
        $jobList = $this->getContainer()->getServer()->getJobList();
        $jobList->add('OCA\Password_Policy\BackgroundJobs\NotifyUserTask');
        $jobList->add('OCA\Password_Policy\BackgroundJobs\DisableUserTask');
    }
}
