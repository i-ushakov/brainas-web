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
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\HttpException;

class ConnectionController extends Controller {

    public static $jsonGoogleClientConfig = "/var/www/brainas.net/backend/config/client_secret_821865067743-3jra19eq308up54c436e1g6fpmvef1g1.apps.googleusercontent.com.json";

    private $userId;
    private $created = array();
    private $updated = array();
    private $deleted = array();
    private $initSyncTime = null;

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionGetTasks() {
        $accessToken = $this->getTokenFronmPost();

        $client = $this->getGoogleClient();
        $token = $client->fetchAccessTokenWithAuthCode($accessToken);
        var_dump($token);exit();
        //$client->authenticate($accessToken);
        //$accessToken = $client->getAccessToken();
        //var_dump($accessToken); exit();
        //$client->setAccessToken($accessToken);
        /*if ($client->isAccessTokenExpired())
        {
            echo "111111";
            $decoded_token = json_decode($client->getAccessToken());
            $refresh_token = $decoded_token->refresh_token;
            $client->refreshToken($refresh_token);
            $tokenInXML = "<accessToken>" . $refresh_token . "</accessToken>";
        } else {
            echo "22222";
            $tokenInXML = "<accessToken>" . $accessToken . "</accessToken>";
        }*/
        $userEmail = $this->verifyIdToken($client, $accessToken);
        $this->userId = $this->getUserIdByEmail($userEmail);

        if (!isset($_POST['initSyncTime'])) {
            // Getting changed tasks from website
            $changedTasks = $this->getChangedTasks(null);
            $currentDatetime = new \DateTime();
            $currentDatetime->setTimezone(new \DateTimeZone("Europe/London"));
            $this->initSyncTime = $currentDatetime->format('Y-m-d H:i:s');
        } else {
            $this->initSyncTime = $_POST['initSyncTime'];
            $changedTasks = $this->getChangedTasks($this->initSyncTime);
            $currentDatetime = new \DateTime();
            $currentDatetime->setTimezone(new \DateTimeZone("Europe/London"));
            $this->initSyncTime = $currentDatetime->format('Y-m-d H:i:s');
        }


        foreach ($changedTasks as $changedTask) {
            if ($changedTask->action == "Created") {
                $this->created[$changedTask->task_id]['action'] = $changedTask->action;
                $this->created[$changedTask->task_id]['datetime'] = $changedTask->datetime;
            } else if ($changedTask->action == "Changed") {
                $this->updated[$changedTask->task_id]['action'] = $changedTask->action;
                $this->updated[$changedTask->task_id]['datetime'] = $changedTask->datetime;
            } else if ($changedTask->action == "Deleted") {
                $this->deleted[$changedTask->task_id]['action'] = $changedTask->action;
                $this->deleted[$changedTask->task_id]['datetime'] = $changedTask->datetime;
            }
        }

        // This new or updated objects that we want to get from device
        $synchronizedObjectsFromDevice = $this->processAllChangesFromDevice();


        $xmlWithTasks = "";
        $xmlWithTasks .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xmlWithTasks .= '<syncResponse>';
        $xmlWithTasks .= '<tasks>';

        // New (created) Tasks
        $xmlWithTasks .= '<created>';
        $createdTasks = Task::find()
            ->where(array('in', 'id', array_keys($this->created)))
            ->orderBy('id')
            ->all();
        foreach ($createdTasks as $createdTask) {
            $xmlWithTasks .= $this->buildTaskXml($createdTask,  $this->created[$createdTask->id]['datetime']);
        }
        $xmlWithTasks .= '</created>';

        // Old (updated) Tasks
        $xmlWithTasks .= '<updated>';
        $updatedTasks = Task::find()
            ->where(array('in', 'id', array_keys($this->updated)))
            ->orderBy('id')
            ->all();
        foreach ($updatedTasks as $updatedTask) {
            $xmlWithTasks .= $this->buildTaskXml($updatedTask, $this->updated[$updatedTask->id]['datetime']);
        }
        $xmlWithTasks .= '</updated>';

        // Removed (deleted) Tasks
        $xmlWithTasks .= '<deleted>';
        foreach ($this->deleted as $id => $d) {
            $xmlWithTasks .= '<deletedTask ' .
                    'global-id="' . $id . '" ' .
                    'time-changes="' . $d['datetime'] . '"' .
                '></deletedTask>';
        }

        $xmlWithTasks .= '</deleted>';
        $xmlWithTasks .= '</tasks>';

        if (!empty($synchronizedObjectsFromDevice)) {
            $xmlWithTasks .= '<synchronizedObjects>';
            $synchronizedTasks = $synchronizedObjectsFromDevice['tasks'];
            if (!empty($synchronizedTasks)) {
                $xmlWithTasks .= '<synchronizedTasks>';
                foreach($synchronizedTasks as $localId => $globalId) {
                    $xmlWithTasks .= "<synchronizedTask>" .
                            "<localId>" . $localId . "</localId>" .
                            "<globalId>" . $globalId . "</globalId>" .
                        "</synchronizedTask>";
                }
                $xmlWithTasks .= '</synchronizedTasks>';
            }
            $xmlWithTasks .= '</synchronizedObjects>';
        }

        $xmlWithTasks .= '<initSyncTime>' . $this->initSyncTime . '</initSyncTime>';
        //$xmlWithTasks .= $tokenInXML;

        $xmlWithTasks .= '</syncResponse>';

        echo $xmlWithTasks;
    }

