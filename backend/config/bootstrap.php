<?php

use yii\di\Instance;
use backend\helpers\ChangeOfTaskParser;
use backend\helpers\ChangeOfTaskHandler;
use common\nmodels\TaskXMLConverter;
use common\nmodels\ConditionXMLConverter;

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
        [Instance::of(ChangeOfTaskParser::class), Instance::of(TaskXMLConverter::class), 1]
    ]
]);