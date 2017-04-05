<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 3/2/2017
 * Time: 5:07 PM
 */

/* @var $scenario Codeception\Scenario */

$I = new \FunctionalTester($scenario);

$I->sendPOST('sync/refresh-token');

$I->seeResponseCodeIs(200);

/*
    "<?xml version="1.0" encoding="UTF-8"?>" .
    "<errors>
        <error>Token wasn't given</error>
     </errors>";
 */

$I->wantTo('check that xml response is correct');
try {
    $response = $I->grabResponse();
    $responseXML = new \SimpleXMLElement($response);
} catch (Exception $exception) {
    $I->fail("Response is not valid XML");
}

$I->assertEquals($responseXML->getName(), 'errors', 'Wrong root element name. Have to be <errors>');
$errors = $responseXML;
$I->assertEquals(count($errors->error), 1, 'Count of error elements have to be 1');
$I->assertEquals((string)$errors->error, "Token wasn't given", 'error element have to contain text "Token wasn\'t given"');

