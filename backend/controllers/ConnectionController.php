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
        $xmlWithTasks .= '<task global-id="' . $tasks[0]->id . '"><message>' . $tasks[0]->message . '</message></task>';
        $xmlWithTasks .= '</tasks>';

        echo $xmlWithTasks;
    }

}