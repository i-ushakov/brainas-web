<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 11/30/2016
 * Time: 10:24 AM
 */

namespace common\components;

class BAException extends \Exception {
    const WRONG_NAME_OF_EVENT_TYPE_ERRORCODE = 1101;
    const INCORRECT_SIMPLE_XML_OBJEST_ERRORCODE = 1102;

    public function __construct($message, $code, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}