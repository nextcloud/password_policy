<?php
namespace OCA\Password_Policy\BackgroundJobs;

use OC\BackgroundJob\TimedJob;
use OCA\Password_Policy\Db\UserDAO;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\IGroupManager;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserManager;


class DisableUserTask extends TimedJob {

    /**
     * @var PasswordPolicyConfig
     */
    private $config;

    /**
     * @var ILogger
     */
    private $logger;

    /**
     * @var UserDAO
     */
    private $userDAO;

    /**
     * @var IUserManager
     */
    private $userManager;

    /**
     * @var IGroupManager
     */
    protected $groupManager;



    public function __construct(PasswordPolicyConfig $config, IUserManager $userManager, IGroupManager $groupManager) {
        // Run once a day
        $this->setInterval(24 * 60 * 60);

        $this->config = $config;
        $this->logger = \OC::$server->getLogger();
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;

        $connection = \OC::$server->getDatabaseConnection();
        $this->userDAO = new UserDAO($connection);
    }

    protected function run($argument) {
        if ($this->config->getExpirationDays() > 0) {
            $this->disableExpiredUsers();
            $this->initialSetOfLastChangedField();
        }

    }

    protected function disableExpiredUsers() {
        $disableTimestamp = strtotime('-' . $this->config->getExpirationDays() . ' days');

        $users = $this->userDAO->findAllUsersExpirationDataBy('last_changed', $disableTimestamp, '<');

        foreach ($users as $user) {
            $userObject = $this->userManager->get($user['uid']);

            if ($this->userPasswordCanExpire($userObject)) {
                $userObject->setEnabled(false);

                $this->logger->debug("User '" . $user['uid'] . "' disabled");
            }
        }
    }

    protected function initialSetOfLastChangedField() {

        $users = $this->userDAO->findAllUsersNotInExpirationTable();

        foreach ($users as $user) {
            $userObject = $this->userManager->get($user['uid']);

            if ($this->userPasswordCanExpire($userObject)) {
                $fields = [
                    'uid' => $user['uid'],
                    'last_changed' => time()
                ];

                $this->userDAO->updateUserExpirationData($fields);
                $this->logger->debug("Add User expiration data for: '" . $user['uid'] . "'");
            }
        }
    }

    /**
     * @param IUser $userObject
     *
     * @return bool
     */
    protected function userPasswordCanExpire($userObject) {
        $excludedGroups = explode('|', $this->config->getExcludeGroups());
        $userHasGroup = false;

        foreach ($excludedGroups as $group) {
            if ($this->groupManager->isInGroup($userObject->getUID(), $group)) {
                $userHasGroup = true;
            }
        }

        return !$this->groupManager->isAdmin($userObject->getUID()) && $userObject->isEnabled() && !$userHasGroup;
    }
}
