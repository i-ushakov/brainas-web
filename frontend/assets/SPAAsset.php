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
    ];
    public $js = [
        'js/libs/underscore.js',
        'js/libs/backbone.js',
        'https://maps.googleapis.com/maps/api/js',
        'js/settings.js',
        'js/models/panels/MainPanel.js',
        'js/models/panels/TaskPanel.js',
        'js/models/task/Task.js',
        'js/models/task/Condition.js',
        'js/models/task/Event.js',
        'js/view/panels/MainPanelView.js',
        'js/view/panels/TaskPanelView.js',
        'js/view/task/TaskTileView.js',
        'js/view/task/TaskConditionView.js',
        'js/view/task/TaskCardView.js',
        'js/collections/Tasks.js',
        'js/app.js'
    ];
    public $depends = [
    ];
}
