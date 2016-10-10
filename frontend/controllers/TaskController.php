<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 10/18/2015
 * Time: 2:11 PM
 */

namespace frontend\controllers;


use common\models\Condition;
use common\models\Event;
use frontend\components\GoogleDriveHelper;
use frontend\components\GoogleIdentityHelper;
use frontend\components\TaskConverter;
use frontend\components\TasksQueryBuilde;
use Yii;
use yii\web\Controller;
use common\models\Task;
use common\models\PictureOfTask;
use common\models\EventType;
use yii\helpers\Json;

class TaskController extends Controller {

    private $userId;
    private $result = array();

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }


    public function actionTestCode() {
        define('APPLICATION_NAME', 'Brainas app');
        define('CLIENT_SECRET_PATH',"/var/www/brainas.net/backend/config/client_secret_925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com.json");
        define('CREDENTIALS_PATH', '/var/www/brainas.net/frontend/credentials');
        define('SCOPES', implode(' ', array(
                \Google_Service_Drive::DRIVE_APPDATA,
                \Google_Service_Drive::DRIVE_METADATA,
                \Google_Service_Drive::DRIVE_FILE,
                )
        ));
        $client = new \Google_Client();
        $client->setApplicationName(APPLICATION_NAME);
        $client->setScopes(SCOPES);
        $client->setAuthConfigFile(CLIENT_SECRET_PATH);
        $client->setDeveloperKey('f958e7db2a82727525e231bd43aabfeddae0a2f3');
        $client->setRedirectUri("postmessage");
        $client->setAccessType('offline');

        $authCode = file_get_contents('php://input');

        if (isset($authCode)) {
            $accessToken = $client->authenticate($authCode);
            \Yii::$app->response->format = 'json';
            $service = new \Google_Service_Drive($client);

            $optParams = array(
                'pageSize' => 10,
                'fields' => "nextPageToken, files(id, name)"
            );
            $results = $service->files->listFiles($optParams);

            $results =  $service->files->listFiles(array(
                'spaces' => 'appDataFolder',
                'fields' => 'nextPageToken, files(id, name, webContentLink)',
                'pageSize' => 10
            ));

            if (count($results->getFiles()) == 0) {
                //print "No files found.\n";
            } else {
                //print "Files:\n";
                foreach ($results->getFiles() as $file) {
                    printf("%s (%s)\n", $file->getName(), $file->getId());
                    print_r("111".$file->getWebContentLink(). "11111");
                }
            }
            $fileId = "1rwymhdIqpVgd3lhH1Qp5VXpGipDjtSO-MinR3KL_xwzP";
            $content = $service->files->get($fileId, array(
                'alt' => 'media' ));
            return;
        }
    }
    /**
     * Return tasks
     *
     * @return mixed
     */
    public function actionGet() {
        if (!Yii::$app->user->isGuest) {
            $userId =  Yii::$app->user->id;

            $tasksQueryBuilde = new TasksQueryBuilde($userId);
            $statusesFilter = (isset($_GET['statusesFilter']) ? $_GET['statusesFilter'] : null);
            $typeOfSort = (isset($_GET['typeOfSort']) ? $_GET['typeOfSort'] : null);
            $tasks = $tasksQueryBuilde->get($statusesFilter, $typeOfSort);

        } else {
            $result = array();
            $result['status'] = "FAILED";
            $result['type'] = "must_be_signed_in";
            \Yii::$app->response->format = 'json';
            return $result;
        }

        $tasksArray = array();
        foreach ($tasks as $task) {
            $tasksArray[] = TaskConverter::prepareTaskForResponse($task);
        }

        \Yii::$app->response->format = 'json';
        return $tasksArray;
    }


    public function actionSave() {
        if ($this->checkThatUserIsNotAGuest()) {
            $post = Yii::$app->request->post();
            $taskForSave = Json::decode($post['task']);
            $taskId = $taskForSave['id'];

            if (is_null($taskId)) {
                $task = new Task();
                $task->user = $this->userId;
                $task->message = "New task";
                $task->save();
            } else {

                $task = Task::find($taskId)
                    ->where(['id' => $taskId, 'user' => Yii::$app->user->id])
                    ->with('picture')
                    ->one();

                if (empty($task)) {
                    $result = array();
                    $this->result['status'] = "FAILED";
                    $result['type'] = "save_error";
                    $this->result['errors'][] = "No task with id = " . $taskId . "that is owned of user  " . $task->user->name;
                    \Yii::$app->response->format = 'json';
                    return $result;
                }
            }

            if (isset($taskForSave['message'])) {
                $task->message = $taskForSave['message'];
            }

            if (isset($taskForSave['description'])) {
                $task->description = $taskForSave['description'];
            }

            if (isset($taskForSave['picture_name']) && isset($taskForSave['picture_file_id']))
            {
               if ((!isset($task->picture->file_id) || $taskForSave['picture_file_id'] != $task->picture->file_id)) {
                   $currentPicture = $task->picture;
                   if (isset($currentPicture)) {
                       $googleDriveHelper = new GoogleDriveHelper(
                           GoogleIdentityHelper::getGoogleClientWithToken(\Yii::$app->user->identity)
                       );
                       $googleDriveHelper->removeFile($currentPicture->file_id);
                       $currentPicture->delete();
                   }

                   $newPictureOfTask = new PictureOfTask();
                   $newPictureOfTask->task_id = $task->id;
                   $newPictureOfTask->name = $taskForSave['picture_name'];
                   $newPictureOfTask->file_id = $taskForSave['picture_file_id'];
                   $newPictureOfTask->save();
               }
            }

            $this->cleanDeletedConditions($taskForSave['conditions'], $task->id);
            if (isset($taskForSave['conditions']) && count($taskForSave['conditions']) > 0) {
                $conditionsAr = Json::decode($post['conditions']);
                foreach ($conditionsAr as $conditionAr) {
                    if (empty($conditionAr)) {
                        continue;
                    }
                    $condition = null;
                    if (isset($conditionAr['conditionId'])) {
                        $conditionId = $conditionAr['conditionId'];
                        $condition = Condition::find($conditionId)
                            ->where(['id' => $conditionId])
                            ->one();
                    } else {
                        $condition = new Condition();
                        $condition->task_id = $task->id;
                        $condition->save();
                    }
                    foreach ($conditionAr['events'] as $eventType => $eventAr) {
                        if (empty($eventAr)) {
                            continue;
                        }
                        if (isset($eventAr['eventId'])) {
                            $eventId = $eventAr['eventId'];
                            $event = Event::find($eventId)
                                ->where(['id' => $eventId])
                                ->one();

                            if (isset($eventAr['deleted'])) {
                                $event->delete();
                            }
                            $event->params = Json::encode($eventAr['params']);
                            $event->save();
                        } else {
                            $event = new Event();
                            $event->condition_id = $condition->id;
                            $event->type = EventType::getTypeIdByName($eventAr['type']);
                            $event->params = Json::encode($eventAr['params']);
                            $event->save();
                        }
                    }
                }
            }


            Yii::warning("!!!!!!!!!!!!!");
            Yii::warning($taskForSave['status']);
            if (isset($taskForSave['status'])) {
                $task->status = $taskForSave['status'];
            }

            if ($task->validate()) {
                $task->save();
                $this->result['status'] = "OK";
                $this->result['task'] = TaskConverter::prepareTaskForResponse($task);
            } else {
                $errors = $task->errors;
                $this->result['status'] = "FAILED";
                $this->result['type'] = "save_erorr";
                $this->result['errors'] = $errors;
            }
        }

        \Yii::$app->response->format = 'json';
        return $this->result;
    }

    public function actionRemove() {
        if ($this->checkThatUserIsNotAGuest()) {

            $post = Yii::$app->request->post();
            $taskForRemove = Json::decode($post['task']);
            $taskId = $taskForRemove['id'];

            $task = Task::find($taskId)
                ->where(['id' => $taskId, 'user' => Yii::$app->user->id])
                ->one();

            if (!empty($task)) {
                if ($task->delete()) {
                    $this->result['status'] = "OK";
                }
            } else {
                $this->result['status'] = "FAILED";
                $this->result['type'] = "remove_error";
                $this->result['message'] = "No task with id = " . $taskId . "that is owned of user  " . $task->user->name;;
            }


            \Yii::$app->response->format = 'json';
            return $this->result;
        } else {
            \Yii::$app->response->format = 'json';
            return $this->result;
        }
    }

    public function getGoogleClient() {
        define('APPLICATION_NAME', 'Brainas app');
        define('CLIENT_SECRET_PATH',"/var/www/brainas.net/backend/config/client_secret_925705811320-cenbqg1fe5jb804116oefl78sbishnga.apps.googleusercontent.com.json");
        define('CREDENTIALS_PATH', '/var/www/brainas.net/frontend/config/credentials.json');
        define('SCOPES', implode(' ', array(
                \Google_Service_Drive::DRIVE_METADATA_READONLY)
        ));


        $client = new \Google_Client();
        $client->setApplicationName(APPLICATION_NAME);
        $client->setScopes(SCOPES);
        $client->setAuthConfigFile(CLIENT_SECRET_PATH);
        $client->setRedirectUri("https://brainas.com/task/test-code");
        $client->setAccessType('offline');

       // $client->setAccessToken($accessToken);

        //if (file_exists(CREDENTIALS_PATH)) {
            //$accessToken = file_get_contents(CREDENTIALS_PATH);
        //} else {
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            //$authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            //$accessToken = $client->authenticate($authCode);

            // Store the credentials to disk.
            //if(!file_exists(dirname(CREDENTIALS_PATH))) {
               //mkdir(dirname(CREDENTIALS_PATH), 0700, true);
            //}
            //file_put_contents(CREDENTIALS_PATH, $accessToken);
            //printf("Credentials saved to %s\n", CREDENTIALS_PATH);
        //}
        //$client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        //if ($client->isAccessTokenExpired()) {
            //$client->refreshToken($client->getRefreshToken());
            //file_put_contents(CREDENTIALS_PATH, $client->getAccessToken());
        //}
        $accessTokent = $client->fetchAccessTokenWithAuthCode("4/Vj3wPOx6CjHUBTBvimef4Jh_RmvqWyruh6z_s7g6ZcE");
        //$accessTokent = $client->getAccessToken();
        file_put_contents(CREDENTIALS_PATH, $client->getAccessToken());
        return $client;
    }



    private function checkThatUserIsNotAGuest() {
        if (Yii::$app->user->isGuest) {
            $this->result['status'] = "FAILED";
            $this->result['type'] = "must_be_signed_in";
            return false;
        } else {
            $this->userId = Yii::$app->user->id;
            return true;
        }
    }

    private function cleanDeletedConditions($conditionsForSave, $taskId) {
        $conditionsIds = array();
        if (!empty($conditionsForSave)) {
            foreach ($conditionsForSave as $conditionForSave) {
                if (isset($conditionForSave['id']) && $conditionForSave['id'] != 0) {
                    $conditionsIds[] = $conditionForSave['id'];
                }
            }
        }
        $conditionsFromDB = Condition::find()
            ->where(['task_id' => $taskId])
            ->all();
        foreach($conditionsFromDB as $conditionFromDB) {
            if(!in_array ($conditionFromDB->id, $conditionsIds)) {
                $conditionFromDB->delete();
                $this->cleanDeletedEvents($conditionFromDB->id);
            }
        }
    }


    private function cleanDeletedEvents($conditionId) {
        Event::deleteAll(['condition_id' => $conditionId]);
    }
}