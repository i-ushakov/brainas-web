<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 11/16/2015
 * Time: 1:01 PM
 */



namespace backend\controllers;

use common\models\User;
use Yii;
use common\infrastructure\ChangedTask;
use common\models\Task;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\HttpException;

class ConnectionController extends Controller {

    public static $jsonGoogleClientConfig = "/var/www/brainas.net/backend/config/client_secret_821865067743-3jra19eq308up54c436e1g6fpmvef1g1.apps.googleusercontent.com.json";

    private $userId;

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionGetTasks() {
        $accessToken = $this->getTokenFronmPost();
        $client = $this->prepareGoogleClient($accessToken);
        $userEmail = $this->verifyIdToken($client, $accessToken);
        $this->userId = $this->getUserIdByEmail($userEmail);
        $changedTasks = $this->getChangedTasks();

        $created = array();
        $updated = array();
        $deleted = array();
        foreach ($changedTasks as $changedTask) {
            if ($changedTask->action == "Created") {
                $created[$changedTask->task_id]['action'] = $changedTask->action;
                $created[$changedTask->task_id]['datetime'] = $changedTask->datetime;
            } else if ($changedTask->action == "Changed") {
                $updated[$changedTask->task_id]['action'] = $changedTask->action;
                $updated[$changedTask->task_id]['datetime'] = $changedTask->datetime;
            } else if ($changedTask->action == "Deleted") {
                $deleted[$changedTask->task_id]['action'] = $changedTask->action;
                $deleted[$changedTask->task_id]['datetime'] = $changedTask->datetime;
            }
        }

        $xmlWithTasks = "";
        $xmlWithTasks .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xmlWithTasks .= '<tasks>';

        // New (created) Tasks
        $xmlWithTasks .= '<created>';
        $createdTasks = Task::find()
            ->where(array('in', 'id', array_keys($created)))
            ->orderBy('id')
            ->all();
        foreach ($createdTasks as $createdTask) {
            $xmlWithTasks .= $this->buildTaskXml($createdTask,  $created[$createdTask->id]['datetime']);
        }
        $xmlWithTasks .= '</created>';

        // Old (updated) Tasks
        $xmlWithTasks .= '<updated>';
        $updatedTasks = Task::find()
            ->where(array('in', 'id', array_keys($updated)))
            ->orderBy('id')
            ->all();
        foreach ($updatedTasks as $updatedTask) {
            $xmlWithTasks .= $this->buildTaskXml($updatedTask, $updated[$updatedTask->id]['datetime']);
        }
        $xmlWithTasks .= '</updated>';

        // Removed (deleted) Tasks
        $xmlWithTasks .= '<deleted>';
        foreach ($deleted as $id => $d) {
            $xmlWithTasks .= '<deletedTask ' .
                    'global-id="' . $id . '" ' .
                    'time-changes="' . $d['datetime'] . '"' .
                '></deletedTask>';
        }

        $xmlWithTasks .= '</deleted>';
        $xmlWithTasks .= '</tasks>';
        echo $xmlWithTasks;
    }

    public function actionAcceptedChanges() {
        $this->enableCsrfValidation = false;
        Yii::$app->controller->enableCsrfValidation = false;
        $post = Yii::$app->request->post();
        $acceptedChangesJSON = file_get_contents("php://input");
        $acceptedChanges = Json::decode($acceptedChangesJSON);
        //$acceptedChanges = Json::decode($acceptedChangesJSON);
        $records = ChangedTask::find()
            ->where(array('in', 'task_id', array_keys($acceptedChanges['tasks'])))
            ->andWhere(['user_id' => 1])
            ->orderBy('id')
            ->all();
        foreach($records as $record) {
            $record->delete();
        }
    }

    private function buildTaskXml($task, $datetime) {
        $xml = '' .
            '<task global-id="' . $task->id . '" time-changes="' . $datetime . '">' .
                '<message>' . $task->message . '</message>' .
                '<description>' . $task->description . '</description>' .
                '<conditions>' . $this->buildConditionsPart($task) . '</conditions>' .
                '<status>WAITING</status>' .
            '</task>';
        return $xml;
    }

    private function buildConditionsPart($task) {
        $xml = "";
        $conditions = $task->conditions;
        foreach($conditions as $condition){
            $xml .= "<condition id='" . $condition->id . "' task-id='" . $condition->task_id ."'>";
            $events = $condition->events;
            foreach($events as $event) {
                $xml .= "<event type='" . $event->eventType->name . "' id='" . $event->id . "'>";
                $xml .= "<params>";
                $params = json_decode($event->params);
                foreach ($params as $name => $value) {
                    $xml .= "<$name>$value</$name>";
                }
                $xml .= "</params>";
                $xml .= "</event>";
            }
            $xml .= "</condition>";
        }
        return $xml;
    }

