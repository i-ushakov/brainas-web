<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 8/8/2017
 * Time: 5:56 PM
 */

namespace frontend\components;

use common\components\MailSender;

class FeedbackManager
{
    const FAILED_MSG = 'failed';
    const FAILED_TYPE_NO_SUBJECT = 'no_subject';
    const FAILED_TYPE_NO_MESSAGE = 'no_message';
    const FAILED_TYPE_SENDING_FAILED = 'sending_is_failed';

    public function sendFeedback($params, $userEmail)
    {
        if (!isset($params['subject'])) {
            $result = array(
                'status' => self::FAILED_MSG,
                'type' => self::FAILED_TYPE_NO_SUBJECT
            );
        } else if (empty($params['message'])){
            $result = array(
                'status' => self::FAILED_MSG,
                'type' => self::FAILED_TYPE_NO_MESSAGE
            );
        } else if (MailSender::sendFeedbackEmail($userEmail, $params)) {
            $result = array('status' => 'success');
        } else {
            $result = array(
                'status' => self::FAILED_MSG,
                'type' => self::FAILED_TYPE_SENDING_FAILED
            );
        }

        return $result;
    }
}