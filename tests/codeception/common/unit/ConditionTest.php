<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 11/21/2016
 * Time: 7:15 PM
 */
use common\nmodels\Condition as Condition;


class ConditionTest extends \Codeception\TestCase\Test {

    /**
     * @var UnitTester
     */
    protected $tester;

    public function testSavingCondition() {
        $condition = new common\nmodels\Condition();
        $condition->type = 1;
        $condition->params = '{"lat":55.595865,"lng":38.113754,"radius":100}';
        $condition->save();
        $this->tester->seeRecord('common\nmodels\Condition', array( 'type' => '1', 'params' => '{"lat":55.595865,"lng":38.113754,"radius":100}'));
   }
}
