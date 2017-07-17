<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 9/24/2016
 * Time: 2:00 PM
 */

namespace common\components\logging;

use Yii;
use yii\log\Logger;


class BALogger
{
    const ERROR = "error";
    const WARNING = "warning";
    const LOG_TYPE_INFO = "info";
    const TRACE = "trace";
    const DEBUG = "debug";
    const PROFILE = "profile";
    const PROFILE_BEGIN = "profile_begin";
    const PROFILE_END = "profile_end";
    const EXCEPTION = "exception";
    const BA_EXCEPTION = "ba_exception";
    const CATEGORY = 'CustomLog';

    // synchronization tags
    const TAG_SYNC = "SYNC";                        // describe synchronization process in a whole
    const TAG_SENDACTION = "SENDACTION";            // when data from device is coming
    const TAG_TOKEN = "TOKEN";                      // all about token exchange
    const TAG_DEVICE_DATA = "devicedata";           // data received from device

    /**
     * Save information log in database
     *
     * @param string $msg
     * @param array $tags
     * @param mixed $data
     *
     * @return void
     */
    public static function i(string $msg = null, array $tags = [], $data = null)
    {
        self::saveLog(self::LOG_TYPE_INFO, $msg, $tags, $data);
    }

    /**
     * @param $type
     * @param string $msg
     * @param array $tags
     * @param mixed $data
     */
    protected static function saveLog($type, $msg = null, $tags = [], $data = null)
    {
        /*
         * @var $logEvent LogEvent
         */
        $logEvent = new LogEvent();
        $logEvent->type = $type;
        if(isset($msg) && !is_null($msg)) {
            $logEvent->message = $msg;
        }
        if(isset($data) && !is_null($data)) {
            $logEvent->data = $data;
        }
        $sessionId = Yii::$app->session->getId();
        // pid
        if($pid = getmypid()) {
            $logEvent->pid = $pid;
        }
        // session id
        if (!empty($sessionId)) {
            $logEvent->session_id = $sessionId;
        }

        // file, line, class, function
        $backTrace = debug_backtrace();
        $logEvent->file = $backTrace[1]['file'];
        $logEvent->line = $backTrace[1]['line'];
        $logEvent->class = $backTrace[2]['class'];
        $logEvent->function = $backTrace[2]['function'];

        // callstack TODO: fro errors and exeptions
        // https://stackoverflow.com/questions/8369275/how-can-i-save-a-php-backtrace-to-the-error-log
        //$logEvent->callstack = debug_print_backtrace();


        // frontend or backend detect
        if (!empty($_SERVER['REDIRECT_URL'])) {
            if (preg_match('/(^\/backend\/)/', $_SERVER['REDIRECT_URL'])) {
                $logEvent->side = 'backend';
            } else if (preg_match('/(^\/frontend\/)/', $_SERVER['REDIRECT_URL'])) {
                $logEvent->side = 'frontend';
            }
        }
        $logEvent->addTags($tags);
        $logEvent->save();
    }

    /**
     * Converting level of message to understandable for YII2
     *
     * @param $level
     * @return int
     */
    static private function convertLogLevel($level) {
        switch ($level) {
            case self::ERROR :
            case self::EXCEPTION :
            case self::BA_EXCEPTION :
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