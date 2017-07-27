<?php
namespace OCA\Password_Policy\Hook;

use OC\HintException;
use OCA\Password_Policy\Db\UserDAO;

class UserHooks {

    /**
     * @param $params
     *
     * @throws HintException
     */
    static public function afterPasswordSet($params) {
        $connection = \OC::$server->getDatabaseConnection();
        $userDAO = new UserDAO($connection);

        $fields = [
            'uid' => $params['uid'],
            'last_changed' => time(),
            'notification_sent' => 0
        ];

        $result = $userDAO->updateUserExpirationData($fields);

        if ($result != 1) {
            throw new HintException("Writing expiration data failed for user " . $params['uid'],"An error occured (1501483501). Please contact your administrator.");
        }
    }



    /**
     * @param $params
     */
    static public function afterUserDeleted($params) {
        $connection = \OC::$server->getDatabaseConnection();
        $userDAO = new UserDAO($connection);

        $userDAO->deleteUserExpirationData($params['uid']);
    }

}