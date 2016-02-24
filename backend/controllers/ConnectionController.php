<?php
/**
 * Created by PhpStorm.
 * User: Kit Ushakov
 * Date: 11/16/2015
 * Time: 1:01 PM
 */


namespace backend\controllers;


use Google_Client;
use Yii;
use common\models\User;
use common\infrastructure\ChangedTask;
use common\models\Task;
use yii\web\Controller;
use yii\web\HttpException;

class ConnectionController extends Controller {

    public static $jsonGoogleClientConfig = "/var/www/brainas.net/backend/config/client_secret_821865067743-3jra19eq308up54c436e1g6fpmvef1g1.apps.googleusercontent.com.json";

    private $userId;
    private $token;
    private $initSyncTime = null;

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionGetTasks() {
        // get user id and access token
        $this->verifyUserAccess();

        // Get chnaged and deletet task
        // from time of last sync for this user
        $changes = $this->getChanges();

        // The objects that was updated from device
        $synchronizedObjectsFromDevice = $this->processAllChangesFromDevice($changes);

        // Build xml-document with server-changes
        // and data about changes from device that were accepted
        $xmlResponse = $this->buildXMLResponse($changes, $synchronizedObjectsFromDevice, $this->token);

        echo $xmlResponse;
    }

    private function verifyUserAccess() {
        // Create Google client and get accessToken
        $client = $this->getGoogleClient();
        $token = $this->getAccessToken($client);


        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($client->getRefreshToken());
            $token = $client->getAccessToken();
        }

        // Get user's date (email) and user id in our system
        $data = $client->verifyIdToken();
        $userEmail = $data['email'];
        $this->userId = $this->getUserIdByEmail($userEmail);

        // save refresh token if exist or retrieve from database and send to client
        if (isset($token['refresh_token'])) {
            $this->saveRefreshToken($token['refresh_token']);
        } else {
            $refreshToken = $this->getRefreshToken();
            if ($refreshToken != null) {
                $token['refresh_token'] = $refreshToken;
            }
        }

