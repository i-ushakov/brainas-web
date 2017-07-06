<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/22/2016
 * Time: 10:59 AM
 */

namespace backend\components;

/*
 * ChangeOfTaskParser is a helper class responsive for parse XML-document that contain a task info,
 * that was gotten from user's device
 */

class ChangeOfTaskParser {
    /**
     * Checking is a task from a new
     *
     * @param \SimpleXMLElement $xml
     * @return bool
     */
    public function isANewTask(\SimpleXMLElement $xml) {
        $statusOfChanges = (String)$xml->change[0]->status;
        $globalId = (string)$xml['globalId'];
        if ($globalId == 0 && $statusOfChanges != "DELETED") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determining - is a given task from device was deleted (on device)
     *
     * @param \SimpleXMLElement $xml
     * @return bool
     */
    public function wasDeletedTask(\SimpleXMLElement $xml) {
        $statusOfChanges = (String)$xml->change[0]->status;
        if ($statusOfChanges == "DELETED") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Just retrieving a task's global Id from XML-document
     *
     * @param \SimpleXMLElement $xml
     * @return int|string
     */
    public function getGlobalId(\SimpleXMLElement $xml) {
        $globalId = (string)$xml['globalId'];
        if ($globalId == '') {
            $globalId = 0;
        }
        return $globalId;
    }

    /**
     * Retrive time of changes (device time)
     * @param \SimpleXMLElement $xml
     * @return string
     */
    public function getClientTimeOfChanges(\SimpleXMLElement $xml) {
        return (String)$xml->change[0]->changeDatetime;
    }

    /**
     * retirve status of changes (CREATED | UPDATED | DELETED)
     *
     * @param \SimpleXMLElement $xml
     * @return string
     */
    public function getStatus(\SimpleXMLElement $xml) {
        return (String)$xml->change[0]->status;
    }
}