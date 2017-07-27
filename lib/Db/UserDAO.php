<?php
namespace OCA\Password_Policy\Db;

use OCP\IDBConnection;

class UserDAO {

    private $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function findAllUsers() {
        $sql = 'SELECT * FROM oc_users LEFT JOIN oc_password_policy_expiration ON oc_users.uid = oc_password_policy_expiration.uid';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();

        $stmt->closeCursor();
        return $rows;
    }


    /**
     * Updates or inserts (if not exists) the expiration Data for a user.
     * User-Name (uid) has to be set within the $fields array
     *
     * @param array $fields
     *
     * @return int number of affected rows
     */
    public function updateUserExpirationData(array $fields) {
        $sql = "INSERT INTO oc_password_policy_expiration";
        $sql .= " (" . implode(',', array_keys($fields)) .  ")";
        $sql .= ' VALUES ("' . implode('","', $fields) .'")';
        $sql .= " ON DUPLICATE KEY UPDATE ";

        $updateFields = [];
        foreach ($fields as $key => $value) {
            $updateFields[] = $key . '= "' . $value . '"';
        }

        $sql .= implode(', ', $updateFields);

        return $this->db->executeUpdate($sql);
    }



    /**
     * @param        $field
     * @param        $value
     * @param string $operation
     *
     * @return array
     */
    public function findAllUsersExpirationDataBy($field, $value, $operation='=') {
        $sql = 'SELECT * FROM oc_password_policy_expiration';
        $sql .= ' WHERE ' . $field . ' ' . $operation . ' "' . $value . '"';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();

        $stmt->closeCursor();
        return $rows;
    }



    /**
     * @return array
     */
    public function findAllUsersNotInExpirationTable() {
        $sql = 'SELECT oc_users.* FROM oc_users LEFT JOIN oc_password_policy_expiration ON oc_users.uid = oc_password_policy_expiration.uid';
        $sql .= ' WHERE last_changed IS NULL';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();

        $stmt->closeCursor();
        return $rows;
    }



    /**
     *  Delete expiration data for one userUid
     *
     * @param $userUid
     */
    public function deleteUserExpirationData($userUid) {
        $sql = 'DELETE FROM oc_password_policy_expiration';
        $sql .= ' WHERE uid="' . $userUid . '"';

        $this->db->executeQuery($sql);
    }
}
