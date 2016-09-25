<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 9/24/2016
 * Time: 2:00 PM
 */

namespace common\components;

use Yii;
use yii\log\Logger;


class CustomLogger
{
    const ERROR = "error";
    const WARNING = "warning";
    const INFO = "info";
    const TRACE = "trace";
    const PROFILE = "profile";
    const PROFILE_BEGIN = "profile_begin";
    const PROFILE_END = "profile_end";

    const CATEGORY = 'CustomLog';


    static public function log($message, $level, $userName = null, $sendmail = false) {
        if (!isset($userName) || $userName == null || $userName == "") {
            $userName = "Guest";
        }
        $content = "#CUSTOM_LOG# (User:" . $userName . ") " .  $message;
        Yii::getLogger()->log($content, self::convertLogLevel($level), self::CATEGORY);
        if ($sendmail) {
            // TODO use debug_backtrace to get caller function, class line
            // http://stackoverflow.com/questions/190421/caller-function-in-php-5
            MailSender::sendLogReport($message, $userName, $level, self::CATEGORY);
        }
    }

    static private function convertLogLevel($level) {
        switch ($level) {
            case self::ERROR :
                return Logger::LEVEL_ERROR;
                break;

            case self::WARNING :
                return Logger::LEVEL_WARNING;
                break;

            case self::INFO :
                return Logger::LEVEL_INFO;
                break;

            case self::TRACE :
                return Logger::LEVEL_TRACE;
                break;

            case self::PROFILE :
                return Logger::LEVEL_PROFILE;
                break;

            case self::PROFILE_BEGIN :
                return Logger::LEVEL_PROFILE_BEGIN;
                break;

            case self::PROFILE_END :
                return Logger::LEVEL_PROFILE_END;
                break;
        }
    }
}