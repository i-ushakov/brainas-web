<?php

use frontend\components\GoogleIdentityHelper;

$container = Yii::$container;

$container->setDefinitions([
    GoogleIdentityHelper::class => [
        ['class' => GoogleIdentityHelper::class]
    ]
]);
