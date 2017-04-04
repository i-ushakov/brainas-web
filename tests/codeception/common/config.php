<?php
$secureParams =  require('params.php');
return $config =  yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../backend/config/main.php'),
    [
        'id' => 'backend-tests',
        'components' => [
            'db' => [
                'class' => 'yii\db\Connection',
                'dsn' => 'mysql:host=localhost;dbname=ba',
                'username' => 'root_user',
                'password' => $secureParams['dbPass'],
                'charset' => 'utf8',
            ],
        ]
    ]
);
