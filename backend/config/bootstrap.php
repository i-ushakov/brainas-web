<?php

use yii\di\Instance;
use backend\components\ChangeOfTaskParser;
use backend\components\ChangeOfTaskHandler;
use backend\components\XMLResponseBuilder;
use backend\components\TasksSyncManager;
use backend\components\Factory\GoogleClientFactory;
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

    ChangeOfTaskHandler::class => [
        ['class' => ChangeOfTaskHandler::class],
        [
            Instance::of(ChangeOfTaskParser::class),
            Instance::of(TaskXMLConverter::class),
            null,
            null,
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