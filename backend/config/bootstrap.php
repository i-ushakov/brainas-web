<?php

use yii\di\Instance;
use backend\components\ChangeOfTaskParser;
use backend\components\ChangeOfTaskHandler;
use backend\components\XMLResponseBuilder;
use common\components\TaskXMLConverter;
use common\components\ConditionXMLConverter;

$container = Yii::$container;

$container->setDefinitions([
    ChangeOfTaskParser::class => [
        ['class' => ChangeOfTaskParser::class],
    ],
    TaskXMLConverter::class => [
        ['class' => ConditionXMLConverter::class],
    ],
    ChangeOfTaskHandler::class => [
        ['class' => ChangeOfTaskHandler::class],
        [Instance::of(ChangeOfTaskParser::class), Instance::of(TaskXMLConverter::class)]
    ],
    XMLResponseBuilder::class => [
        ['class' => XMLResponseBuilder::class],
        [Instance::of(TaskXMLConverter::class)]
    ]
]);