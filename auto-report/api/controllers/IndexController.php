<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\Admin;
use common\models\AdminSearch;

/**
 * Site controller
 */
class IndexController extends BaseController
{

    /**
     * @method   初始化方法 
     * @author : kongerlong
     * @Date   : 2017-11-8
     *
     */
    public function init()
    {
        //检测登录
        // $this->isLogin();
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        echo 666;exit;
//        $this->respond();
        // return $this->render('index');
    }

}
