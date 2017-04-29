<?php

/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 5/2/2016
 * Time: 11:26 AM
 */
namespace backend\components;

use common\components\TaskXMLConverter;

class XMLResponseBuilder {

    private $taskConveter;

    static function buildXMLResponse($serverChanges, $synchronizedObjects, $lastSyncTime, $token) {
        $xmlResponse = "";
        $xmlResponse .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xmlResponse .= '<syncResponse>';

        /* Tasks */
        $xmlResponse .= '<tasks>';
        // Created tasks
        $xmlResponse .= '<created>';
        foreach ($serverChanges['tasks']['created'] as $id => $serverChange) {
            $xmlResponse .= self::buildXmlOfTask($serverChange['object'],  $serverChange['datetime']);
        }
        $xmlResponse .= '</created>';

        // Updated tasks
        $xmlResponse .= '<updated>';
        foreach ($serverChanges['tasks']['updated'] as $id => $serverChange) {
            if (isset($serverChange['object']) && !empty($serverChange['object'])) {
                $xmlResponse .= self::buildXmlOfTask($serverChange['object'], $serverChange['datetime']);
            } else {
                \Yii::info(
                    "We have a server change without object with datetime = " . $serverChange['datetime'] .
                    " for task with id = " . $id, "MyLog"
                );
            }
        }
        $xmlResponse .= '</updated>';

        // Deleted Tasks
        $xmlResponse .= '<deleted>';
        foreach ($serverChanges['tasks']['deleted'] as $globalId => $localId) {
            $xmlResponse .= '<deletedTask ' .
                'global-id="' . $globalId . '" ' .
                'local-id="' . $localId . '"' .
                '></deletedTask>';
        }
        $xmlResponse .= '</deleted>';
        $xmlResponse .= '</tasks>';

        /* Synchronized Objects */
        if (!empty($synchronizedObjects)) {
            $xmlResponse .= '<synchronizedObjects>';
            // Tasks
            $synchronizedTasks = $synchronizedObjects['tasks'];
            if (!empty($synchronizedTasks)) {
                $xmlResponse .= '<synchronizedTasks>';
                foreach($synchronizedTasks as $localId => $globalId) {
                    $xmlResponse .= "<synchronizedTask>" .
                        "<localId>" . $localId . "</localId>" .
                        "<globalId>" . $globalId . "</globalId>" .
                        "</synchronizedTask>";
                }
                $xmlResponse .= '</synchronizedTasks>';
            }
            // Conditions
            if (!empty($synchronizedObjects['conditions'])) {
                $synchronizedConditions = $synchronizedObjects['conditions'];
                $xmlResponse .= '<synchronizedConditions>';
                foreach($synchronizedConditions as $localId => $globalId) {
                    $xmlResponse .= "<synchronizedCondition>" .
                        "<localId>" . $localId . "</localId>" .
                        "<globalId>" . $globalId . "</globalId>" .
                        "</synchronizedCondition>";
                }
                $xmlResponse .= '</synchronizedConditions>';
            }

            // Events
            if (!empty($synchronizedObjects['events'])) {
                $synchronizedEvents = $synchronizedObjects['events'];
                $xmlResponse .= '<synchronizedEvents>';
                foreach($synchronizedEvents as $localId => $globalId) {
                    $xmlResponse .= "<synchronizedEvent>" .
                        "<localId>" . $localId . "</localId>" .
                        "<globalId>" . $globalId . "</globalId>" .
                        "</synchronizedEvent>";
                }
                $xmlResponse .= '</synchronizedEvents>';
            }
            $xmlResponse .= '</synchronizedObjects>';
        }

        $xmlResponse .= '<lastSyncTime>' . $lastSyncTime . '</lastSyncTime>';
        $xmlResponse .=  "<accessToken>" . json_encode($token) . "</accessToken>";

        $xmlResponse .= '</syncResponse>';

        return $xmlResponse;
    }

    public function __construct(TaskXMLConverter $taskXMLConverter)
    {
        $this->taskConveter = $taskXMLConverter;
    }

    private function addPictureEntity($picture) {
        $xmlPart = "";
        if (isset($picture) && isset($picture->name)) {
            $xmlPart =
                '<picture><name>' . $picture->name . '</name>';
            if (isset($picture->file_id)) {
                $xmlPart .= '<resourceId>' . $picture->file_id . '</resourceId>';
            }
            $xmlPart .= '</picture>';
        }
        return $xmlPart;
    }

    public function buildXmlWithTasksChanges($changedTasks, $currentTime) {
        $xmlResponse = "";
        $xmlResponse .= '<?xml version="1.0" encoding="UTF-8"?>';

        $xmlResponse .= '<changes>';
        $xmlResponse .= '<tasks>';

        // Created tasks
        $xmlResponse .= $this->buildCreatedPart($changedTasks['created']);

        // Updated tasks
        $xmlResponse .= $this->buildUpdatedPart($changedTasks['updated']);

        // Deleted tasks
        $xmlResponse .= $this->buildDeletedPart($changedTasks['deleted']);

        $xmlResponse .= '</tasks>';

        $xmlResponse .= '<serverTime>' . $currentTime . '</serverTime>';

        $xmlResponse .= '</changes>';

        return $xmlResponse;
    }

    public function buildCreatedPart($createdTasks)
    {
        $xmlPart = '<created>';
        foreach ($createdTasks as $id => $createdTask) {
            $xmlPart .= $this->taskConveter->toXML($createdTask['object'],  $createdTask['datetime']);
        }
        $xmlPart .= '</created>';
        return $xmlPart;
    }

    public function buildUpdatedPart($updatedTasks)
    {
        $xmlPart = '<updated>';
        foreach ($updatedTasks as $id => $updatedTask) {
            if (isset($updatedTask['object']) && !empty($updatedTask['object'])) {
                $xmlPart .= self::buildXmlOfTask($updatedTask['object'], $updatedTask['datetime']);
            } else {
                \Yii::info(
                    "We have a server change without object with datetime = " . $updatedTask['datetime'] .
                    " for task with id = " . $id, "MyLog"
                );
            }
        }
        $xmlPart .= '</updated>';

        return $xmlPart;
    }

    public function buildDeletedPart($deletedTasks)
    {
        $xmlPart = '<deleted>';
        foreach ($deletedTasks as $globalId => $localId) {
            $xmlPart .= '<deletedTask ' .
                'globalId="' . $globalId . '" ' .
                'localId="' . $localId . '"' .
                '></deletedTask>';
        }
        $xmlPart .= '</deleted>';
        return $xmlPart;
    }

    public function prepareSyncObjectsXml($synchronizedTasks)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<synchronizedTasks>';

        if (count($synchronizedTasks) > 0) {
            foreach ($synchronizedTasks as $localId => $globalId) {
                $xml .= "<synchronizedTask>" .
                    "<localId>$localId</localId>" .
                    "<globalId>$globalId</globalId>" .
                    "</synchronizedTask>";
            }
        }

        $xml .= '</synchronizedTasks>';
        return $xml;
    }
}