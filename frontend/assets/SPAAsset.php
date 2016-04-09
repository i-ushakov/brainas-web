<?php
/**
 * Created by Kit on 10/19/2015
 */

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SPAAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        "css/bootstrap-datetimepicker.min.css"
    ];
    public $js = [
        "js/libs/moment.min.js",
        "js/libs/bootstrap-datetimepicker.min.js",
        'js/libs/underscore.js',
        'js/libs/backbone.js',
        'js/settings.js',
        'js/Utils.js',
        'js/models/panels/MainPanel.js',
        'js/models/panels/TaskPanel.js',
        'js/models/task/Condition.js',
        'js/collections/Conditions.js',
        'js/models/task/Task.js',
        'js/models/task/Event.js',
        'js/view/ConditionTypeSelectorView.js',
        'js/view/panels/MainPanelView.js',
        'js/view/panels/TaskPanelView.js',
        'js/view/task/TaskTileView.js',
        'js/view/task/TaskLocationConditionView.js',
        'js/view/task/TaskTimeConditionView.js',
        'js/view/task/TaskCardView.js',
        'js/collections/Tasks.js',
        'js/app.js'
    ];
    public $depends = [
    ];
}
