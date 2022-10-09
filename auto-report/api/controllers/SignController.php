<?php

namespace api\controllers;
header('Access-Control-Allow-Origin:*');

use common\models\Admin;
use Yii;
use common\models\Merchant;
use common\models\MerchantSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;


class SignController extends BaseController
{

	public $enableCsrfValidation = false;
    public $PostData;
    /**
     * @method   初始化方法 
     * @author : gengshaopeng
     * @Date   : 2019-11-15
     */
    public function init()
    {
        $this->PostData = Yii::$app->request->post();
	}
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST','GET'],
                ],
            ],
        ];
    }

	/**
     * @method   客户验证登录接口
     * @author : gengshaopeng
     * @Date   : 2019-11-15
     */
    public function actionLogin()
    {
		$username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');
//        $username = 'gengshao';
//        $password = '123123';
		if (!empty($username) && !empty($password)) {
            $admin = Admin::find()->where(['username'=>$username,'password'=>md5($password)])->asArray()->one();
            if($admin){
                Yii::$app->session['username'] = $username;
                Yii::$app->session['password'] = $password;
                $time = time()+3600*2;
                Yii::$app->session['expiretime'] = $time;
                return $this->api_result(200,'success');
            }else{
                return $this->api_result(202,'用户名或密码错误');
            }
        }else{
		    return $this->api_result(201,'用户名或密码不能为空');
        }
    }
}
