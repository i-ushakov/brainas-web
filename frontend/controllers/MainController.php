<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 10/18/2015
 * Time: 2:11 PM
 */

namespace frontend\controllers;

use yii\web\Controller;

/**
 * Class MainController
 * Provide access to functional of Single Page Application Brainy Assistant
 *
 * @package frontend\controllers
 */
class MainController extends Controller {
    /**
     * Displays main panel with tasks and filters
     *
     * @return mixed
     */
    public function actionPanel()
    {
        return $this->render('panel');
    }
}