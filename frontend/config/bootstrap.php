<?php

use yii\di\Instance;
use common\components\MailSender;
use frontend\components\GoogleIdentityHelper;
use frontend\components\FeedbackManager;
use frontend\components\Factory\GoogleClientFactory;

$container = Yii::$container;

$container->setDefinitions([
    'GoogleClientWithoutToken' => function() {
        return GoogleClientFactory::create();
    },

    GoogleIdentityHelper::class => [
        ['class' => GoogleIdentityHelper::class],
        [Instance::of('GoogleClientWithoutToken')]
    ],

    MailSender::class => [
        ['class' => MailSender::class]
    ],

    FeedbackManager::class => [
        ['class' => FeedbackManager::class],
        [
            Instance::of(MailSender::class)
        ]
    ]
]);
