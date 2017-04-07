<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 11/28/2016
 * Time: 5:37 PM
 */

namespace common\components;

use common\models\EventType;
use common\nmodels\Condition;

class ConditionXMLConverter {
    public function fromXML(\SimpleXMLElement $xml) {
        if ($xml == null) {
            return null;
        }
        $condition = new Condition;
        if (isset($xml['id']) && $xml['id'] != 0) {
            $condition->id = intval($xml['id']);
        } else {
            $condition->id = 0;
        }

        if (isset($xml['type'])) {
            $type = (String)$xml['type'];
            try {
                $condition->type = EventType::getTypeIdByName($type);
            } catch (BAException $e) {
                throw $e;
            }
        }

        if (isset($xml['task-id'])) {
            $taskId = intval($xml['task-id']);
            $condition->task_id = $taskId;
        }

        if (isset($xml->params)) {
            $params = (String)$xml->params;
            $condition->params = $params;
        }

        return $condition;
    }
}