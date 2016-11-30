<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 11/21/2016
 * Time: 7:15 PM
 */
use common\nmodels\Condition as Condition;
use common\models\EventType as EventType;


class ConditionTest extends \Codeception\TestCase\Test {

    /**
     * @var UnitTester
     */
    protected $tester;

    public function testSaving() {
        $condition = new common\nmodels\Condition();
        $condition->type = 1;
        $condition->params = '{"lat":55.595865,"lng":38.113754,"radius":100}';
        $condition->task_id = 304;
        $this->assertTrue($condition->save());
        $this->tester->seeRecord('common\nmodels\Condition', array( 'type' => '1', 'params' => '{"lat":55.595865,"lng":38.113754,"radius":100}'));
   }

    public function testSavingWithoutTaskId() {
        $condition = new common\nmodels\Condition();
        $condition->type = 1;
        $condition->params = '{"lat":55.595865,"lng":38.113754,"radius":100}';
        $this->assertFalse($condition->save());
    }

   public function testSavingWithoutType() {
       $condition = new common\nmodels\Condition();
       $condition->params = '{"lat":55.595865,"lng":38.113754,"radius":100}';
       $condition->task_id = 304;
       $this->assertFalse($condition->save());
   }

    public function testSavingWithoutParams() {
        $condition = new common\nmodels\Condition();
        $condition->type = 2;
        $condition->task_id = 304;
        $this->assertFalse($condition->save());
    }

    public function testSaveWithWrongType() {
        $condition = new common\nmodels\Condition();
        $condition->type = 1003;
        $condition->params = '{"lat":55.595865,"lng":38.113754,"radius":100}';
        $this->assertFalse($condition->save());
    }

   public function testGettingEventType() {
       $condition = new common\nmodels\Condition();
       $condition->type = 1;
       $condition->params = '{"lat":55.595865,"lng":38.113754,"radius":100}';
       $this->assertEquals(1, $condition->eventType->id);
       $this->assertNotEquals(2, $condition->eventType->id);
       $this->assertEquals('GPS', $condition->eventType->name);
       $this->assertNotEquals('GPS2', $condition->eventType->name);
   }
}
