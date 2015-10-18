<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 10/18/2015
 * Time: 2:11 PM
 */

namespace frontend\controllers;

use Yii;
use yii\web\Controller;

class MainController extends Controller {
    /**
     * Displays main panel.
     *
     * @return mixed
     */
    public function actionPanel()
    {
        return $this->render('panel');
    }
}