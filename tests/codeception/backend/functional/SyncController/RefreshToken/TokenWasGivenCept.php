<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/2/2017
 * Time: 5:07 PM
 */

/* @var $scenario Codeception\Scenario */

$I = new \FunctionalTester($scenario);

$I->sendPOST('sync/refresh-token',
    ['accessToken' => Yii::$app->params['testAccessToken']]
);
$I->seeResponseCodeIs(200);

/*
    "<?xml version="1.0" encoding="UTF-8"?>" .
    "<credentials>" .
        "<accessToken> " . json_encode($token) . "</accessToken>" .
        "<userEmail>$userEmail</userEmail>" .
    "</credentials>";
 */

$I->wantTo('check that xml response is correct');
try {
    $response = $I->grabResponse();
    $responseXML = new \SimpleXMLElement($response);
} catch (Exception $exception) {
    $I->fail("Response is not valid XML");
}

$I->assertEquals($responseXML->getName(), 'credentials', 'Wrong root element name. Have to be <credentials>');
$credentials = $responseXML;
$I->assertEquals(count($credentials->accessToken), 1, 'Count of accessToken el have to be 1');
$token = json_decode($credentials->accessToken, true);
$I->assertTrue(isset($token['access_token']));
$I->assertTrue(isset($token['token_type']));
$I->assertTrue(isset($token['expires_in']));
$I->assertTrue(isset($token['id_token']));
$I->assertTrue(isset($token['created']));
$I->assertTrue(isset($token['refresh_token']));
$I->assertTrue((intval($token['created']) + intval($token['expires_in'])) >= time());
$I->assertTrue((intval($token['created']) + intval($token['expires_in'])) >= time());

$I->assertEquals(count($credentials->userEmail), 1, 'Count of userEmail el have to be 1');
$I->assertEquals($credentials->userEmail, "kitushakoff@gmail.com", 'userEmail el have to be equal "kitushakoff@gmail.com"');