        $this->token = $token;
    }

    private static function getGoogleClient() {
        $client = new Google_Client();
        $client->setAuthConfigFile(self::$jsonGoogleClientConfig);
        $client->setRedirectUri("http://brainas.net/backend/web/connection/");
        $client->setScopes("https://www.googleapis.com/auth/plus.login");
        $client->setAccessType('online'); //offline
        $client->setApprovalPrompt('force');

        return $client;
    }

    private function getAccessToken($client) {
        $code = $this->getCodeFromPost();
        if ($code != null) {
            $client->authenticate($code);
            $token = $client->getAccessToken();
        } else {
            $token = json_decode($this->getTokenFromPost(), true);
            $client->setAccessToken($token);
        }
        return $token;
    }

    private function getChanges() {
        $changes = array();
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

        $changes['tasks']['created'] = array();
        $changes['tasks']['updated'] = array();
        $changes['tasks']['deleted'] = array();
        foreach ($changedTasks as $changedTask) {
            if ($changedTask->action == "Created") {
                $changes['tasks']['created'][$changedTask->task_id]['action'] = $changedTask->action;
                $changes['tasks']['created'][$changedTask->task_id]['datetime'] = $changedTask->datetime;
            } else if ($changedTask->action == "Changed") {
                $changes['tasks']['updated'][$changedTask->task_id]['action'] = $changedTask->action;
                $changes['tasks']['updated'][$changedTask->task_id]['datetime'] = $changedTask->datetime;
            } else if ($changedTask->action == "Deleted") {
                $changes['tasks']['deleted'][$changedTask->task_id]['action'] = $changedTask->action;
                $changes['tasks']['deleted'][$changedTask->task_id]['datetime'] = $changedTask->datetime;
            }
        }
        return $changes;
    }

    private function buildXMLResponse($changes, $synchronizedObjectsFromDevice, $token) {
        $xmlResponse = "";
        $xmlResponse .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xmlResponse .= '<syncResponse>';
        $xmlResponse .= '<tasks>';

        // Created tasks
        $xmlResponse .= '<created>';
        $createdTasks = Task::find()
            ->where(array('in', 'id', array_keys($changes['tasks']['created'])))
            ->orderBy('id')
            ->all();
        foreach ($createdTasks as $createdTask) {
            $xmlResponse .= $this->buildTaskXml($createdTask,  $changes['tasks']['created'][$createdTask->id]['datetime']);
        }
        $xmlResponse .= '</created>';

        // Updated tasks
        $xmlResponse .= '<updated>';
        $updatedTasks = Task::find()
            ->where(array('in', 'id', array_keys($changes['tasks']['updated'])))
            ->orderBy('id')
            ->all();
        foreach ($updatedTasks as $updatedTask) {
            $xmlResponse .= $this->buildTaskXml($updatedTask, $changes['tasks']['updated'][$updatedTask->id]['datetime']);
        }
        $xmlResponse .= '</updated>';

        // Deleted Tasks
        $xmlResponse .= '<deleted>';
        foreach ($changes['tasks']['deleted'] as $id => $d) {
            $xmlResponse .= '<deletedTask ' .
                'global-id="' . $id . '" ' .
                'time-changes="' . $d['datetime'] . '"' .
                '></deletedTask>';
        }

        $xmlResponse .= '</deleted>';
        $xmlResponse .= '</tasks>';

        if (!empty($synchronizedObjectsFromDevice)) {
            $xmlResponse .= '<synchronizedObjects>';
            $synchronizedTasks = $synchronizedObjectsFromDevice['tasks'];
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
            $xmlResponse .= '</synchronizedObjects>';
        }

        $xmlResponse .= '<initSyncTime>' . $this->initSyncTime . '</initSyncTime>';
        $xmlResponse .=  "<accessToken>" . json_encode($token) . "</accessToken>";;

        $xmlResponse .= '</syncResponse>';

        return $xmlResponse;
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

    private function getTokenFromPost() {
        $post = Yii::$app->request->post();
        //$post['accessToken'] = "";
        if(isset($post['accessToken'])) {
            $accessToken = $post['accessToken'];
        } else {
            throw new HttpException(470 ,'Token wasn\'t sent');
        }
        return $accessToken;
    }

    private function getCodeFromPost() {
        $post = Yii::$app->request->post();
        //$post['accessToken'] = "";
        if(isset($post['accessCode'])) {
            $accessToken = $post['accessCode'];
            return $accessToken;
        }
        return null;
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

    private function processAllChangesFromDevice($changes) {
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
                    $serverChangesTime = $this->getTimeOfTaskChanges($globalId);
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
                        unset($changes['tasks']['updated'][$globalId]);
                    }
                } else {
                    $changes['tasks']['deleted'][$globalId]['action'] = "Deleted";
                    $currentDatetime = new \DateTime();
                    $currentDatetime->setTimezone(new \DateTimeZone("Europe/London"));
                    $changes['tasks']['deleted'][$globalId]['datetime'] = $currentDatetime->format('Y-m-d H:i:s');
                    $changes['tasks']['deleted'][$globalId]['datetime'] = $changedTask->datetime;
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
    }

    private function getTimeOfTaskChanges($taskid) {
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

    private function getRefreshToken() {
        $refreshToken = null;
        $params = [':user_id' => $this->userId];

        $r = Yii::$app->db->createCommand('SELECT * FROM refresh_tokens WHERE user_id=:user_id')
            ->bindValues($params)
            ->queryOne();
        if (isset($r['refresh_token'])) {
            $refreshToken = $r['refresh_token'];
        }
        return $refreshToken;
    }

    private function saveRefreshToken($refreshToken) {
        $params = [
            ':user_id' => $this->userId,
            ':refresh_token' => $refreshToken,
        ];

        Yii::$app->db->createCommand('
                INSERT INTO refresh_tokens (user_id, refresh_token)
                VALUES(:user_id, :refresh_token) ON DUPLICATE KEY UPDATE
                user_id=:user_id, refresh_token=:refresh_token
            ')
            ->bindValues($params)
            ->execute();
        return;
    }
}