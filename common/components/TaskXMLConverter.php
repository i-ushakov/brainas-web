<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 12/1/2016
 * Time: 8:42 AM
 */

namespace common\components;

use common\components\BAException;
use common\models\PictureOfTask;
use common\nmodels\Task;

class TaskXMLConverter {
    const WRONG_ROOT_ELEMNT = "XML root element have to be <task>";

    private $conditionConverter;

    public function __construct(ConditionXMLConverter $conditionConverter) {
        $this->conditionConverter = $conditionConverter;
    }

    /*
     * @param \SimpleXMLElement
     *
     * Conver xml element to Task object without condition
     *
     * @param \SimpleXMLElement $xml - xml obj with changeOfTask
     *
     * @return array - Associative array('task'=>Task,'conditions'=>Condition[], 'picture' => Picture)
     *
     * @throws BAException INCORRECT_SIMPLE_XML_OBJEST_ERRORCODE
     */
    public function fromXML(\SimpleXMLElement $xml) {
        if ($xml == null) {
            return null;
        }
        if ($xml->getName() != "task") {
            throw new BAException(self::WRONG_ROOT_ELEMNT,  BAException::WRONG_ROOT_XML_ELEMENT_NAME);
        }
        $task = new Task;

        if (isset($xml['globalId']) && intval($xml['globalId']) != 0) {
            $task->id = intval($xml['globalId']);
        } else {
            $task->id = 0;
        }

        if (isset($xml->message)) {
            $task->message = (String)$xml->message;
        }

        if (isset($xml->description)) {
            $task->description = (String)$xml->description;
        }

        if (isset($xml->status)) {
            $task->status = (String)$xml->status;
        }

        $conditions = array();
        if (isset($xml->conditions)) {
            foreach ($xml->conditions->condition as $conditionXMLElement) {
                $condition = $this->conditionConverter->fromXML($conditionXMLElement);
                $condition->task_id = $task->id;
                $conditions[] = $condition;
            }
        }

        $picture = null;
        if (isset($xml->picture)) {
            $picture = new PictureOfTask();
            //$picture->
        }

        return array('task'=>$task, 'conditions'=>$conditions, 'picture' => $picture);
    }

    public function toXML($task, $datetime) {
        $xml = '' .
            '<task globalId="' . $task->id . '" timeOfChange="' . $datetime . '">' .
                '<message>' . $task->message . '</message>' .
                '<description>' . $task->description . '</description>' .
                $this->addPictureEntity($task->picture) .
                '<conditions>' . $this->conditionConverter->toXML($task) . '</conditions>' .
                '<status>' . $task->status . '</status>' .
            '</task>';
        return $xml;
    }

    public function addPictureEntity($picture) {
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
}
