<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 7/16/2017
 * Time: 11:08 AM
 */

namespace common\components\logging;


use Yii;
use yii\db\ActiveRecord;

/**
 * Class LogEvent
 * Model for logging different type of events with important info in database
 * and binding with Tag to easily retrieve needed information later
 *
 * @package common\components\logging
 */
class LogEvent extends ActiveRecord
{
    protected $tags = [];

    public static function tableName()
    {
        return 'log_events';
    }

    public function getTags()
    {
        return $this->hasMany(LogTag::className(), ['log_id' => 'id']);
    }

    public function addTag($tag)
    {
        $this->tags[] = $tag;
    }

    public function addTags($tags)
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }

    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);
        foreach ($this->tags as $tag) {
            $logTag = new LogTag();
            $logTag->log_id = $this->id;
            $logTag->name = $tag;
            $logTag->save();
        }
    }
}