    private function getChangedTasks() {
        $changedTasks = ChangedTask::find()
            ->where(['user_id' => $this->userId])
            ->orderBy('datetime')
            ->all();
        return $changedTasks;
    }

    private function getTokenFronmPost() {
        $post = Yii::$app->request->post();
        $post['accessToken'] = "eyJhbGciOiJSUzI1NiIsImtpZCI6IjFmYmU4OTA5M2JkZTk1NTc1YTMwMDY1OTlmMWExOWQyMzViMzgzYjcifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLmdvb2dsZS5jb20iLCJhdWQiOiI4MjE4NjUwNjc3NDMtM2pyYTE5ZXEzMDh1cDU0YzQzNmUxZzZmcG12ZWYxZzEuYXBwcy5nb29nbGV1c2VyY29udGVudC5jb20iLCJzdWIiOiIxMTc0MzAxNDA5ODYwNTg4NjgzOTgiLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwiYXpwIjoiODIxODY1MDY3NzQzLWo4bDJwZjJyaXBycGFwNzVsY2RxYmZ1bTJiYTY4cXR1LmFwcHMuZ29vZ2xldXNlcmNvbnRlbnQuY29tIiwiZW1haWwiOiJraXR1c2hha29mZkBnbWFpbC5jb20iLCJpYXQiOjE0NTM2Mjg1MDMsImV4cCI6MTQ1MzYzMjEwMywicGljdHVyZSI6Imh0dHBzOi8vbGg0Lmdvb2dsZXVzZXJjb250ZW50LmNvbS8tZzNHWDRXLVFxSkkvQUFBQUFBQUFBQUkvQUFBQUFBQUFBQkEvQ25rcnhOMVMydzAvczk2LWMvcGhvdG8uanBnIiwibG9jYWxlIjoiZW4iLCJuYW1lIjoiS2l0IFVzaGFrb3YiLCJnaXZlbl9uYW1lIjoiS2l0IiwiZmFtaWx5X25hbWUiOiJVc2hha292In0.ZKg_V1TE8gYgp1o-qig743kJFwRWKaKZnLvCos-4VlSlmvVK8ORFP2MVp_KtkDJ_Q946thLEnrknEQUGjUlwPd6vq6lHUPm-a5KufbUy6hfAXArZ2XdrpnorpWAAG7kRoVhTbFCPl-qRHaKO6y-5BUxfYVMjZ2dYQYRO0VmxfujNs9YYe1apLFKIZU3ulS9ZQ3Gi6ZxrPvnSvu5Kj0bneY1hQg-pgkt0FqBk67TUbtsaiCiZtC4TW1Qs6xtjb0X6zV1W6owB8mwGhJ04W8wr6foM7YGTk8G5UKehb_q2hsfoQUb6JIFYXSBGnV8tz-vLDCgAyi_I4RJVIsuhYTG4aQ";
        if(isset($post['accessToken'])) {
            $accessToken = $post['accessToken'];
        } else {
            throw new HttpException(401 ,'Token wasn\'t sent');
        }
        return $accessToken;
    }

    private function prepareGoogleClient() {
        $client = new \Google_Client();
        $client->setAuthConfigFile(self::$jsonGoogleClientConfig);
        $client->setAccessType('online'); // default: offline
        $client->setApplicationName('Brain Assistent');
        //$client->setClientId(Yii::$app->params['OAuth2ClientIdFroWebApp']);
        //$client->setClientSecret(Yii::$app->params['OAuth2ClientSecretFroWebApp']);
        //$scriptUri = "http://".$_SERVER["HTTP_HOST"].$_SERVER['PHP_SELF'];
        //$client->setRedirectUri($scriptUri);
        //$client->setDeveloperKey(Yii::$app->params['androidAPIKey']); // API key

        /*
        https://developers.google.com/api-client-library/php/auth/web-app (Using OAuth 2.0 for Web Server Applications)
        http://www.sanwebe.com/2012/11/login-with-google-api-php
        http://enarion.net/programming/php/google-client-api/google-client-api-php/
        */

        return $client;
    }

    private function verifyIdToken($client, $accessToken) {
        if ($accessToken) {
            try {
                $userInfo = $client->verifyIdToken($accessToken);
            } catch (\Firebase\JWT\ExpiredException  $e) {
                throw new HttpException(401 ,'Expired token');
            }
            if (isset($userInfo) && !is_null($userInfo) && isset($userInfo['email'])) {
                return $userInfo['email'];
            } else {
                throw new HttpException(401 ,'Token is not valid');
            }
        }
    }

    private function getUserIdByEmail($userEmail) {
        $user = User::findOne(['username' => $userEmail]);
        if (!empty($user)) {
            return $user->id;
        } else {
            // TODO create new user
            return null;
        }
    }

}