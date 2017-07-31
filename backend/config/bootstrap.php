<?php

use yii\di\Instance;
use backend\components\ChangeOfTaskParser;
use backend\components\ChangeOfTaskHandler;
use backend\components\XMLResponseBuilder;
use backend\components\TasksSyncManager;
use common\components\TaskXMLConverter;
use common\components\ConditionXMLConverter;
use \common\components\GoogleDriveHelper;

$container = Yii::$container;

$container->setDefinitions([
    ChangeOfTaskParser::class => [
        ['class' => ChangeOfTaskParser::class],
    ],

    TaskXMLConverter::class => [
        ['class' => ConditionXMLConverter::class],
    ],


    XMLResponseBuilder::class => [
        ['class' => XMLResponseBuilder::class],
        [Instance::of(TaskXMLConverter::class)]
    ],

    Google_Service_Drive::class => function() {
        $client = Yii::$container->get(Google_Client::class . "_WithToken");
        return new Google_Service_Drive($client);
    },


    GoogleDriveHelper::class => function() {
        $service = Yii::$container->get(Google_Service_Drive::class);
        return GoogleDriveHelper::getInstance($service);
    },

    ChangeOfTaskHandler::class => [
        ['class' => ChangeOfTaskHandler::class],
        [
            Instance::of(ChangeOfTaskParser::class),
            Instance::of(TaskXMLConverter::class),
            null,
            Instance::of(GoogleDriveHelper::class),
        ]
    ],

    TasksSyncManager::class => [
        ['class' => TasksSyncManager::class],
        [
            Instance::of(ChangeOfTaskHandler::class),
            Instance::of(XMLResponseBuilder::class),
        ]
    ]
]);