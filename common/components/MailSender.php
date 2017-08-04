<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 9/19/2016
 * Time: 8:09 PM
 */
namespace common\components;

/**
 * Class MailSender
 * For sending different types of emails
 * @package common\components
 */
class MailSender
{
    /**
     * Send email with feedback from user
     * @param $userEmail
     * @param $params
     * @return bool
     */
    static public function sendFeedbackEmail($userEmail, $params) {
        if (!isset($params['subject']) || !isset($params['message']) || !isset($params['contactemail'])) {
            return false;
        }
        $mailContent = [
            'subject' => trim($params['subject']),
            'message' => trim($params['message']),
            'contactemail' => trim($params['contactemail'])
        ];
        $view = 'feedback_email-html';
        $recipient = \Yii::$app->params['adminEmail'];
        $from = ['brainas.net@gmail' => 'BA Feedback'];
        $subject = "Feedback from " . $userEmail;
        self::sendEmail($view, $recipient, $from, $subject, $mailContent);
        return true;
    }

    /**
     * Send email with log report
     * @param $message
     * @param null $userName
     * @param $level
     * @param null $category
     * @return bool
     */
    static public function sendLogReport($message, $userName= null, $level, $category = null) {
        $view = 'log_report_email-html';
        $recipient = \Yii::$app->params['adminEmail'];
        $from = array('log@brainas.net' => 'Log Report');
        $subject = "Log report - " . $level;
        $mailContent = [
            'level' => $level,
            'category' => $category,
            'message' => $message,
            'userName' => $userName
        ];
        self::sendEmail($view, $recipient, $from, $subject, $mailContent);
        return true;
    }

    /**
     * Send email
     *
     * @param $view
     * @param $recipient
     * @param $from
     * @param $subject
     * @param $params
     */
    static public function sendEmail($view, $recipient, $from, $subject, $params) {
        \Yii::$app->mail->compose($view, $params)
            ->setFrom($from)
            ->setTo($recipient)
            ->setSubject($subject)
            ->send();
    }
}