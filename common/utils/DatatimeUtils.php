<?php

/**
 * Created by PhpStorm.
 * User: kit
 * Date: 8/9/2017
 * Time: 12:13 PM
 */

namespace common\utils;

class DatatimeUtils
{
    static public function getCurrentUTCTime()
    {
        $currentDatetime = new \DateTime();
        $currentDatetime->setTimezone(new \DateTimeZone("UTC"));
        return $currentDatetime->format('Y-m-d H:i:s');
    }
}