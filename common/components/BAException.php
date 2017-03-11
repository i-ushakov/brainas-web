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
// 1102
    const EMPTY_PARAM_EXCODE = 1103;
    const INVALID_PARAM_EXCODE = 1104;
    const WRONG_ROOT_XML_ELEMENT_NAME = 1105;

    public function __construct(string $message, int $code, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}