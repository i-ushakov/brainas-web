<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/22/2016
 * Time: 10:59 AM
 */

namespace backend\helpers;

use common\nmodels\TaskXMLConverter;

class ChangeOfTaskParser {
    public function isANewTask(\SimpleXMLElement $xml) {
        $statusOfChanges = (String)$xml->change[0]->status;
        $globalId = (string)$xml['globalId'];
        if ($globalId == 0 && $statusOfChanges != "DELETED") {
            return true;
        } else {
            return false;
        }
    }

    public function wasDeletedTask(\SimpleXMLElement $xml) {
        $statusOfChanges = (String)$xml->change[0]->status;
        if ($statusOfChanges == "DELETED") {
            return true;
        } else {
            return false;
        }
    }

    public function getGlobalId(\SimpleXMLElement $xml) {
        $globalId = (string)$xml['globalId'];
        return $globalId;
    }

    public function getTimeOfChange(\SimpleXMLElement $xml) {
        return (String)$xml->change[0]->changeDatetime;
    }

    public function getStatus(\SimpleXMLElement $xml) {
        return (String)$xml->change[0]->status;
    }
}