<?php
/**
 * @copyright Copyright (c) 2016 Bjoern Schiessle <bjoern@schiessle.org>
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


use OCP\IConfig;

/**
 * Class Config
 *
 * read/write config of the password policy
 *
 * @package OCA\Password_Policy
 */
class PasswordPolicyConfig {

	/** @var IConfig */
	private $config;

	/**
	 * Config constructor.
	 *
	 * @param IConfig $config
	 */
	public function __construct(IConfig $config) {
		$this->config = $config;
	}

	/**
	 * get the enforced minimum length of passwords
	 *
	 * @return int
	 */
	public function getMinLength() {
		$minLength = $this->config->getAppValue('password_policy', 'minLength', '8');
		return (int)$minLength;
	}

	/**
	 * Whether non-common passwords should be enforced
	 *
	 * @return bool
	 */
	public function getEnforceNonCommonPassword() {
		$enforceNonCommonPasswords = $this->config->getAppValue(
			'password_policy',
			'enforceNonCommonPassword',
			'1'
		);
		return $enforceNonCommonPasswords === '1';
	}

	/**
	 * does the password need to contain upper and lower case characters
	 *
	 * @return bool
	 */
	public function getEnforceUpperLowerCase() {
		$enforceUpperLowerCase = $this->config->getAppValue(
			'password_policy',
			'enforceUpperLowerCase',
			'0'
		);

		return $enforceUpperLowerCase === '1';
	}

	/**
	 * does the password need to contain numeric characters
	 *
	 * @return bool
	 */
	public function getEnforceNumericCharacters() {
		$enforceNumericCharacters = $this->config->getAppValue(
			'password_policy',
			'enforceNumericCharacters',
			'0'
		);

		return $enforceNumericCharacters === '1';
	}

	/**
	 * does the password need to contain special characters
	 *
	 * @return bool
	 */
	public function getEnforceSpecialCharacters() {
		$enforceSpecialCharacters = $this->config->getAppValue(
			'password_policy',
			'enforceSpecialCharacters',
			'0'
		);

		return $enforceSpecialCharacters === '1';
	}

    /**
     * get the days until an password expires
     *
     * @return int
     */
    public function getExpirationDays() {
        $expirationDays = $this->config->getAppValue('password_policy', 'expirationDays', '0');
        return (int)$expirationDays;
    }

    /**
     * how many days before password expiration should the notification email be send
     *
     * @return int
     */
    public function getExpirationMailDaysBefore() {
        $expirationMailDaysBefore = $this->config->getAppValue('password_policy', 'expirationMailDaysBefore', '0');
        return (int)$expirationMailDaysBefore;
    }

    /**
     * get the nextcloud host
     *
     * @return string
     */
    public function getNextcloudHost() {
        $nextcloudHost = $this->config->getAppValue('password_policy', 'nextcloudHost', '');
        return $nextcloudHost;
    }

    /**
     * get the excluded groups
     *
     * @return string
     */
    public function getExcludeGroups() {
        $excludeGroups = $this->config->getAppValue('password_policy', 'excludeGroups', '');
        $excludeGroups = implode('|', json_decode($excludeGroups, true));
        return $excludeGroups;
    }

	/**
	 * set minimal length of passwords
	 *
	 * @param int $minLength
	 */
	public function setMinLength($minLength) {
		$this->config->setAppValue('password_policy', 'minLength', $minLength);
	}

	/**
	 * enforce upper and lower case characters
	 *
	 * @param bool $enforceUpperLowerCase
	 */
	public function setEnforceUpperLowerCase($enforceUpperLowerCase) {
		$value = $enforceUpperLowerCase === true ? '1' : '0';
		$this->config->setAppValue('password_policy', 'enforceUpperLowerCase', $value);
	}

	/**
	 * enforce numeric characters
	 *
	 * @param bool $enforceNumericCharacters
	 */
	public function setEnforceNumericCharacters($enforceNumericCharacters) {
		$value = $enforceNumericCharacters === true ? '1' : '0';
		$this->config->setAppValue('password_policy', 'enforceNumericCharacters', $value);
	}

	/**
	 * enforce special characters
	 *
	 * @param bool $enforceSpecialCharacters
	 */
	public function setEnforceSpecialCharacters($enforceSpecialCharacters) {
		$value = $enforceSpecialCharacters === true ? '1' : '0';
		$this->config->setAppValue('password_policy', 'enforceSpecialCharacters', $value);
	}

    /**
     * set the days until an password expires
     *
     * @param int $expirationDays
     */
    public function setExpirationDays($expirationDays) {
        $this->config->setAppValue('password_policy', 'expirationDays', $expirationDays);
    }

    /**
     * set the days until an password expires
     *
     * @param int $expirationDays
     */
    public function setExpirationMailDaysBefore($expirationMailDaysBefore) {
        $this->config->setAppValue('password_policy', 'expirationMailDaysBefore', $expirationMailDaysBefore);
    }

    /**
     * set nextcloud host
     *
     * @param $nextcloudHost
     */
    public function setNextcloudHost($nextcloudHost) {
        $this->config->setAppValue('password_policy', 'nextcloudHost', $nextcloudHost);
    }

    /**
     * set the excluded groups
     *
     * @param $excludeGroups
     */
    public function setExcludeGroups($excludeGroups) {
        $this->config->setAppValue('password_policy', 'ExcludeGroups', $excludeGroups);
    }
}