    public function actionAcceptedChanges() {
        $this->enableCsrfValidation = false;
        Yii::$app->controller->enableCsrfValidation = false;
        $post = Yii::$app->request->post();
        $acceptedChangesJSON = file_get_contents("php://input");
        $acceptedChanges = Json::decode($acceptedChangesJSON);
        //$acceptedChanges = Json::decode($acceptedChangesJSON);
        /*$records = ChangedTask::find()
            ->where(array('in', 'task_id', array_keys($acceptedChanges['tasks'])))
            ->andWhere(['user_id' => 1])
            ->orderBy('id')
            ->all();
        foreach($records as $record) {
            $record->delete();
        }*/
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

    private function getChangedTasks($initSyncTime) {
        if ($initSyncTime != null) {
            $changedTasks = ChangedTask::find()
                ->where([
                    'and',
                    ['=', 'user_id', $this->userId],
                    ['>', 'datetime', $initSyncTime]
                ])
                ->orderBy('datetime')
                ->all();
        } else {
            $changedTasks = ChangedTask::find()
                ->where(['user_id' => $this->userId])
                ->orderBy('datetime')
                ->all();
        }
        return $changedTasks;
    }

    private function getTokenFronmPost() {
        $post = Yii::$app->request->post();
        //$post['accessToken'] = "";
        if(isset($post['accessToken'])) {
            $accessToken = $post['accessToken'];
        } else {
            throw new HttpException(470 ,'Token wasn\'t sent');
        }
        return $accessToken;
    }

    private function getGoogleClient() {
        $client = new \Google_Client();
        $client->setAuthConfigFile(self::$jsonGoogleClientConfig);
        $client->setAccessType('offline'); // default: offline
        $client->setApplicationName('Brain Assistent');
        //$client->setClientId(Yii::$app->params['OAuth2ClientIdFroWebApp']);
        //$client->setClientSecret(Yii::$app->params['OAuth2ClientSecretFroWebApp']);
        $scriptUri = "http://".$_SERVER["HTTP_HOST"].$_SERVER['PHP_SELF'];
        $client->setRedirectUri($scriptUri);
        //$client->setDeveloperKey(Yii::$app->params['androidAPIKey']); // API key

        /*
        https://developers.google.com/api-client-library/php/auth/web-app (Using OAuth 2.0 for Web Server Applications)
        http://www.sanwebe.com/2012/11/login-with-google-api-php
        http://enarion.net/programming/php/google-client-api/google-client-api-php/
        */

        return $client;
    }

    private function verifyIdToken($client, $token) {
        if ($token) {
            try {
                $userInfo = $client->verifyIdToken($token);
            } catch (\Firebase\JWT\ExpiredException  $e) {
                throw new HttpException(471 ,'Expired token');
            }
            if (isset($userInfo) && !is_null($userInfo) && isset($userInfo['email'])) {
                return $userInfo['email'];
            } else {
                throw new HttpException(472 ,'Token is not valid');
            }
        }
    }

    private function getUserIdByEmail($userEmail) {
        $user = User::findOne(['username' => $userEmail]);
        if (!empty($user)) {
            return $user->id;
        } else {
            $user = new User();
            $user->username = $userEmail;
            $user->email = $userEmail;
            $user->save();
            return $user->id;
        }
    }

    private function processAllChangesFromDevice() {
        $synchronizedObjects = array();
        $synchronizedTasks = array();
        $allChangesInXML = simplexml_load_file($_FILES['all_changes_xml']['tmp_name']);

        $changedTasks = $allChangesInXML->changedTasks;
        foreach($changedTasks->changedTask as $changedTask) {
            $statusOfChanges = (String)$changedTask->change[0]->status;
            if ((string)$changedTask['globalId'] == 0 && $statusOfChanges != "DELETED") {
                $globalId = $this->addTaskFromDevice($changedTask);
                $localId = (string)$changedTask['id'];
                $synchronizedTasks[$localId] = $globalId;
            } else {
                $globalId = (string)$changedTask['globalId'];
                if (Task::findOne($globalId) != null) {
                    $serverChangesTime = $this->getServerChangesTimeById($globalId);
                    $clientChangesTime = (String)$changedTask->change[0]->changeDatetime;
                    if (strtotime($serverChangesTime) < strtotime($clientChangesTime)) {
                        $status = (String)$changedTask->change[0]->status;
                        if ($status == "DELETED") {
                            $this->deleteTaskFromDevice($changedTask);
                            $localId = 0;
                        } elseif ($status == "UPDATED" || $status == "CREATED") {
                            $this->updateTaskFromDevice($changedTask);
                            $localId = (string)$changedTask['id'];
                        }

                        $synchronizedTasks[$localId] = $globalId;
                        unset($this->updated[$globalId]);
                    }
                } else {
                    $this->deleted[$globalId]['action'] = "Deleted";
                    $currentDatetime = new \DateTime();
                    $currentDatetime->setTimezone(new \DateTimeZone("Europe/London"));
                    $this->deleted[$globalId]['datetime'] = $currentDatetime->format('Y-m-d H:i:s');
                    $this->deleted[$globalId]['datetime'] = $changedTask->datetime;
                }
            }
        }

        $synchronizedObjects['tasks'] = $synchronizedTasks;
        return $synchronizedObjects;
    }

    private function addTaskFromDevice ($newTaskFromDevice) {
        $task = new Task();
        $task->message = (String)$newTaskFromDevice->message;
        $task->user = $this->userId;
        $task->save();
        $changeDatetime = (String)$newTaskFromDevice->change[0]->changeDatetime;
        $task->loggingChangesForSync("Created", $changeDatetime);
        return $task->id;
    }

    private function updateTaskFromDevice ($changedTask) {
        $id = (string)$changedTask['globalId'];
        $message = (string)$changedTask->message;
        $task = Task::findOne($id);
        $task->message = $message;
        $task->save();
        $changeDatetime = (String)$changedTask->change[0]->changeDatetime;
        $task->loggingChangesForSync("Changed", $changeDatetime);
        return $task->id;
    }

    private function deleteTaskFromDevice ($changedTask) {
        $id = (string)$changedTask['globalId'];
        $task = Task::findOne($id);
        $task->delete();
        //$changeDatetime = (String)$changedTask->change[0]->changeDatetime;
        //$task->loggingChangesForSync("Changed", $changeDatetime);
        //return $task->id;
    }

    private function getServerChangesTimeById($taskid)
    {
        $changedTask = ChangedTask::find()
            ->where(['user_id' => $this->userId, 'task_id' => $taskid])
            ->orderBy('id')
            ->one();
        if (!is_null($changedTask)) {
            return $changedTask->datetime;
        } else {
            return null;
        }
    }
}