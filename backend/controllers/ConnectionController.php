<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 11/16/2015
 * Time: 1:01 PM
 */

namespace backend\controllers;


use common\models\Task;
use yii\web\Controller;

class ConnectionController extends Controller {


    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionGetTasks(){
        /*
         * $response = Yii::$app->getResponse();
$response->headers->set('Content-Type', 'image/jpeg');
$response->format = Response::FORMAT_RAW;
if ( !is_resource($response->stream = fopen($imgFullPath, 'r')) ) {
   throw new \yii\web\ServerErrorHttpException('file access failed: permission deny');
}
return $response->send();
         */

        //$response = Yii::$app->getResponse();
        //$response->headers->set('Content-Type', 'image/jpeg');

        $tasks = Task::find()
            ->where(['id' => 1])
            ->orderBy('id')
            ->all();

        $xmlWithTasks = "";
        $xmlWithTasks .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xmlWithTasks .= '<tasks>';
        $xmlWithTasks .= '' .
            '<task global-id="' . $tasks[0]->id . '">' .
                '<message>' . $tasks[0]->message . '</message>' .
                '<conditions>' . $this->getConditionsPart($tasks[0]) . '</conditions>' .
            '</task>';
        $xmlWithTasks .= '</tasks>';

        echo $xmlWithTasks;
    }

    private function getConditionsPart($task) {
        $xml = "";
        $conditions = $task->conditions;
        foreach($conditions as $condition){
            $xml .= "<condition>";
            $c = array();
            $events = $condition->events;
            foreach($events as $event) {
                $xml .= "<event type='" . $event->eventType->name . "'>";
                $c[$event->eventType->name]['type'] = $event->eventType->name;
                $xml .= "<params>";
                $params = json_decode($event->params);
                foreach ($params as $name => $value) {
                    $xml .= "<$name>$value</$name>";
                }
                $xml .= "</params>";
                $xml .= "</event>";
            }
            $item['conditions'][] = $c;
            $xml .= "</condition>";
        }
        $items[] = $item;
        return $xml;
    }

}