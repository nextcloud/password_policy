<?php
namespace OCA\Password_Policy\BackgroundJobs;

use OC\BackgroundJob\TimedJob;
use OCA\Password_Policy\Db\UserDAO;
use OCA\Password_Policy\PasswordPolicyConfig;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IUser;
use OCP\Util;
use OCP\Mail\IMailer;
use OCP\Defaults;
use OCP\IUserManager;

class NotifyUserTask extends TimedJob
{
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
     * @var IMailer
     */
    private $mailer;

    /**
     * @var Defaults
     */
    private $defaults;

    /**
     * @var IUserManager
     */
    private $userManager;

    /**
     * @var IL10N
     */
    private $l10n;

    /**
     * @var IGroupManager
     */
    protected $groupManager;



    /**
     * NotifyUserTask constructor.
     *
     * @param PasswordPolicyConfig $config
     * @param IMailer              $mailer
     * @param Defaults             $defaults
     * @param IUserManager         $userManager
     * @param IL10N                $l10n
     */
    public function __construct(PasswordPolicyConfig $config, IMailer $mailer, Defaults $defaults,
        IUserManager $userManager,
        IL10N $l10n,
        IGroupManager $groupManager ) {

        // Run once a day
        $this->setInterval(24 * 60 * 60);

        $this->config = $config;
        $this->logger = \OC::$server->getLogger();
        $this->mailer = $mailer;
        $this->defaults = $defaults;
        $this->userManager = $userManager;
        $this->l10n = $l10n;
        $this->groupManager = $groupManager;

        $connection = \OC::$server->getDatabaseConnection();
        $this->userDAO = new UserDAO($connection);
    }

    /**
     * @param $argument
     */
    protected function run($argument) {
        if ($this->config->getExpirationDays() > 0 && $this->config->getExpirationMailDaysBefore() > 0) {

            $daysTillNotification = $this->config->getExpirationDays() - $this->config->getExpirationMailDaysBefore();

            $users = $this->userDAO->findAllUsersExpirationDataBy('notification_sent', 0);

            foreach ($users as $user) {
                $passwordLastChanged = $user['last_changed'];
                $userObject = $this->userManager->get($user['uid']);

                if (strtotime('+' . $daysTillNotification . ' days', $passwordLastChanged) <= time()
                    && $this->userPasswordCanExpire($userObject)) {

                    $result = $this->sendNotificationMail($userObject, $passwordLastChanged);

                    if ($result == true) {
                        $this->setNotificationSend($user['uid']);
                    } else {
                        $this->logger->debug('Sending email to user "' . $user['uid'] . '" with email "' . $userObject->getEMailAddress() .'" failed');
                    }
                }
            }
        }
    }

    /**
     * @param IUser $userObject
     * @param integer $passwordLastChanged
     *
     * @return bool
     */
    protected function sendNotificationMail($userObject, $passwordLastChanged) {
        $userId = $userObject->getUID();
        $userEmail = $userObject->getEMailAddress();

        if (strlen($userEmail) > 1 && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            $template_var = [
                'user' => $userId,
                'linkToPage' => $this->config->getNextcloudHost(),
                'expireDay' => date('j. F Y', strtotime('+' . $this->config->getExpirationDays() . ' days', $passwordLastChanged))
            ];

            $html_template = new TemplateResponse('password_policy', 'email.usernotify_html', $template_var, 'blank');
            $html_part = $html_template->render();

            $plaintext_template = new TemplateResponse('password_policy', 'email.usernotify_plaintext', $template_var, 'blank');
            $plaintext_part = $plaintext_template->render();

            $subject = $this->l10n->t('Your password is about to expire.');
            $from = Util::getDefaultEmailAddress('register');

            $message = $this->mailer->createMessage();
            $message->setFrom([$from => $this->defaults->getName()]);
            $message->setTo([$userEmail => $userId]);
            $message->setSubject($subject);
            $message->setPlainBody($plaintext_part);
            $message->setHtmlBody($html_part);

            $failed_recipients = $this->mailer->send($message);

            if ( !empty($failed_recipients) ) {
                $this->logger->error('Failed recipients: '.print_r($failed_recipients, true));
                return false;
            }

            return true;
        }

        return false;
    }


    /**
     * Set notification_sent field in db for user
     *
     * @param $userId
     */
    protected function setNotificationSend($userId) {
        $fields = [
            'uid' => $userId,
            'notification_sent' => time()
        ];

        $this->userDAO->updateUserExpirationData($fields);
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